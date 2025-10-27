<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasTambahan;

class PenugasanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('penugasan::index');
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
        return view('penugasan::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}

    /**
     * Berikan tugas (harian atau tambahan) ke pegawai
     */
    public function berikanTugas(Request $request)
    {
        // Validasi berdasarkan jenis tugas
        $rules = [
            'jenis_tugas' => 'required|in:tugas_harian,tugas_tambahan',
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'nama_tugas' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'deadline' => 'required|date|after_or_equal:tanggal_mulai',
            // Ganti bobot_persen dengan target penilaian
            'target_penilaian' => 'nullable|numeric|min:0|max:100',
            'target_value' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:50',
        ];

        // Tambahan validasi untuk tugas harian
        if ($request->jenis_tugas === 'tugas_harian') {
            $rules['tugas_pokok_id'] = 'required|exists:knj_tugas_pokok,id';
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            // Get pemberi tugas id from authenticated user (MasterPegawai)
            $pemberiTugasId = Auth::id(); // ID dari MasterPegawai yang login

            // Validasi pemberi tugas
            if (!$pemberiTugasId) {
                throw new \Exception('Anda harus login untuk memberikan tugas.');
            }

            if ($validated['jenis_tugas'] === 'tugas_harian') {
                // Verifikasi bahwa tugas pokok memang milik pegawai yang dituju
                $tugasPokok = \Modules\Penugasan\Models\TugasPokok::where('id', $validated['tugas_pokok_id'])
                    ->where('pegawai_id', $validated['pegawai_id'])
                    ->first();

                if (!$tugasPokok) {
                    throw new \Exception('Tugas pokok tidak sesuai dengan pegawai yang dipilih');
                }

                // Buat tugas harian
                $tugasHarian = TugasHarian::create([
                    'tugas_pokok_id' => $validated['tugas_pokok_id'],
                    'pegawai_id' => $validated['pegawai_id'],
                    'pemberi_tugas_id' => $pemberiTugasId,
                    'nama_tugas' => $validated['nama_tugas'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'periode_type' => 'Harian',
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'deadline' => $validated['deadline'],
                    'target_penilaian' => $validated['target_penilaian'] ?? null,
                    'target_value' => $validated['target_value'],
                    'satuan' => $validated['satuan'],
                    'status' => 'pending', // Sesuai dengan enum di migrasi
                ]);

                $message = 'Tugas harian berhasil diberikan kepada pegawai';
            } else {
                // Buat tugas tambahan
                $tugasTambahan = TugasTambahan::create([
                    'pegawai_id' => $validated['pegawai_id'],
                    'pemberi_tugas_id' => $pemberiTugasId,
                    'nama_tugas' => $validated['nama_tugas'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
                    'tanggal_mulai' => $validated['tanggal_mulai'],
                    'deadline' => $validated['deadline'],
                    'target_penilaian' => $validated['target_penilaian'] ?? null,
                    'status' => 'pending', // Sesuai dengan enum di migrasi
                ]);

                $message = 'Tugas tambahan berhasil diberikan kepada pegawai';
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal memberikan tugas: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validasi tugas oleh atasan
     */
    public function validasiTugas(Request $request, $id)
    {
        $validated = $request->validate([
            'jenis_tugas' => 'required|in:tugas_harian,tugas_tambahan',
            'catatan_validasi' => 'nullable|string',
            'penilaian' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            $modelClass = $validated['jenis_tugas'] === 'tugas_harian'
                ? TugasHarian::class
                : TugasTambahan::class;

            $tugas = $modelClass::findOrFail($id);

            // Validasi bahwa yang melakukan validasi adalah pemberi tugas
            if ($tugas->pemberi_tugas_id !== Auth::id()) {
                throw new \Exception('Anda tidak berhak memvalidasi tugas ini');
            }

            $tugas->update([
                'validasi_oleh' => Auth::id(),
                'tanggal_validasi' => now(),
                'catatan_validasi' => $validated['catatan_validasi'],
                'penilaian' => $validated['penilaian'] ?? null,
                'status' => 'validasi', // Update status menjadi validasi
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Validasi berhasil']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Berikan catatan monitoring
     */
    public function catatanMonitoring(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'tugas_id' => 'nullable|integer',
            'tugas_type' => 'nullable|in:tugas_pokok,tugas_harian,tugas_tambahan',
            'jenis_catatan' => 'required|in:monitoring,revisi,feedback',
            'isi_catatan' => 'required|string',
        ]);

        // Simpan ke tabel catatan monitoring
        \Modules\Penugasan\Models\CatatanMonitoring::create([
            'pegawai_id' => $validated['pegawai_id'],
            'tugas_id' => $validated['tugas_id'],
            'tugas_type' => $validated['tugas_type'],
            'catatan_oleh' => Auth::id(),
            'tanggal_catatan' => now(),
            'jenis_catatan' => $validated['jenis_catatan'],
            'isi_catatan' => $validated['isi_catatan'],
        ]);

        return response()->json(['success' => true, 'message' => 'Catatan berhasil disimpan']);
    }

    /**
     * Dashboard monitoring untuk Kaban
     */
    public function dashboardMonitoring(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $bulan = $request->get('bulan', date('m'));

        // Statistik umum
        $stats = [
            'total_pegawai' => \App\Models\MasterPegawai::where('status_aktif', 'Aktif')->count(),
            'tugas_harian_total' => TugasHarian::whereYear('tanggal_mulai', $tahun)->count(),
            'tugas_tambahan_total' => TugasTambahan::whereYear('tanggal_mulai', $tahun)->count(),
            'menunggu_validasi' => TugasHarian::where('status_validasi', 'menunggu')->count() +
                TugasTambahan::where('status_validasi', 'menunggu')->count(),
        ];

        // Penilaian bulanan pegawai
        $penilaianBulanan = \App\Models\MasterPegawai::with(['tugasHarian', 'tugasTambahan'])
            ->where('status_aktif', 'Aktif')
            ->get()
            ->map(function ($pegawai) use ($tahun, $bulan) {
                $tugasHarian = $pegawai->tugasHarian()
                    ->whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->whereNotNull('penilaian')
                    ->avg('penilaian');

                $tugasTambahan = $pegawai->tugasTambahan()
                    ->whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->whereNotNull('penilaian')
                    ->avg('penilaian');

                return [
                    'pegawai' => $pegawai,
                    'rata_rata_harian' => round($tugasHarian ?? 0, 2),
                    'rata_rata_tambahan' => round($tugasTambahan ?? 0, 2),
                    'rata_rata_total' => round((($tugasHarian ?? 0) + ($tugasTambahan ?? 0)) / 2, 2),
                ];
            });

        return view('penugasan::monitoring.dashboard', compact('stats', 'penilaianBulanan', 'tahun', 'bulan'));
    }
}
