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

        // Progress tugas pokok dengan subquery untuk menghitung jumlah tugas harian
        $tugasPokok = TugasPokok::selectRaw('
                knj_tugas_pokok.*,
                (SELECT COUNT(*) FROM knj_tugas_harian WHERE tugas_pokok_id = knj_tugas_pokok.id) as jumlah_tugas_harian,
                (SELECT COUNT(*) FROM knj_tugas_harian WHERE tugas_pokok_id = knj_tugas_pokok.id AND status = ?) as selesai_count
            ', ['selesai'])
            ->where('pegawai_id', $user->id)
            ->with(['indikatorPK:id,indikator_sasaran', 'perjanjianKinerja:id,periode_mulai,periode_selesai'])
            ->orderBy('progress_persen', 'desc')
            ->limit(5)
            ->get();

        // Tugas mendesak (deadline < 7 hari)
        $tugasMendesak = TugasHarian::select(
            'id',
            'nama_tugas',
            'tanggal_selesai',
            'status',
            'tugas_pokok_id'
        )
            ->where('pegawai_id', $user->id)
            ->whereIn('status', ['pending', 'dikerjakan', 'revisi'])
            ->where('tanggal_selesai', '<=', now()->addDays(7))
            ->where('tanggal_selesai', '>=', now())
            ->orderBy('tanggal_selesai')
            ->limit(5)
            ->get();

        // Tugas tambahan mendesak
        $tugasTambahanMendesak = TugasTambahan::select(
            'id',
            'nama_tugas',
            'tanggal_selesai',
            'status'
        )
            ->where('pegawai_id', $user->id)
            ->whereIn('status', ['pending', 'dikerjakan', 'revisi'])
            ->where('tanggal_selesai', '<=', now()->addDays(7))
            ->where('tanggal_selesai', '>=', now())
            ->orderBy('tanggal_selesai')
            ->limit(3)
            ->get();

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

        return view('penugasan::dashboard', compact(
            'stats',
            'tugasPokok',
            'tugasMendesak',
            'tugasTambahanMendesak',
            'progressMingguan'
        ));
    }
}
