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
            'bobot_persen' => 'nullable|numeric|min:0|max:100',
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
                    'bobot_persen' => $validated['bobot_persen'] ?? 0,
                    'target_value' => $validated['target_value'],
                    'satuan' => $validated['satuan'],
                    'status' => 'Assigned',
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
                    'bobot_persen' => $validated['bobot_persen'] ?? 0,
                    'target_value' => $validated['target_value'],
                    'satuan' => $validated['satuan'],
                    'status' => 'Assigned',
                    'prioritas' => 'Normal',
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
}
