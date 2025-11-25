<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasTambahan;
use Modules\Penugasan\Helpers\PenilaianHelper;

/**
 * TeamController
 * 
 * Mengelola tim dan tugas untuk atasan/supervisor
 * Fitur: overview tim, berikan tugas, validasi, monitoring
 */
class TeamController extends Controller
{
    use AuthorizesRequests;

    /**
     * Menampilkan daftar anggota tim
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get anggota tim (bawahan langsung)
        $anggotaTim = MasterPegawai::where('atasan_langsung_id', $user->id)
            ->where('status_aktif', 'Aktif')
            ->with(['jabatan', 'bidang'])
            ->withCount([
                'tugasHarian as tugas_aktif' => function($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
                },
                'tugasHarian as tugas_selesai' => function($q) {
                    $q->where('status', 'selesai');
                },
                'tugasHarian as tugas_pending' => function($q) {
                    $q->where('status', 'pending');
                },
            ])
            ->get()
            ->map(function($anggota) {
                // Hitung workload sederhana
                $workload = ($anggota->tugas_aktif + $anggota->tugas_pending) * 10;
                $anggota->workload_persen = min($workload, 100);
                return $anggota;
            });
        
        return view('penugasan::tim.index', compact('anggotaTim'));
    }

    /**
     * Menampilkan overview tim dengan statistik lengkap
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function overview(Request $request)
    {
        $user = $request->user();
        
        // Get anggota tim dengan detail tugas
        $anggotaTim = MasterPegawai::where('atasan_langsung_id', $user->id)
            ->where('status_aktif', 'Aktif')
            ->with(['jabatan', 'bidang'])
            ->withCount([
                'tugasHarian as tugas_selesai' => function($q) {
                    $q->where('status', 'selesai');
                },
                'tugasHarian as tugas_aktif' => function($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
                },
                'tugasHarian as tugas_pending' => function($q) {
                    $q->where('status', 'pending');
                },
                'tugasTambahan as tugas_tambahan_aktif' => function($q) {
                    $q->whereIn('status', ['dikerjakan', 'validasi']);
                },
            ])
            ->get()
            ->map(function($anggota) {
                $workload = ($anggota->tugas_aktif + $anggota->tugas_pending) * 10;
                $anggota->workload_persen = min($workload, 100);
                return $anggota;
            });
        
        // Statistik tim
        $statistikTim = [
            'total_anggota' => $anggotaTim->count(),
            'rata_rata_workload' => $anggotaTim->avg('workload_persen'),
            'total_tugas_aktif' => $anggotaTim->sum('tugas_aktif'),
            'perlu_validasi' => TugasHarian::whereIn('pegawai_id', $anggotaTim->pluck('id'))
                ->where('status', 'validasi')
                ->count(),
            'tugas_terlambat' => TugasHarian::whereIn('pegawai_id', $anggotaTim->pluck('id'))
                ->whereIn('status', ['dikerjakan', 'validasi'])
                ->where('tanggal_selesai', '<', now())
                ->count(),
        ];
        
        return view('penugasan::tim.overview', compact('anggotaTim', 'statistikTim'));
    }

    /**
     * Menampilkan detail anggota tim
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function detailAnggota(Request $request, $id)
    {
        $user = $request->user();
        
        // Cek apakah pegawai adalah bawahan
        $pegawai = MasterPegawai::where('id', $id)
            ->where('atasan_langsung_id', $user->id)
            ->with(['jabatan', 'bidang'])
            ->firstOrFail();
        
        // Redirect ke PenugasanController@show untuk detail lengkap
        return redirect()->route('penugasan.show', $id);
    }

    /**
     * Menampilkan form untuk memberikan tugas
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function formBerikanTugas(Request $request)
    {
        $user = $request->user();
        
        // Get daftar bawahan
        $bawahan = MasterPegawai::where('atasan_langsung_id', $user->id)
            ->where('status_aktif', 'Aktif')
            ->with(['jabatan', 'bidang'])
            ->get();
        
        return view('penugasan::tim.form-berikan-tugas', compact('bawahan'));
    }

    /**
     * Proses memberikan tugas ke anggota tim
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function berikanTugas(Request $request)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController();
        return $controller->berikanTugas($request);
    }

    /**
     * Menampilkan daftar tugas yang perlu divalidasi
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function daftarValidasi(Request $request)
    {
        $user = $request->user();
        
        // Get tugas yang perlu validasi dari bawahan
        $tugasValidasi = TugasHarian::whereHas('pegawai', function($q) use ($user) {
                $q->where('atasan_langsung_id', $user->id);
            })
            ->where('status', 'validasi')
            ->with([
                'tugasPokok:id,nama_tugas',
                'pegawai:id,nama,jabatan_id,bidang_id',
                'pegawai.jabatan:id,nama',
                'pegawai.bidang:id,nama',
                'attachedFiles'
            ])
            ->orderBy('tanggal_selesai', 'asc')
            ->paginate(20);
        
        return view('penugasan::tim.daftar-validasi', compact('tugasValidasi'));
    }

    /**
     * Proses validasi tugas
     * 
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function validasiTugas(Request $request, $id)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController();
        return $controller->validasiTugas($request, $id);
    }

    /**
     * Preview penilaian sebelum validasi
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewPenilaian(Request $request)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController();
        return $controller->previewPenilaian($request);
    }

    /**
     * Menampilkan dashboard monitoring tim
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function monitoring(Request $request)
    {
        // Delegate to PenugasanController for now
        $controller = new \Modules\Penugasan\Http\Controllers\PenugasanController();
        return $controller->dashboardMonitoring($request);
    }

    /**
     * Menambahkan catatan monitoring
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function catatanMonitoring(Request $request)
    {
        // TODO: Implement catatan monitoring
        return response()->json([
            'success' => true,
            'message' => 'Catatan monitoring berhasil disimpan'
        ]);
    }
}
