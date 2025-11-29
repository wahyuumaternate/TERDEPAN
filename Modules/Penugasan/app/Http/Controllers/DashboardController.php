<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Penugasan\Models\TugasPokok;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasTambahan;

/**
 * DashboardController
 * 
 * Mengelola halaman dashboard modul penugasan
 * Menampilkan statistik, progress, dan tugas mendesak
 */
class DashboardController extends Controller
{
    /**
     * Menampilkan halaman dashboard penugasan
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Cache statistik untuk 5 menit
        $stats = Cache::remember("dashboard_stats_{$user->id}", 300, function () use ($user) {
            return [
                'tugas_pokok' => [
                    'total' => TugasPokok::where('pegawai_id', $user->id)->count(),
                    'aktif' => TugasPokok::where('pegawai_id', $user->id)
                        ->where('status', 'dikerjakan')
                        ->count(),
                    'selesai' => TugasPokok::where('pegawai_id', $user->id)
                        ->where('status', 'selesai')
                        ->count(),
                ],
                'tugas_harian' => [
                    'total' => TugasHarian::where('pegawai_id', $user->id)->count(),
                    'pending' => TugasHarian::where('pegawai_id', $user->id)
                        ->where('status', 'pending')
                        ->count(),
                    'dikerjakan' => TugasHarian::where('pegawai_id', $user->id)
                        ->where('status', 'dikerjakan')
                        ->count(),
                    'validasi' => TugasHarian::where('pegawai_id', $user->id)
                        ->where('status', 'validasi')
                        ->count(),
                    'selesai' => TugasHarian::where('pegawai_id', $user->id)
                        ->where('status', 'selesai')
                        ->count(),
                ],
                'tugas_tambahan' => [
                    'total' => TugasTambahan::where('pegawai_id', $user->id)->count(),
                    'aktif' => TugasTambahan::where('pegawai_id', $user->id)
                        ->whereIn('status', ['dikerjakan', 'validasi'])
                        ->count(),
                    'selesai' => TugasTambahan::where('pegawai_id', $user->id)
                        ->where('status', 'selesai')
                        ->count(),
                ],
                'nilai_rata_rata' => TugasHarian::where('pegawai_id', $user->id)
                    ->whereNotNull('nilai_akhir')
                    ->avg('nilai_akhir'),
            ];
        });

        // Tugas baru/pending (diberikan oleh atasan atau status pending)
        // Tugas Harian dengan status pending atau baru (created_at < 7 hari)
        $tugasBaruHarian = TugasHarian::where('pegawai_id', $user->id)
            ->where('status', 'pending')
            ->where('is_mandiri', false) // Hanya tugas yang diberikan atasan
            ->with(['pemberiTugas:id,nama', 'tugasPokok:id,nama_tugas'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($tugas) {
                return [
                    'id' => $tugas->id,
                    'nama_tugas' => $tugas->nama_tugas,
                    'jenis' => 'harian',
                    'status' => $tugas->status,
                    'tanggal_selesai' => $tugas->tanggal_selesai,
                    'pemberi_tugas' => $tugas->pemberiTugas->nama ?? '-',
                    'created_at' => $tugas->created_at,
                ];
            });

        // Tugas Tambahan dengan status pending atau baru
        $tugasBaruTambahan = TugasTambahan::where('pegawai_id', $user->id)
            ->where('status', 'pending')
            ->with(['pemberiTugas:id,nama'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($tugas) {
                return [
                    'id' => $tugas->id,
                    'nama_tugas' => $tugas->nama_tugas,
                    'jenis' => 'tambahan',
                    'status' => $tugas->status,
                    'tanggal_selesai' => $tugas->tanggal_selesai,
                    'pemberi_tugas' => $tugas->pemberiTugas->nama ?? '-',
                    'created_at' => $tugas->created_at,
                ];
            });

        // Gabungkan dan urutkan berdasarkan created_at
        $tugasBaruPending = $tugasBaruHarian->concat($tugasBaruTambahan)
            ->sortByDesc('created_at')
            ->take(10)
            ->values();

        // Progress mingguan (4 minggu terakhir)
        $progressMingguan = DB::table('knj_progress')
            ->select([
                DB::raw('DATE_TRUNC(\'week\', tanggal) as minggu'),
                DB::raw('AVG(progress_persen) as avg_progress')
            ])
            ->where('pegawai_id', $user->id)
            ->where('tanggal', '>=', now()->subWeeks(4))
            ->groupBy(DB::raw('DATE_TRUNC(\'week\', tanggal)'))
            ->orderBy('minggu')
            ->get();

        // Check if view exists, if not use default dashboard view
        if (view()->exists('dashboard')) {
            return view('dashboard', compact(
                'stats',
                'tugasBaruPending',
                'progressMingguan'
            ));
        }
        
        return view('penugasan::dashboard', compact(
            'stats',
            'tugasBaruPending',
            'progressMingguan'
        ));
    }
}
