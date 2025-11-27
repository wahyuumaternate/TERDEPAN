<?php

namespace Modules\PerjanjianKinerja\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\PerjanjianKinerja\Models\PkPeriode;

class PeriodeController extends Controller
{
    /**
     * Display a listing of periode
     */
    public function index(Request $request)
    {
        $query = PkPeriode::with(['pembuka', 'penutup'])
            ->withCount('perjanjianKinerja');

        // Filter tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        $query->orderBy('tahun', 'desc')->orderBy('tanggal_mulai', 'desc');

        $periodes = $query->paginate($request->get('per_page', 15));

        // Get tahun list
        $tahuns = PkPeriode::distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        // Current active periode
        $periodeAktif = PkPeriode::getPeriodeAktif();

        return view('perjanjiankinerja::periode.index', compact('periodes', 'tahuns', 'periodeAktif'));
    }

    /**
     * Show form for creating new periode
     */
    public function create()
    {
        return view('perjanjiankinerja::periode.create');
    }

    /**
     * Store new periode
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2100',
            'nama_periode' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        try {
            DB::beginTransaction();

            $periode = PkPeriode::create($validated);

            DB::commit();

            return redirect()->route('perjanjian-kinerja.periode.index')
                ->with('success', 'Periode berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal membuat periode: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified periode
     */
    public function show($id)
    {
        $periode = PkPeriode::with(['pembuka', 'penutup', 'perjanjianKinerja.pegawai'])
            ->withCount('perjanjianKinerja')
            ->findOrFail($id);

        return view('perjanjiankinerja::periode.show', compact('periode'));
    }

    /**
     * Show form for editing periode
     */
    public function edit($id)
    {
        $periode = PkPeriode::with(['pembuka', 'penutup'])
            ->withCount('perjanjianKinerja')
            ->findOrFail($id);

        // Check if periode is active - allow editing tanggal_selesai only
        // Full form will be read-only for active periode

        return view('perjanjiankinerja::periode.edit', compact('periode'));
    }

    /**
     * Update periode
     */
    public function update(Request $request, $id)
    {
        $periode = PkPeriode::findOrFail($id);

        // Check if periode is active
        if ($periode->is_active) {
            return redirect()->back()
                ->with('error', 'Tidak dapat mengedit periode yang sedang aktif');
        }

        $validated = $request->validate([
            'tahun' => 'required|integer|min:2020|max:2100',
            'nama_periode' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        try {
            DB::beginTransaction();

            $periode->update($validated);

            DB::commit();

            return redirect()->route('perjanjian-kinerja.periode.index')
                ->with('success', 'Periode berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui periode: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete periode
     */
    public function destroy($id)
    {
        try {
            $periode = PkPeriode::findOrFail($id);

            // Check if periode is active
            if ($periode->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus periode yang sedang aktif'
                ], 422);
            }

            // Check if there are PK associated
            if ($periode->perjanjianKinerja()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus periode yang memiliki perjanjian kinerja'
                ], 422);
            }

            $periode->delete();

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus periode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buka periode (aktivasi)
     */
    public function buka($id)
    {
        try {
            DB::beginTransaction();

            $periode = PkPeriode::findOrFail($id);

            // Check if there's already an active periode for the same year
            $existingActive = PkPeriode::where('tahun', $periode->tahun)
                ->where('is_active', true)
                ->where('id', '!=', $id)
                ->first();

            if ($existingActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sudah ada periode aktif untuk tahun ' . $periode->tahun
                ], 422);
            }

            $periode->update([
                'is_active' => true,
                'status' => 'Aktif',
                'dibuka_oleh' => Auth::id(),
                'dibuka_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil dibuka'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka periode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tutup periode (deaktivasi)
     */
    public function tutup($id)
    {
        try {
            DB::beginTransaction();

            $periode = PkPeriode::findOrFail($id);

            if (!$periode->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Periode sudah ditutup'
                ], 422);
            }

            $periode->update([
                'is_active' => false,
                'status' => 'Ditutup',
                'ditutup_oleh' => Auth::id(),
                'ditutup_pada' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil ditutup'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup periode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get periode aktif (for API)
     */
    public function getPeriodeAktif(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $periode = PkPeriode::getPeriodeAktif($tahun);

        return response()->json([
            'success' => true,
            'data' => $periode
        ]);
    }
}
