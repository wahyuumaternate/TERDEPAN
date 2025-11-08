<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
                    'is_mandiri' => false, // Tugas dari atasan
                    'nama_tugas' => $validated['nama_tugas'],
                    'deskripsi' => $validated['deskripsi'] ?? null,
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
     * Upload bukti pengerjaan tugas
     * UPDATED: Menggunakan Terminal Data (td_files) dengan polymorphic relation
     */
    public function uploadBukti(Request $request)
    {
        $validated = $request->validate([
            'tugas_id' => 'required|integer',
            'jenis_tugas' => 'required|in:tugas_harian,tugas_tambahan',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls|max:10240',
            'folder_ids' => 'required|array|min:1',
            'folder_ids.*' => 'required|uuid|exists:td_folders,id',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $modelClass = $validated['jenis_tugas'] === 'tugas_harian'
                ? \Modules\Penugasan\Models\TugasHarian::class
                : \Modules\Penugasan\Models\TugasTambahan::class;

            $tugas = $modelClass::findOrFail($validated['tugas_id']);

            // Validasi pegawai
            if ($tugas->pegawai_id !== Auth::id()) {
                throw new \Exception('Anda tidak memiliki izin untuk upload bukti tugas ini');
            }

            if (!in_array($tugas->status, ['dikerjakan', 'revisi'])) {
                throw new \Exception('Status tugas harus dikerjakan atau revisi untuk dapat upload bukti');
            }

            $isRevision = $tugas->status === 'revisi';
            $uploadedFiles = [];

            // Process each uploaded file
            foreach ($request->file('files') as $index => $file) {
                $folderId = $validated['folder_ids'][$index] ?? $validated['folder_ids'][0];

                // Upload file ke storage
                $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $filePath = $file->store('terminal-data', 'public');

                // Create TdFile record (polymorphic ke tugas)
                $tdFile = \Modules\TerminalData\Models\TdFile::create([
                    'folder_id' => $folderId,
                    'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'original_name' => $file->getClientOriginalName(),
                    'description' => $validated['keterangan'],
                    'storage_path' => $filePath,
                    'extension' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'hash' => hash_file('sha256', $file->getRealPath()),
                    'version' => $isRevision ? 2 : 1,
                    'is_latest_version' => true,
                    'version_notes' => $isRevision ? 'Revisi: ' . $validated['keterangan'] : null,
                    'created_by' => Auth::id(),
                    // Polymorphic relation
                    'attachable_type' => $modelClass,
                    'attachable_id' => $tugas->id,
                ]);

                $uploadedFiles[] = $tdFile;
            }

            // Update status tugas
            $tugas->update(['status' => 'validasi']);

            // Buat record progress dengan polymorphic relation
            \Modules\Penugasan\Models\Progress::create([
                'tipe_progress' => $modelClass,
                'tipe_progress_id' => $tugas->id,
                'pegawai_id' => $tugas->pegawai_id,
                'tanggal' => now(),
                'progress_persen' => 100.00,
                'deskripsi_kegiatan' => "Upload bukti: {$validated['keterangan']} (" . count($uploadedFiles) . " file)",
            ]);

            // Simpan history revisi jika ini revisi
            if ($isRevision) {
                $this->saveRevisionHistory($tugas, $validated, $modelClass);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bukti berhasil diupload. Status tugas diubah menjadi menunggu validasi.',
                'files' => $uploadedFiles,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Gagal upload bukti: ' . $e->getMessage(),
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
            'status_validasi' => 'required|in:diterima,revisi',
            'penilaian' => 'required_if:status_validasi,diterima|nullable|numeric|min:0|max:100',
            'catatan_validasi' => 'nullable|string|max:1000',
            'catatan_revisi' => 'required_if:status_validasi,revisi|nullable|string|max:1000',
            'progress_update_type' => 'required_if:status_validasi,diterima|nullable|in:otomatis,manual',
            'progress_value' => 'required_if:progress_update_type,manual|nullable|numeric|min:0',
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

            // Validasi bahwa tugas dalam status validasi
            if ($tugas->status !== 'validasi') {
                throw new \Exception('Tugas harus dalam status validasi untuk dapat divalidasi');
            }

            if ($validated['status_validasi'] === 'diterima') {
                // VALIDASI DITERIMA - Status jadi selesai
                $tugas->update([
                    'status' => 'selesai',
                    'validator_id' => Auth::id(),
                    'validated_at' => now(),
                    'hasil_validasi' => 'diterima',
                    'penilaian_kualitas' => $validated['penilaian'], // Score 1-5 atau bisa mapping dari 0-100
                    'nilai_akhir' => $validated['penilaian'],
                    'catatan_validasi' => $validated['catatan_validasi'] ?? 'Tugas diterima dan selesai',
                ]);

                // Update progress tugas pokok jika tugas harian
                if ($validated['jenis_tugas'] === 'tugas_harian') {
                    $this->updateProgressTugasPokok($tugas, $validated);
                }

                $message = 'Tugas berhasil divalidasi dan diselesaikan dengan nilai: ' . $validated['penilaian'];
            } else {
                // VALIDASI REVISI - Status jadi revisi
                $tugas->update([
                    'status' => 'revisi',
                    'validator_id' => Auth::id(),
                    'validated_at' => now(),
                    'hasil_validasi' => 'revisi',
                    'catatan_validasi' => $validated['catatan_revisi'] ?? 'Perlu revisi',
                ]);

                // Simpan history revisi dengan model class
                $jenisTugas = $validated['jenis_tugas'] === 'tugas_harian' 
                    ? TugasHarian::class 
                    : TugasTambahan::class;
                
                $this->saveRevisionHistory($tugas, $validated, $jenisTugas);

                $message = 'Tugas dikembalikan untuk revisi';
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $tugas->status
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update progress tugas pokok berdasarkan penyelesaian tugas harian
     */
    private function updateProgressTugasPokok($tugasHarian, $validated)
    {
        $tugasPokok = $tugasHarian->tugasPokok;

        if ($validated['progress_update_type'] === 'otomatis') {
            // Otomatis: hitung berdasarkan target value
            $progressValue = $tugasHarian->target_value;
        } else {
            // Manual: gunakan nilai yang diinput atasan
            $progressValue = $validated['progress_value'];
        }

        // Update atau create progress tugas pokok dengan polymorphic
        \Modules\Penugasan\Models\Progress::create([
            'tipe_progress' => \Modules\Penugasan\Models\TugasPokok::class,
            'tipe_progress_id' => $tugasPokok->id,
            'pegawai_id' => $tugasHarian->pegawai_id,
            'tanggal' => now(),
            'progress_persen' => 100.00,
            'deskripsi_kegiatan' => "Penyelesaian tugas harian: {$tugasHarian->nama_tugas} (Nilai: {$tugasHarian->nilai_akhir})",
        ]);

        // Update total progress tugas pokok
        $this->recalculateProgressTugasPokok($tugasPokok);
    }

    /**
     * Recalculate total progress tugas pokok
     */
    private function recalculateProgressTugasPokok($tugasPokok)
    {
        // Hitung rata-rata nilai dari semua tugas harian yang selesai
        $avgNilai = TugasHarian::where('tugas_pokok_id', $tugasPokok->id)
            ->where('status', 'selesai')
            ->whereNotNull('nilai_akhir')
            ->avg('nilai_akhir');

        if ($avgNilai) {
            $tugasPokok->update([
                'progress_persen' => round($avgNilai, 2),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Simpan history revisi dengan polymorphic relation
     */
    private function saveRevisionHistory($tugas, $validated, $modelClass)
    {
        if (class_exists('\Modules\Penugasan\Models\HistoriRevisi')) {
            \Modules\Penugasan\Models\HistoriRevisi::create([
                'tipe_revisi' => $modelClass,
                'tipe_revisi_id' => $tugas->id,
                'revisi_ke' => $this->getNextRevisionNumber($tugas, $modelClass),
                'tanggal_revisi' => now(),
                'catatan_revisi' => $validated['catatan_revisi'] ?? $validated['catatan_validasi'] ?? 'Revisi',
                'deadline_revisi' => now()->addDays(3), // Default 3 hari untuk revisi
                'direvisi_oleh' => Auth::id(),
                'pegawai_id' => $tugas->pegawai_id,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Get next revision number
     */
    private function getNextRevisionNumber($tugas, $modelClass)
    {
        if (!class_exists('\Modules\Penugasan\Models\HistoriRevisi')) {
            return 1;
        }

        $lastRevision = \Modules\Penugasan\Models\HistoriRevisi::where('tipe_revisi', $modelClass)
            ->where('tipe_revisi_id', $tugas->id)
            ->max('revisi_ke');

        return ($lastRevision ?? 0) + 1;
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
            'menunggu_validasi' => TugasHarian::where('status', 'validasi')->count() +
                TugasTambahan::where('status', 'validasi')->count(),
        ];

        // Penilaian bulanan pegawai
        $penilaianBulanan = \App\Models\MasterPegawai::with(['tugasHarian', 'tugasTambahan'])
            ->where('status_aktif', 'Aktif')
            ->get()
            ->map(function ($pegawai) use ($tahun, $bulan) {
                $tugasHarian = TugasHarian::where('pegawai_id', $pegawai->id)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->whereNotNull('nilai_akhir')
                    ->avg('nilai_akhir');

                $tugasTambahan = TugasTambahan::where('pegawai_id', $pegawai->id)
                    ->whereYear('tanggal_mulai', $tahun)
                    ->whereMonth('tanggal_mulai', $bulan)
                    ->whereNotNull('nilai_akhir')
                    ->avg('nilai_akhir');

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
