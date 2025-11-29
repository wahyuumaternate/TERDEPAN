<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Penugasan\Models\TugasPokok;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasTambahan;

/**
 * ApiController
 * 
 * Menyediakan API endpoints untuk AJAX calls
 * Response format: JSON
 */
class ApiController extends Controller
{
    /**
     * Mendapatkan statistik dashboard pengguna
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistik(Request $request)
    {
        $user = $request->user();

        $stats = Cache::remember("api_stats_{$user->id}", 300, function () use ($user) {
            return [
                'tugas_pokok' => [
                    'total' => TugasPokok::where('pegawai_id', $user->id)->count(),
                    'aktif' => TugasPokok::where('pegawai_id', $user->id)
                        ->where('status', 'dikerjakan')->count(),
                    'selesai' => TugasPokok::where('pegawai_id', $user->id)
                        ->where('status', 'selesai')->count(),
                ],
                'tugas_harian' => DB::table('knj_tugas_harian')
                    ->select([
                        DB::raw('COUNT(*) as total'),
                        DB::raw('SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending'),
                        DB::raw('SUM(CASE WHEN status = \'dikerjakan\' THEN 1 ELSE 0 END) as dikerjakan'),
                        DB::raw('SUM(CASE WHEN status = \'validasi\' THEN 1 ELSE 0 END) as validasi'),
                        DB::raw('SUM(CASE WHEN status = \'selesai\' THEN 1 ELSE 0 END) as selesai'),
                    ])
                    ->where('pegawai_id', $user->id)
                    ->whereNull('deleted_at')
                    ->first(),
                'tugas_tambahan' => [
                    'total' => TugasTambahan::where('pegawai_id', $user->id)->count(),
                    'aktif' => TugasTambahan::where('pegawai_id', $user->id)
                        ->whereIn('status', ['dikerjakan', 'validasi'])->count(),
                ],
                'nilai_rata_rata' => round(TugasHarian::where('pegawai_id', $user->id)
                    ->whereNotNull('nilai_akhir')
                    ->avg('nilai_akhir'), 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Mendapatkan data kalender tugas
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kalender(Request $request)
    {
        $user = $request->user();
        $start = $request->input('start', now()->startOfMonth());
        $end = $request->input('end', now()->endOfMonth());

        // Tugas harian
        $tugasHarian = TugasHarian::where('pegawai_id', $user->id)
            ->whereBetween('tanggal_selesai', [$start, $end])
            ->select('id', 'nama_tugas', 'tanggal_mulai', 'tanggal_selesai', 'status')
            ->get()
            ->map(function ($tugas) {
                return [
                    'id' => $tugas->id,
                    'title' => $tugas->nama_tugas,
                    'start' => $tugas->tanggal_mulai,
                    'end' => $tugas->tanggal_selesai,
                    'status' => $tugas->status,
                    'type' => 'harian',
                    'color' => $this->getStatusColor($tugas->status),
                ];
            });

        // Tugas tambahan
        $tugasTambahan = TugasTambahan::where('pegawai_id', $user->id)
            ->whereBetween('tanggal_selesai', [$start, $end])
            ->select('id', 'nama_tugas', 'tanggal_mulai', 'tanggal_selesai', 'status')
            ->get()
            ->map(function ($tugas) {
                return [
                    'id' => $tugas->id,
                    'title' => $tugas->nama_tugas,
                    'start' => $tugas->tanggal_mulai,
                    'end' => $tugas->tanggal_selesai,
                    'status' => $tugas->status,
                    'type' => 'tambahan',
                    'color' => $this->getStatusColor($tugas->status),
                ];
            });

        $events = $tugasHarian->merge($tugasTambahan);

        return response()->json([
            'success' => true,
            'data' => $events
        ]);
    }

    /**
     * Mendapatkan notifikasi pengguna
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function notifikasi(Request $request)
    {
        $user = $request->user();

        $notifikasi = [];

        // Tugas baru (pending)
        $tugasBaru = TugasHarian::where('pegawai_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($tugasBaru > 0) {
            $notifikasi[] = [
                'type' => 'info',
                'message' => "Anda memiliki {$tugasBaru} tugas baru yang perlu ditindaklanjuti",
                'link' => route('penugasan.tugas-harian.tugas-saya', ['status' => 'pending']),
            ];
        }

        // Tugas mendesak (deadline < 3 hari)
        $tugasMendesak = TugasHarian::where('pegawai_id', $user->id)
            ->whereIn('status', ['pending', 'dikerjakan'])
            ->where('tanggal_selesai', '<=', now()->addDays(3))
            ->where('tanggal_selesai', '>', now())
            ->count();

        if ($tugasMendesak > 0) {
            $notifikasi[] = [
                'type' => 'warning',
                'message' => "{$tugasMendesak} tugas akan jatuh tempo dalam 3 hari ke depan",
                'link' => route('penugasan.dashboard'),
            ];
        }

        // Tugas terlambat
        $tugasTerlambat = TugasHarian::where('pegawai_id', $user->id)
            ->whereIn('status', ['dikerjakan', 'validasi'])
            ->where('tanggal_selesai', '<', now())
            ->count();

        if ($tugasTerlambat > 0) {
            $notifikasi[] = [
                'type' => 'danger',
                'message' => "Ada {$tugasTerlambat} tugas yang melewati deadline",
                'link' => route('penugasan.tugas-harian.tugas-saya'),
            ];
        }

        // Tugas perlu revisi
        $tugasRevisi = TugasHarian::where('pegawai_id', $user->id)
            ->where('status', 'revisi')
            ->count();

        if ($tugasRevisi > 0) {
            $notifikasi[] = [
                'type' => 'warning',
                'message' => "{$tugasRevisi} tugas perlu diperbaiki",
                'link' => route('penugasan.tugas-harian.tugas-saya', ['status' => 'revisi']),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $notifikasi,
            'count' => count($notifikasi)
        ]);
    }

    /**
     * Mendapatkan workload tim (untuk atasan)
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function workloadTim(Request $request)
    {
        $user = $request->user();

        // Get anggota tim
        $anggotaTim = MasterPegawai::where('atasan_langsung_id', $user->id)
            ->where('status_aktif', 'Aktif')
            ->select('id', 'nama', 'jabatan_id')
            ->with('jabatan:id,nama')
            ->withCount([
                'tugasHarian as tugas_aktif' => function ($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
                },
                'tugasHarian as tugas_pending' => function ($q) {
                    $q->where('status', 'pending');
                },
            ])
            ->get()
            ->map(function ($anggota) {
                $workload = ($anggota->tugas_aktif + $anggota->tugas_pending) * 10;
                return [
                    'id' => $anggota->id,
                    'nama' => $anggota->nama,
                    'jabatan' => $anggota->jabatan->nama ?? '-',
                    'tugas_aktif' => $anggota->tugas_aktif,
                    'tugas_pending' => $anggota->tugas_pending,
                    'workload_persen' => min($workload, 100),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $anggotaTim
        ]);
    }

    /**
     * Mendapatkan progress anggota tim
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function progressAnggota(Request $request, $id)
    {
        $user = $request->user();

        // Cek apakah pegawai adalah bawahan
        $pegawai = MasterPegawai::where('id', $id)
            ->where('atasan_langsung_id', $user->id)
            ->firstOrFail();

        // Progress 30 hari terakhir
        $progress = DB::table('knj_progress')
            ->select([
                DB::raw('DATE(tanggal) as tanggal'),
                DB::raw('AVG(progress_persen) as avg_progress'),
                DB::raw('COUNT(*) as jumlah_update')
            ])
            ->where('pegawai_id', $id)
            ->where('tanggal', '>=', now()->subDays(30))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'pegawai' => [
                    'id' => $pegawai->id,
                    'nama' => $pegawai->nama,
                ],
                'progress' => $progress
            ]
        ]);
    }

    /**
     * Mendapatkan daftar tugas pokok berdasarkan pegawai
     * Digunakan untuk dropdown saat memberikan tugas harian
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function tugasPokokByPegawai(Request $request, $id)
    {
        try {
            $user = $request->user();

            // Cek apakah pegawai adalah bawahan atau user sendiri yang memiliki akses
            $pegawai = MasterPegawai::where('id', $id)->firstOrFail();

            // Authorization: hanya atasan langsung atau pegawai itu sendiri yang bisa akses
            if ($pegawai->atasan_langsung_id !== $user->id && $pegawai->id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak memiliki akses'
                ], 403);
            }

            // Get tugas pokok yang masih aktif
            $tugasPokok = TugasPokok::where('pegawai_id', $id)
                ->whereIn('status', ['dikerjakan', 'pending'])
                ->select('id', 'nama_tugas', 'progress_persen', 'status', 'satuan')
                ->orderBy('nama_tugas')
                ->get()
                ->map(function ($tugas) {
                    return [
                        'id' => $tugas->id,
                        'nama_tugas' => $tugas->nama_tugas,
                        'progress_persen' => $tugas->progress_persen ?? 0,
                        'status' => $tugas->status,
                        'satuan' => $tugas->satuan ?? '',
                    ];
                });

            return response()->json($tugasPokok);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper: Get color based on status
     * 
     * @param  string  $status
     * @return string
     */
    private function getStatusColor($status)
    {
        return match ($status) {
            'pending' => '#6c757d',    // gray
            'dikerjakan' => '#0dcaf0', // cyan
            'validasi' => '#ffc107',   // yellow
            'revisi' => '#fd7e14',     // orange
            'selesai' => '#198754',    // green
            default => '#6c757d'
        };
    }
}
