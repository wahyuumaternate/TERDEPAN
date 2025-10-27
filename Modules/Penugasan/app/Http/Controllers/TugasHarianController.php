<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TugasHarianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \Modules\Penugasan\Models\TugasHarian::with(['tugasPokok', 'pegawai', 'pemberiTugas', 'dokumenLampiran'])
            ->orderBy('tanggal_mulai', 'desc');

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_tugas', 'like', "%{$request->search}%")
                    ->orWhere('deskripsi', 'like', "%{$request->search}%");
            });
        }

        $tugasHarian = $query->paginate($request->get('per_page', 15));

        return view('penugasan::tugas-harian.index', compact('tugasHarian'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('penugasan::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('penugasan::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::with(['tugasPokok', 'pegawai', 'pemberiTugas'])
                ->findOrFail($id);

            // Return JSON response for AJAX request
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json($tugasHarian);
            }

            // Return view for regular request (fallback)
            return view('penugasan::penugasan.tugas-harian.edit', compact('tugasHarian'));
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tugas harian tidak ditemukan'
                ], 404);
            }
            return redirect()->back()->with('error', 'Tugas harian tidak ditemukan');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nama_tugas' => 'required|string|max:500',
                'deskripsi' => 'nullable|string',
                'tanggal_mulai' => 'required|date',
                'deadline' => 'required|date|after_or_equal:tanggal_mulai',
                'target_value' => 'required|numeric|min:0',
                'satuan' => 'required|string|max:100',
            ]);

            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);
            $tugasHarian->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Tugas harian berhasil diperbarui'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);

            // Check if user has permission to delete
            // Only pemberi_tugas or admin can delete
            $currentUserId = \Illuminate\Support\Facades\Auth::id();
            if ($tugasHarian->pemberi_tugas_id !== $currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki izin untuk menghapus tugas ini'
                ], 403);
            }

            $tugasHarian->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tugas harian berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status tugas harian
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,dikerjakan,validasi,revisi,selesai'
        ]);

        $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);
        $tugasHarian->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status tugas harian berhasil diperbarui'
        ]);
    }

    /**
     * Update progress tugas harian
     */
    public function updateProgress(Request $request, $id)
    {
        $validated = $request->validate([
            'persentase_progress' => 'required|numeric|min:0|max:100',
            'keterangan' => 'nullable|string',
        ]);

        $tugasHarian = \Modules\Penugasan\Models\TugasHarian::findOrFail($id);

        // Create progress record
        $tugasHarian->progress()->create([
            'tanggal_update' => now(),
            'persentase_progress' => $validated['persentase_progress'],
            'keterangan' => $validated['keterangan'],
            'updated_by' => \Illuminate\Support\Facades\Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress tugas berhasil diperbarui'
        ]);
    }
}
