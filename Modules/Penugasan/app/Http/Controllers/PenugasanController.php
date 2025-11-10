<?php

namespace Modules\Penugasan\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MasterBidang;
use App\Models\MasterJabatan;
use App\Models\MasterPegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Penugasan\Models\TugasHarian;
use Modules\Penugasan\Models\TugasTambahan;
use Modules\Penugasan\Helpers\PenilaianHelper;
use Modules\Penugasan\Models\TugasPokok;

class PenugasanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Default filter tahun sekarang berdasarkan tanggal_mulai
        $tahun = $request->get('tahun', date('Y'));

        // Query untuk mendapatkan daftar pegawai dengan statistik tugas pokok
        $pegawaiQuery = MasterPegawai::where('status_aktif', 'Aktif')
            ->with(['jabatan', 'bidang'])
            // Kecualikan admin utama (ID 1) dan user yang sedang login
            ->where('id', '!=', 1) // Asumsikan ID 1 adalah admin utama
            ->where('id', '!=', Auth::id()) // Kecualikan user yang sedang login
            ->withCount([
                // Count tugas pokok
                'tugasPokok as tugas_pokok_count' => function ($q) use ($tahun) {
                    $q->whereYear('tanggal_mulai', $tahun);
                },
                // Count tugas harian
                'tugasHarian as tugas_harian_count' => function ($q) use ($tahun) {
                    $q->whereYear('tanggal_mulai', $tahun);
                },
                // Count tugas tambahan
                'tugasTambahan as tugas_tambahan_count' => function ($q) use ($tahun) {
                    $q->whereYear('tanggal_mulai', $tahun);
                },
                // Status tugas pokok: pending, dikerjakan, selesai
                'tugasPokok as pending_tugas' => function ($q) use ($tahun) {
                    $q->whereYear('tanggal_mulai', $tahun)
                        ->where('status', 'pending');
                },
                'tugasPokok as dikerjakan_tugas' => function ($q) use ($tahun) {
                    $q->whereYear('tanggal_mulai', $tahun)
                        ->where('status', 'dikerjakan');
                },
                'tugasPokok as selesai_tugas' => function ($q) use ($tahun) {
                    $q->whereYear('tanggal_mulai', $tahun)
                        ->where('status', 'selesai');
                }
            ]);

        // Filter by jabatan
        if ($request->filled('jabatan_id')) {
            $pegawaiQuery->where('jabatan_id', $request->jabatan_id);
        }

        // Filter by bidang
        if ($request->filled('bidang_id')) {
            $pegawaiQuery->where('bidang_id', $request->bidang_id);
        }

        // Search
        if ($request->filled('search')) {
            $pegawaiQuery->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                    ->orWhere('nip', 'like', "%{$request->search}%");
            });
        }

        // Only show pegawai yang punya tugas
        if ($request->get('has_tugas', false)) {
            $pegawaiQuery->has('tugasPokok');
        }

        // Sort - default by jabatan then nomor_identitas
        if ($request->has('sort_by')) {
            $sortBy = $request->get('sort_by');
            $sortOrder = $request->get('sort_order', 'asc');
            $pegawaiQuery->orderBy($sortBy, $sortOrder);
        } else {
            // Default sorting: jabatan_id ASC, then nomor_identitas ASC
            $pegawaiQuery->orderBy('jabatan_id', 'asc')
                ->orderBy('nomor_identitas', 'asc');
        }

        $pegawaiList = $pegawaiQuery->paginate($request->get('per_page', 15));

        // Hitung total_tugas dari semua jenis tugas
        $pegawaiList->getCollection()->transform(function ($pegawai) {
            $pegawai->total_tugas = ($pegawai->tugas_pokok_count ?? 0) +
                ($pegawai->tugas_harian_count ?? 0) +
                ($pegawai->tugas_tambahan_count ?? 0);
            return $pegawai;
        });

        // Get filter options
        $tahuns = TugasPokok::selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->filter();

        $jabatans = MasterJabatan::where('is_active', true)
            ->orderBy('level')
            ->get();

        $bidangs = MasterBidang::where('is_active', true)
            ->orderBy('nama')
            ->get();

        // Statistics
        $stats = [
            'total_pegawai' => MasterPegawai::where('status_aktif', 'Aktif')->count(),
            'pegawai_dengan_tugas' => MasterPegawai::where('status_aktif', 'Aktif')
                ->whereHas('tugasPokok', function ($q) use ($tahun) {
                    $q->whereYear('tanggal_mulai', $tahun);
                })->count(),
            'total_tugas' => TugasPokok::whereYear('tanggal_mulai', $tahun)->count(),
            'pending' => TugasPokok::whereYear('tanggal_mulai', $tahun)
                ->where('status', 'pending')->count(),
            'dikerjakan' => TugasPokok::whereYear('tanggal_mulai', $tahun)
                ->where('status', 'dikerjakan')->count(),
            'selesai' => TugasPokok::whereYear('tanggal_mulai', $tahun)
                ->where('status', 'selesai')->count(),
        ];

        return view('penugasan::penugasan.daftar', compact(
            'pegawaiList',
            'tahuns',
            'tahun',
            'jabatans',
            'bidangs',
            'stats'
        ));
    }

    /**
     * Show the specified resource.
     */
    public function show(Request $request, $id)
    {
        $pegawai = MasterPegawai::with(['jabatan', 'bidang'])->findOrFail($id);

        $tahun = $request->get('tahun', date('Y'));

        // Query tugas pokok untuk pegawai ini
        $query = TugasPokok::where('pegawai_id', $id)
            ->with([
                'perjanjianKinerja',
                'indikatorPK',
                'attachedFiles',
                'progress'
            ])
            ->whereYear('tanggal_mulai', $tahun);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_tugas', 'like', "%{$request->search}%")
                    ->orWhere('deskripsi', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'tanggal_mulai');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tugasPokok = $query->paginate($request->get('per_page', 10));

        // Get tahun options untuk pegawai ini
        $tahuns = TugasPokok::where('pegawai_id', $id)
            ->selectRaw('EXTRACT(YEAR FROM tanggal_mulai) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->filter();

        // Query tugas harian untuk pegawai ini
        $tugasHarianQuery = \Modules\Penugasan\Models\TugasHarian::where('pegawai_id', $id)
            ->with([
                'tugasPokok',
                'pemberiTugas',
                'attachedFiles',
                'progress'
            ])
            ->whereYear('tanggal_mulai', $tahun);

        // Sort tugas harian
        $tugasHarianQuery->orderBy('tanggal_mulai', 'desc');

        $tugasHarian = $tugasHarianQuery->paginate($request->get('per_page_harian', 10), ['*'], 'page_harian');

        // Get tugas tambahan list for this pegawai
        $tugasTambahanList = \Modules\Penugasan\Models\TugasTambahan::with([
            'pemberiTugas',
            'validator',
            'attachedFiles'
        ])->where('pegawai_id', $pegawai->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Statistics untuk pegawai ini
        // Total Tugas Pokok
        $totalTugasPokok = TugasPokok::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->count();

        // Total Tugas Harian
        $totalTugasHarian = \Modules\Penugasan\Models\TugasHarian::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->count();

        // Tugas Harian Selesai
        $tugasSelesai = \Modules\Penugasan\Models\TugasHarian::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->where('status', 'selesai')
            ->count();

        // Tugas Harian Berjalan (dikerjakan + revisi)
        $tugasBerjalan = \Modules\Penugasan\Models\TugasHarian::where('pegawai_id', $id)
            ->whereYear('tanggal_mulai', $tahun)
            ->whereIn('status', ['dikerjakan', 'revisi'])
            ->count();

        // Tugas Tambahan Stats
        $totalTugasTambahan = $tugasTambahanList->count();
        $tugasTambahanSelesai = $tugasTambahanList->where('status', 'selesai')->count();
        $tugasTambahanProgress = $tugasTambahanList->whereIn('status', ['dikerjakan', 'revisi', 'validasi'])->count();

        return view('penugasan::penugasan.detail', compact(
            'pegawai',
            'tugasPokok',
            'tugasHarian',
            'tahuns',
            'tahun',
            'tugasTambahanList',
            'totalTugasPokok',
            'totalTugasHarian',
            'tugasSelesai',
            'tugasBerjalan',
            'totalTugasTambahan',
            'tugasTambahanSelesai',
            'tugasTambahanProgress'
        ));
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
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
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
                    'tanggal_selesai' => $validated['tanggal_selesai'],
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
                    'tanggal_selesai' => $validated['tanggal_selesai'],
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
            'tugas_id' => 'required|string|uuid',
            'jenis_tugas' => 'required|in:tugas_harian,tugas_tambahan',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls|max:10240',
            'folder_ids' => 'nullable|array',
            'folder_ids.*' => 'required|uuid|exists:td_folders,id',
            'replacements' => 'nullable|array',
            'replacements.*.old_file_id' => 'required|uuid|exists:td_files,id',
            'replacements.*.file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xlsx,xls|max:10240',
            'replacements.*.folder_id' => 'required|uuid|exists:td_folders,id',
            'keterangan' => 'nullable|string|max:1000',
        ]);

        // At least one file or replacement is required
        if (empty($validated['files']) && empty($validated['replacements'])) {
            return response()->json([
                'success' => false,
                'message' => 'Minimal harus ada file baru atau file pengganti',
            ], 422);
        }

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
            $replacedFiles = [];

            // Process file replacements first
            if (!empty($validated['replacements'])) {
                foreach ($validated['replacements'] as $replacement) {
                    $oldFile = \Modules\TerminalData\Models\TdFile::findOrFail($replacement['old_file_id']);

                    // Verify file belongs to this tugas
                    if ($oldFile->attachable_id !== $tugas->id || $oldFile->attachable_type !== $modelClass) {
                        throw new \Exception('File tidak terkait dengan tugas ini');
                    }

                    $file = $replacement['file'];
                    $folderId = $replacement['folder_id'];

                    // Upload new version file
                    $fileName = time() . '_v' . ($oldFile->version + 1) . '_' . $file->getClientOriginalName();
                    $filePath = $file->store('terminal-data', 'public');

                    // Mark old file as not latest
                    $oldFile->update(['is_latest_version' => false]);

                    // Create new version
                    $newVersion = \Modules\TerminalData\Models\TdFile::create([
                        'folder_id' => $folderId,
                        'name' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'original_name' => $file->getClientOriginalName(),
                        'description' => 'Revisi: ' . ($validated['keterangan'] ?? 'File diperbarui'),
                        'storage_path' => $filePath,
                        'extension' => $file->getClientOriginalExtension(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'hash' => hash_file('sha256', $file->getRealPath()),
                        'version' => $oldFile->version + 1,
                        'original_file_id' => $oldFile->original_file_id ?? $oldFile->id,
                        'is_latest_version' => true,
                        'version_notes' => 'Revisi dari versi ' . $oldFile->version . ': ' . ($validated['keterangan'] ?? ''),
                        'created_by' => Auth::id(),
                        // Polymorphic relation - same as parent
                        'attachable_type' => $oldFile->attachable_type,
                        'attachable_id' => $oldFile->attachable_id,
                    ]);

                    $replacedFiles[] = [
                        'old' => $oldFile->original_name,
                        'new' => $newVersion->original_name,
                        'version' => $newVersion->version,
                    ];
                    $uploadedFiles[] = $newVersion;
                }
            }

            // Process new files
            if (!empty($request->file('files'))) {
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
                        'version' => 1,
                        'is_latest_version' => true,
                        'version_notes' => $isRevision ? 'File tambahan saat revisi: ' . $validated['keterangan'] : null,
                        'created_by' => Auth::id(),
                        // Polymorphic relation
                        'attachable_type' => $modelClass,
                        'attachable_id' => $tugas->id,
                    ]);

                    $uploadedFiles[] = $tdFile;
                }
            }

            // Update status tugas
            $tugas->update(['status' => 'validasi']);

            // Build description message
            $progressDesc = [];
            if (count($replacedFiles) > 0) {
                $progressDesc[] = count($replacedFiles) . " file diperbarui";
            }
            if (count($uploadedFiles) - count($replacedFiles) > 0) {
                $progressDesc[] = (count($uploadedFiles) - count($replacedFiles)) . " file baru";
            }
            $descMessage = "Upload bukti: " . implode(", ", $progressDesc);
            if ($validated['keterangan']) {
                $descMessage .= " - " . $validated['keterangan'];
            }

            // Buat record progress dengan polymorphic relation
            \Modules\Penugasan\Models\Progress::create([
                'tipe_progress' => $modelClass,
                'tipe_progress_id' => $tugas->id,
                'pegawai_id' => $tugas->pegawai_id,
                'tanggal' => now(),
                'progress_persen' => 100.00,
                'deskripsi_kegiatan' => $descMessage,
            ]);

            // Simpan history revisi jika ini revisi
            if ($isRevision) {
                $this->saveRevisionHistory($tugas, $validated, $modelClass);
            }

            DB::commit();

            $message = 'Bukti berhasil diupload. Status tugas diubah menjadi menunggu validasi.';
            if (count($replacedFiles) > 0) {
                $message .= ' ' . count($replacedFiles) . ' file telah diperbarui dengan versi baru.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'files' => $uploadedFiles,
                'replaced_files' => $replacedFiles,
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
            'penilaian_kualitas' => 'required_if:status_validasi,diterima|nullable|numeric|min:0|max:100',
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
                // ========================================
                // HITUNG PENILAIAN DENGAN HELPER
                // ========================================
                $nilaiKualitas = $validated['penilaian_kualitas'];
                $tanggalSelesai = now(); // Tanggal validasi = tanggal selesai

                // Gunakan PenilaianHelper untuk hitung nilai
                $hasilPenilaian = PenilaianHelper::hitungDanSimpanPenilaian(
                    $tugas,
                    $nilaiKualitas,
                    $tanggalSelesai
                );

                // VALIDASI DITERIMA - Status jadi selesai
                $tugas->update([
                    'status' => 'selesai',
                    'validator_id' => Auth::id(),
                    'validated_at' => $tanggalSelesai,
                    'hasil_validasi' => 'diterima',
                    'catatan_validasi' => $validated['catatan_validasi'] ?? 'Tugas diterima dan selesai',
                    // penilaian_kualitas dan nilai_akhir sudah di-update oleh helper
                ]);

                // Update progress tugas pokok jika tugas harian
                if ($validated['jenis_tugas'] === 'tugas_harian') {
                    $this->updateProgressTugasPokok($tugas, $validated);
                }

                $message = sprintf(
                    'Tugas berhasil divalidasi! Nilai Waktu: %.2f, Nilai Kualitas: %.2f, Nilai Akhir: %.2f (%s)',
                    $hasilPenilaian['waktu']['nilai'],
                    $nilaiKualitas,
                    $hasilPenilaian['nilai_akhir'],
                    $hasilPenilaian['grade']['kategori']
                );
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
                'status' => $tugas->status,
                'penilaian' => $validated['status_validasi'] === 'diterima' ? [
                    'nilai_waktu' => $hasilPenilaian['waktu']['nilai'],
                    'nilai_kualitas' => $nilaiKualitas,
                    'nilai_akhir' => $hasilPenilaian['nilai_akhir'],
                    'grade' => $hasilPenilaian['grade'],
                    'keterlambatan' => $hasilPenilaian['waktu']['status'],
                ] : null
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
     * Preview penilaian sebelum validasi (untuk modal)
     */
    public function previewPenilaian(Request $request)
    {
        $request->validate([
            'tugas_id' => 'required|uuid',
            'jenis_tugas' => 'required|in:tugas_harian,tugas_tambahan',
            'nilai_kualitas' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $modelClass = $request->jenis_tugas === 'tugas_harian'
                ? TugasHarian::class
                : TugasTambahan::class;

            $tugas = $modelClass::findOrFail($request->tugas_id);

            // Preview penilaian menggunakan helper
            $preview = PenilaianHelper::previewPenilaian(
                \Carbon\Carbon::parse($tugas->tanggal_selesai),
                $request->nilai_kualitas,
                now()
            );

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'tanggal_deadline' => $tugas->tanggal_selesai->format('d/m/Y'),
                'tanggal_selesai' => now()->format('d/m/Y H:i'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal preview penilaian: ' . $e->getMessage()
            ], 500);
        }
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
