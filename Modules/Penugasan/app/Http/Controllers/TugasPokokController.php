<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Modules\Penugasan\Models\TugasPokok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TugasPokokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request, $id)
    {
        //
    }

    public function sinkronData()
    {
        $created = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            // Query dengan join yang benar: pk_indikator -> pk_sasaran -> pk_perjanjian_kinerja
            $rows = DB::table('pk_indikator as i')
                ->join('pk_sasaran as s', 'i.sasaran_id', '=', 's.id')
                ->join('pk_perjanjian_kinerja as p', 's.perjanjian_kinerja_id', '=', 'p.id')
                ->join('master_pegawai as peg', 'p.pegawai_id', '=', 'peg.id')
                ->where('peg.status_aktif', 'Aktif')
                ->where('p.status_dokumen', '!=', 'Draft') // Hanya sinkron yang sudah bukan draft
                ->whereNull('p.deleted_at') // Tidak yang sudah dihapus
                ->select(
                    'p.id as perjanjian_kinerja_id',
                    'i.id as indikator_id',
                    'p.pegawai_id',
                    DB::raw("COALESCE(NULLIF(i.indikator_sasaran,''), concat('Indikator ', i.id)) as indikator_nama"),
                    DB::raw("COALESCE(i.keterangan, '') as indikator_deskripsi"),
                    'p.periode_mulai as perjanjian_periode_mulai',
                    'p.periode_selesai as perjanjian_periode_selesai',
                    DB::raw("COALESCE(i.target_value, 0) as indikator_target"),
                    DB::raw("COALESCE(i.satuan, '-') as indikator_satuan")
                )
                ->get();

            foreach ($rows as $r) {
                $exists = TugasPokok::where('perjanjian_kinerja_id', $r->perjanjian_kinerja_id)
                    ->where('indikator_id', $r->indikator_id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $periode_mulai = $r->perjanjian_periode_mulai ?? \Carbon\Carbon::now()->startOfYear()->toDateString();
                $periode_selesai = $r->perjanjian_periode_selesai ?? \Carbon\Carbon::now()->endOfYear()->toDateString();

                TugasPokok::create([
                    'perjanjian_kinerja_id' => $r->perjanjian_kinerja_id,
                    'pegawai_id' => $r->pegawai_id,
                    'indikator_id' => $r->indikator_id,
                    'nama_tugas' => $r->indikator_nama ?? 'Tugas indikator ' . $r->indikator_id,
                    'deskripsi' => $r->indikator_deskripsi ?? '',
                    'bobot_persen' => 60, // Default bobot, bisa disesuaikan
                    'tanggal_mulai' => $periode_mulai,
                    'tanggal_selesai' => $periode_selesai,
                    'target_value' => $r->indikator_target ?? 0,
                    'satuan' => $r->indikator_satuan ?? '-',
                ]);

                $created++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sinkron selesai',
                'created' => $created,
                'skipped' => $skipped,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Sinkron gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update status tugas pokok
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,dikerjakan,selesai'
        ]);

        $tugasPokok = TugasPokok::findOrFail($id);
        $tugasPokok->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'message' => 'Status tugas berhasil diperbarui'
        ]);
    }
}
