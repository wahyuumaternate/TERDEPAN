<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Penugasan\Models\Penugasan;

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
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Cache statistik untuk 5 menit
        $stats = Cache::remember("dashboard_stats_{$user->id}", 300, function () use ($user) {
            return [
                'tugas_pokok' => [
                    'total' => Penugasan::pokok()->where('pegawai_id', $user->id)->count(),
                    'aktif' => Penugasan::pokok()->where('pegawai_id', $user->id)
                        ->where('status', 'dikerjakan')
                        ->count(),
                    'selesai' => Penugasan::pokok()->where('pegawai_id', $user->id)
                        ->where('status', 'selesai')
                        ->count(),
                ],
                'tugas_harian' => [
                    'total' => Penugasan::where('pegawai_id', $user->id)->count(),
                    'pending' => Penugasan::where('pegawai_id', $user->id)
                        ->where('status', 'pending')
                        ->count(),
                    'dikerjakan' => Penugasan::where('pegawai_id', $user->id)
                        ->where('status', 'dikerjakan')
                        ->count(),
                    'validasi' => Penugasan::where('pegawai_id', $user->id)
                        ->where('status', 'validasi')
                        ->count(),
                    'selesai' => Penugasan::where('pegawai_id', $user->id)
                        ->where('status', 'selesai')
                        ->count(),
                ],
                'tugas_tambahan' => [
                    'total' => Penugasan::tambahan()->where('pegawai_id', $user->id)->count(),
                    'aktif' => Penugasan::tambahan()->where('pegawai_id', $user->id)
                        ->whereIn('status', ['dikerjakan', 'validasi'])
                        ->count(),
                    'selesai' => Penugasan::tambahan()->where('pegawai_id', $user->id)
                        ->where('status', 'selesai')
                        ->count(),
                ],
                'nilai_rata_rata' => Penugasan::where('pegawai_id', $user->id)
                    ->whereNotNull('nilai_akhir')
                    ->avg('nilai_akhir'),
            ];
        });

        // Tugas baru/pending yang diberikan oleh atasan (bukan mandiri)
        $tugasBaruPending = Penugasan::where('pegawai_id', $user->id)
            ->where('status', 'pending')
            ->whereNotNull('pemberi_tugas_id')
            ->with(['pemberiTugas:id,nama'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($tugas) {
                return [
                    'id' => $tugas->id,
                    'nama_tugas' => $tugas->nama_tugas,
                    'jenis' => $tugas->jenis,
                    'status' => $tugas->status,
                    'tanggal_selesai' => $tugas->tanggal_selesai,
                    'pemberi_tugas' => $tugas->pemberiTugas->nama ?? '-',
                    'created_at' => $tugas->created_at,
                ];
            });

        // Progress mingguan (4 minggu terakhir)
        $progressMingguan = DB::table('knj_progress')
            ->select([
                DB::raw('DATE_TRUNC(\'week\', tanggal) as minggu'),
                DB::raw('AVG(progress_persen) as avg_progress'),
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
