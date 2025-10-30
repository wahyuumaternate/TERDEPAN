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
     * Upload bukti pengerjaan tugas
     */
    public function uploadBukti(Request $request)
    {
        $validated = $request->validate([
            'tugas_id' => 'required|integer',
            'jenis_tugas' => 'required|in:tugas_harian,tugas_tambahan',
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls|max:10240', // Max 10MB per file
            'keterangan' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Tentukan model berdasarkan jenis tugas
            $modelClass = $validated['jenis_tugas'] === 'tugas_harian'
                ? TugasHarian::class
                : TugasTambahan::class;

            $tugas = $modelClass::findOrFail($validated['tugas_id']);

            // Validasi bahwa yang upload adalah penerima tugas
            if ($tugas->pegawai_id !== Auth::id()) {
                throw new \Exception('Anda tidak berhak mengupload bukti untuk tugas ini');
            }

            // Validasi status tugas harus 'dikerjakan' atau 'revisi' untuk bisa upload bukti
            if (!in_array($tugas->status, ['dikerjakan', 'revisi'])) {
                throw new \Exception('Tugas harus dalam status dikerjakan atau revisi untuk dapat mengupload bukti');
            }

            // Dapatkan informasi pegawai dan bidang
            $pegawai = \App\Models\MasterPegawai::with('bidang')->findOrFail($tugas->pegawai_id);
            $namaPegawai = str_replace(' ', '_', $pegawai->nama);

            if (!$pegawai->bidang) {
                throw new \Exception('Pegawai tidak memiliki bidang yang terdaftar');
            }

            $bidangNama = str_replace(' ', '_', strtolower($pegawai->bidang->nama));

            // Handle versioning untuk dokumen
            $isRevision = $tugas->status === 'revisi';
            $version = 1;
            $oldDokumen = null;

            if ($isRevision && $tugas->dokumen_lampiran_id) {
                // Jika ini adalah revisi, set file lama menjadi tidak current
                $oldDokumen = \Modules\Dokumen\Models\Dokumen::find($tugas->dokumen_lampiran_id);
                if ($oldDokumen) {
                    // Update file lama menjadi tidak current
                    \Modules\Dokumen\Models\File::where('dokumen_id', $oldDokumen->id)
                        ->update(['is_current' => false]);

                    // Get next version
                    $version = \Modules\Dokumen\Models\File::where('dokumen_id', $oldDokumen->id)->max('version') + 1;
                }
            }

            // Process each uploaded file
            $uploadedFiles = [];
            $dokumen = null; // Single dokumen untuk semua file

            foreach ($request->file('files') as $index => $file) {
                // Upload file ke folder bidang/eviden/nama_pegawai
                $fileName = time() . '_' . $index . '_' . $file->getClientOriginalName();
                $folderPath = "{$bidangNama}/eviden/" . str_replace(' ', '_', strtolower($namaPegawai));
                $filePath = $file->storeAs($folderPath, $fileName, 'public');

                // Create dokumen hanya sekali (untuk file pertama)
                if ($index === 0) {
                    if ($oldDokumen) {
                        // Gunakan dokumen yang sama, update info
                        $dokumen = $oldDokumen;
                        $dokumen->update([
                            'version' => $version,
                            'deskripsi' => $validated['keterangan'] . ' (Revisi ke-' . ($version - 1) . ')',
                            'tanggal_dokumen' => now(),
                        ]);
                    } else {
                        // Upload pertama kali, buat dokumen baru
                        $dokumen = $this->createNewDokumen($tugas, $validated, $namaPegawai);
                    }
                }

                // Buat record file baru (semua file menggunakan dokumen yang sama)
                \Modules\Dokumen\Models\File::create([
                    'dokumen_id' => $dokumen->id,
                    'version' => $version,
                    'nama_file' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'extension' => $file->getClientOriginalExtension(),
                    'size_kb' => round($file->getSize() / 1024),
                    'hash' => hash_file('sha256', $file->getRealPath()),
                    'is_current' => true,
                    'uploaded_by' => Auth::id(),
                ]);
            }

            // Update status tugas menjadi 'validasi' dan simpan dokumen lampiran
            $tugas->update([
                'status' => 'validasi',
                'dokumen_lampiran_id' => $dokumen->id,
            ]);

            // Buat record progress
            \Modules\Penugasan\Models\Progress::create([
                'tugas_harian_id' => $validated['jenis_tugas'] === 'tugas_harian' ? $tugas->id : null,
                'tugas_tambahan_id' => $validated['jenis_tugas'] === 'tugas_tambahan' ? $tugas->id : null,
                'pegawai_id' => $tugas->pegawai_id,
                'tanggal' => now(),
                'progress_persen' => 100.00, // Karena upload bukti berarti selesai
                'deskripsi_kegiatan' => "Upload bukti pengerjaan: {$validated['keterangan']} (" . count($request->file('files')) . " file)",
                'dokumen_bukti_id' => $dokumen->id,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bukti berhasil diupload. Status tugas diubah menjadi menunggu validasi.',
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
     * Dapatkan atau buat folder eviden untuk pegawai berdasarkan bidang
     */
    private function getOrCreateEvidenFolder($namaPegawai)
    {
        // Dapatkan informasi bidang pegawai yang sedang login
        $pegawai = \App\Models\MasterPegawai::with('bidang')->findOrFail(Auth::id());

        if (!$pegawai->bidang) {
            throw new \Exception('Pegawai tidak memiliki bidang yang terdaftar');
        }

        $bidangNama = $pegawai->bidang->nama;

        // 1. Cari atau buat folder bidang
        $bidangFolder = \Modules\Dokumen\Models\Folder::where('nama', $bidangNama)
            ->whereNull('parent_id')
            ->first();

        if (!$bidangFolder) {
            // Buat folder bidang
            $bidangFolder = \Modules\Dokumen\Models\Folder::create([
                'bidang_id' => $pegawai->bidang_id,
                'nama' => $bidangNama,
                'path' => '/' . str_replace(' ', '_', strtolower($bidangNama)),
                'level' => 0,
                'is_auto' => true,
                'created_by' => Auth::id(),
            ]);
        }

        // 2. Cari atau buat folder Eviden di dalam bidang
        $evidenFolder = \Modules\Dokumen\Models\Folder::where('nama', 'Eviden')
            ->where('parent_id', $bidangFolder->id)
            ->first();

        if (!$evidenFolder) {
            // Buat folder eviden di dalam bidang
            $evidenFolder = \Modules\Dokumen\Models\Folder::create([
                'parent_id' => $bidangFolder->id,
                'bidang_id' => $pegawai->bidang_id,
                'nama' => 'Eviden',
                'path' => $bidangFolder->path . '/eviden',
                'level' => 1,
                'is_auto' => true,
                'created_by' => Auth::id(),
            ]);
        }

        // 3. Cari atau buat folder pegawai di dalam Eviden
        $pegawaiFolder = \Modules\Dokumen\Models\Folder::where('nama', $namaPegawai)
            ->where('parent_id', $evidenFolder->id)
            ->first();

        if (!$pegawaiFolder) {
            $pegawaiFolder = \Modules\Dokumen\Models\Folder::create([
                'parent_id' => $evidenFolder->id,
                'bidang_id' => $pegawai->bidang_id,
                'nama' => $namaPegawai,
                'path' => $evidenFolder->path . '/' . str_replace(' ', '_', strtolower($namaPegawai)),
                'level' => 2,
                'is_auto' => true,
                'created_by' => Auth::id(),
            ]);
        }

        return $pegawaiFolder->id;
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
                    'validasi_oleh' => Auth::id(),
                    'tanggal_validasi' => now(),
                    'penilaian' => $validated['penilaian'],
                    'nilai_akhir' => $validated['penilaian'],
                    'tanggal_penilaian' => now(),
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
                    'validasi_oleh' => Auth::id(),
                    'tanggal_validasi' => now(),
                    'catatan_validasi' => $validated['catatan_revisi'] ?? 'Perlu revisi',
                ]);

                // Simpan history revisi
                $this->saveRevisionHistory($tugas, $validated);

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

        // Update atau create progress tugas pokok
        \Modules\Penugasan\Models\Progress::create([
            'tugas_pokok_id' => $tugasPokok->id,
            'pegawai_id' => $tugasHarian->pegawai_id,
            'tanggal' => now(),
            'progress_persen' => 100.00, // Tugas harian selesai = 100%
            'deskripsi_kegiatan' => "Penyelesaian tugas harian: {$tugasHarian->nama_tugas} (Nilai: {$tugasHarian->penilaian})",
            'dokumen_bukti_id' => $tugasHarian->dokumen_lampiran_id,
        ]);

        // Update total progress tugas pokok (bisa dihitung dari akumulasi tugas harian)
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
     * Simpan history revisi
     */
    private function saveRevisionHistory($tugas, $validated)
    {
        // Cek apakah model HistoriRevisi ada
        if (class_exists('\Modules\Penugasan\Models\HistoriRevisi')) {
            \Modules\Penugasan\Models\HistoriRevisi::create([
                'tugas_harian_id' => $validated['jenis_tugas'] === 'tugas_harian' ? $tugas->id : null,
                'tugas_tambahan_id' => $validated['jenis_tugas'] === 'tugas_tambahan' ? $tugas->id : null,
                'revisi_ke' => $this->getNextRevisionNumber($tugas, $validated['jenis_tugas']),
                'tanggal_revisi' => now(),
                'catatan_revisi' => $validated['catatan_revisi'],
                'direvisi_oleh' => Auth::id(),
                'dokumen_lama_id' => $tugas->dokumen_lampiran_id,
            ]);
        }
    }

    /**
     * Get next revision number
     */
    private function getNextRevisionNumber($tugas, $jenisTugas)
    {
        if (!class_exists('\Modules\Penugasan\Models\HistoriRevisi')) {
            return 1;
        }

        $field = $jenisTugas === 'tugas_harian' ? 'tugas_harian_id' : 'tugas_tambahan_id';

        $lastRevision = \Modules\Penugasan\Models\HistoriRevisi::where($field, $tugas->id)
            ->max('revisi_ke');

        return ($lastRevision ?? 0) + 1;
    }

    /**
     * Create new dokumen for bukti
     */
    private function createNewDokumen($tugas, $validated, $namaPegawai, $fileIndex = null)
    {
        $suffix = $fileIndex ? " (File {$fileIndex})" : "";
        return \Modules\Dokumen\Models\Dokumen::create([
            'nomor' => 'BUKTI-' . $tugas->id . '-' . time() . ($fileIndex ? "-{$fileIndex}" : ""),
            'folder_id' => $this->getOrCreateEvidenFolder($namaPegawai),
            'judul' => "Bukti Pengerjaan - {$tugas->nama_tugas}{$suffix}",
            'deskripsi' => $validated['keterangan'],
            'tanggal_dokumen' => now(),
            'status' => 'Final',
            'uploaded_by' => Auth::id(),
            'related_type' => $validated['jenis_tugas'],
            'related_id' => $tugas->id,
        ]);
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
