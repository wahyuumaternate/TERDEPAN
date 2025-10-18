<?php

namespace Modules\PerjanjianKinerja\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\PerjanjianKinerja\Models\PkIndikator;
use Modules\PerjanjianKinerja\Models\PkKegiatan;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;
use Modules\PerjanjianKinerja\Models\PkDokumen;
use Modules\PerjanjianKinerja\Models\PkProgram;
use Modules\PerjanjianKinerja\Models\PkSasaran;
use Modules\PerjanjianKinerja\Models\PkSubKegiatan;
use Modules\PerjanjianKinerja\Models\PkTemplate;
use App\Models\MasterPegawai;
use App\Models\MasterJabatan;
use App\Models\MasterBidang;
use Barryvdh\DomPDF\Facade\Pdf;

class PerjanjianKinerjaController extends Controller
{
    /**
     * Display a listing of perjanjian kinerja
     */
    public function index(Request $request)
    {
        $query = PkPerjanjianKinerja::with([
            'pegawai.jabatan',
            'pegawai.bidang',
            'atasan',
            'template',
            'sasaran',
            'dokumen' => function ($q) {
                $q->where('is_latest', true);
            }
        ]);

        // Default filter tahun sekarang
        $tahun = $request->get('tahun', date('Y'));
        $query->where('tahun', $tahun);

        // Filter by pegawai
        if ($request->filled('pegawai_id')) {
            $query->where('pegawai_id', $request->pegawai_id);
        }

        // Filter by jabatan
        if ($request->filled('jabatan_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('jabatan_id', $request->jabatan_id);
            });
        }

        // Filter by bidang
        if ($request->filled('bidang_id')) {
            $query->whereHas('pegawai', function ($q) use ($request) {
                $q->where('bidang_id', $request->bidang_id);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status_dokumen', $request->status);
        }

        // Filter by template
        if ($request->filled('template_id')) {
            $query->where('template_id', $request->template_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nomor_perjanjian', 'like', "%{$request->search}%")
                    ->orWhereHas('pegawai', function ($subQ) use ($request) {
                        $subQ->where('nama', 'like', "%{$request->search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perjanjians = $query->paginate($request->get('per_page', 10));

        // Get filter options
        $tahuns = PkPerjanjianKinerja::distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        $jabatans = MasterJabatan::where('is_active', true)
            ->where('is_struktural', true)
            ->orderBy('level')
            ->get();

        $bidangs = MasterBidang::where('is_active', true)
            ->orderBy('nama')
            ->get();

        $templates = PkTemplate::where('tahun', $tahun)
            ->where('is_active', true)
            ->orderBy('nama_template')
            ->get();

        $pegawais = MasterPegawai::where('status_aktif', 'Aktif')
            ->orderBy('nama')
            ->get();

        // Statistics
        $stats = [
            'total' => PkPerjanjianKinerja::where('tahun', $tahun)->count(),
            'draft' => PkPerjanjianKinerja::where('tahun', $tahun)->where('status_dokumen', 'Draft')->count(),
            'aktif' => PkPerjanjianKinerja::where('tahun', $tahun)->where('status_dokumen', 'Aktif')->count(),
            'selesai' => PkPerjanjianKinerja::where('tahun', $tahun)->where('status_dokumen', 'Selesai')->count(),
            'total_anggaran' => PkPerjanjianKinerja::where('tahun', $tahun)->sum('total_anggaran'),
        ];

        return view('perjanjiankinerja::index', compact(
            'perjanjians',
            'tahuns',
            'tahun',
            'jabatans',
            'bidangs',
            'templates',
            'pegawais',
            'stats'
        ));
    }

    /**
     * Download PDF dokumen
     */
    /**
     * Download PDF dokumen dengan penanganan khusus untuk mencegah refresh halaman
     */
    public function download($id)
    {
        try {
            $pk = PkPerjanjianKinerja::with(['pegawai', 'dokumen' => function ($q) {
                $q->where('is_latest', true);
            }])->findOrFail($id);

            $dokumen = $pk->dokumen->first();

            // Cek apakah dokumen ada
            if (!$dokumen) {
                Log::warning('Dokumen belum di-generate (ID PK: ' . $id . ')');
                return redirect()
                    ->route('perjanjian-kinerja.index')
                    ->with('error', 'Dokumen belum di-generate. Silakan generate dokumen terlebih dahulu.');
            }

            // Dapatkan file path yang disimpan di database
            $dbFilePath = $dokumen->file_path;
            Log::info('DB file path: ' . $dbFilePath);

            // Coba dengan direct path dulu (normalisasi path di database)
            $normalizedPath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $dbFilePath);

            // Build semua kemungkinan path untuk dicoba
            $possiblePaths = [
                $dbFilePath,                                                            // Original path
                $normalizedPath,                                                        // Normalized path
                storage_path('app/public/' . $dbFilePath),                              // Absolute path with forward slash
                storage_path('app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $normalizedPath), // Absolute with normalized
                public_path('storage/' . $dbFilePath),                                  // Public storage path 
                public_path('storage' . DIRECTORY_SEPARATOR . $normalizedPath),         // Public with normalized
                str_replace('public/', '', $dbFilePath),                                // Without public/ prefix
                str_replace('public\\', '', $normalizedPath)                            // Without public\ prefix
            ];

            // Log semua possible paths untuk debugging
            Log::info('Trying possible paths:', $possiblePaths);

            // Cari file yang valid dari semua kemungkinan path
            $validPath = null;
            foreach ($possiblePaths as $path) {
                if (file_exists($path) && is_file($path) && filesize($path) > 0) {
                    $validPath = $path;
                    Log::info('Found valid file at: ' . $validPath . ' (Size: ' . filesize($validPath) . ' bytes)');
                    break;
                }
            }

            // Jika tidak ada path valid
            if (!$validPath) {
                // Cek apakah file ada di storage (gunakan Storage facade sebagai fallback)
                if (Storage::disk('public')->exists($dbFilePath)) {
                    $validPath = Storage::disk('public')->path($dbFilePath);
                    Log::info('Found via Storage facade: ' . $validPath);
                } else {
                    Log::error('File not found in any location. Tried paths: ' . implode(', ', $possiblePaths));
                    return redirect()
                        ->route('perjanjian-kinerja.show', $id)
                        ->with('error', 'File PDF tidak ditemukan. Silakan generate ulang dokumen.');
                }
            }

            // Update download count
            $dokumen->increment('download_count');

            // PENGGUNAAN DIRECT PHP UNTUK FORCED DOWNLOAD
            // Ini adalah cara alternatif yang lebih robust untuk memaksa download

            // Bersihkan semua output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Set header yang lebih lengkap untuk memastikan file didownload
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $dokumen->file_name . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($validPath));

            // Kirim file dengan readfile
            flush();
            readfile($validPath);
            exit; // Penting: Exit setelah mengirim file

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('PK Not Found (ID: ' . $id . '): ' . $e->getMessage());
            return redirect()
                ->route('perjanjian-kinerja.index')
                ->with('error', 'Perjanjian Kinerja tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Error Download PDF (ID: ' . $id . '): ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return redirect()
                ->route('perjanjian-kinerja.index')
                ->with('error', 'Gagal mengunduh PDF: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified perjanjian kinerja
     */
    public function show($id)
    {
        $pk = PkPerjanjianKinerja::with([
            'pegawai.jabatan',
            'pegawai.bidang',
            'atasan.jabatan',
            'template',
            'sasaran.indikator.program.kegiatan.subKegiatan',
            'dokumen' => function ($q) {
                $q->orderBy('versi', 'desc');
            }
        ])->findOrFail($id);

        return view('perjanjiankinerja::show', compact('pk'));
    }


    /**
     * Show the form for creating a new perjanjian kinerja
     */
    public function create()
    {
        // Ambil pegawai yang aktif dan punya atasan
        $pegawai = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereNotNull('atasan_langsung_id')
            ->with(['jabatan', 'bidang', 'atasanLangsung'])
            ->orderBy('nama')
            ->get();

        // Ambil template yang aktif untuk tahun ini
        $templates = PkTemplate::where('is_active', true)
            ->where('tahun', date('Y'))
            ->with('jabatan')
            ->get();

        $currentYear = date('Y');

        return view('perjanjiankinerja::create', compact(
            'pegawai',
            'templates',
            'currentYear'
        ));
    }

    /**
     * Store a newly created perjanjian kinerja
     */
    public function store(Request $request)
    {
        // Validasi input dengan struktur lengkap
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'template_id' => 'required|exists:pk_template,id',
            'tahun' => 'required|integer|min:2020|max:2100',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
            'catatan' => 'nullable|string',

            // Validasi Sasaran
            'sasaran' => 'nullable|array',
            'sasaran.*.sasaran_strategis' => 'required_with:sasaran|string',
            'sasaran.*.urutan' => 'required_with:sasaran|integer|min:1',

            // Validasi Indikator
            'sasaran.*.indikator' => 'nullable|array',
            'sasaran.*.indikator.*.indikator_sasaran' => 'required_with:sasaran.*.indikator|string',
            'sasaran.*.indikator.*.target_value' => 'required_with:sasaran.*.indikator|numeric',
            'sasaran.*.indikator.*.satuan' => 'required_with:sasaran.*.indikator|string',

            // Validasi Program
            'sasaran.*.indikator.*.program' => 'nullable|array',
            'sasaran.*.indikator.*.program.*.kode_program' => 'required_with:sasaran.*.indikator.*.program|string|max:50',
            'sasaran.*.indikator.*.program.*.nama_program' => 'required_with:sasaran.*.indikator.*.program|string',
            'sasaran.*.indikator.*.program.*.anggaran' => 'required_with:sasaran.*.indikator.*.program|numeric|min:0',
            'sasaran.*.indikator.*.program.*.urutan' => 'required_with:sasaran.*.indikator.*.program|integer|min:1',

            // Validasi Kegiatan
            'sasaran.*.indikator.*.program.*.kegiatan' => 'nullable|array',
            'sasaran.*.indikator.*.program.*.kegiatan.*.kode_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|string|max:50',
            'sasaran.*.indikator.*.program.*.kegiatan.*.nama_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|string',
            'sasaran.*.indikator.*.program.*.kegiatan.*.anggaran' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|numeric|min:0',
            'sasaran.*.indikator.*.program.*.kegiatan.*.urutan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|integer|min:1',

            // Validasi Sub Kegiatan
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan' => 'nullable|array',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.kode_sub_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|string|max:50',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.nama_sub_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|string',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.anggaran' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|numeric|min:0',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.target_value' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|numeric',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.satuan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Ambil data pegawai untuk mendapatkan atasan
            $pegawai = MasterPegawai::findOrFail($validated['pegawai_id']);

            if (!$pegawai->atasan_langsung_id) {
                return back()
                    ->withInput()
                    ->with('error', 'Pegawai tidak memiliki atasan langsung');
            }

            // Generate nomor perjanjian
            $nomorPerjanjian = $this->generateNomorPerjanjian($validated['tahun']);

            // Buat perjanjian kinerja
            $pk = PkPerjanjianKinerja::create([
                'nomor_perjanjian' => $nomorPerjanjian,
                'pegawai_id' => $validated['pegawai_id'],
                'atasan_id' => $pegawai->atasan_langsung_id,
                'template_id' => $validated['template_id'],
                'tahun' => $validated['tahun'],
                'periode_mulai' => $validated['periode_mulai'],
                'periode_selesai' => $validated['periode_selesai'],
                'tempat_ttd' => 'Sofifi',
                'catatan' => $validated['catatan'] ?? null,
                'status_dokumen' => 'Draft',
                'is_locked' => false,
                'total_anggaran' => 0,
            ]);

            $totalAnggaran = 0;

            // Simpan sasaran dan struktur lengkapnya
            if (isset($validated['sasaran']) && is_array($validated['sasaran'])) {
                foreach ($validated['sasaran'] as $sasaranData) {
                    // 1. Simpan Sasaran
                    $sasaran = PkSasaran::create([
                        'perjanjian_kinerja_id' => $pk->id,
                        'sasaran_strategis' => $sasaranData['sasaran_strategis'],
                        'urutan' => $sasaranData['urutan'],
                    ]);

                    // 2. Simpan Indikator jika ada
                    if (isset($sasaranData['indikator']) && is_array($sasaranData['indikator'])) {
                        foreach ($sasaranData['indikator'] as $indikatorData) {
                            $indikator = PkIndikator::create([
                                'sasaran_id' => $sasaran->id,
                                'indikator_sasaran' => $indikatorData['indikator_sasaran'],
                                'target_value' => $indikatorData['target_value'],
                                'satuan' => $indikatorData['satuan'],
                            ]);

                            // 3. Simpan Program jika ada
                            if (isset($indikatorData['program']) && is_array($indikatorData['program'])) {
                                foreach ($indikatorData['program'] as $programData) {
                                    $program = PkProgram::create([
                                        'indikator_id' => $indikator->id,
                                        'kode_program' => $programData['kode_program'],
                                        'nama_program' => $programData['nama_program'],
                                        'anggaran' => $programData['anggaran'],
                                        'urutan' => $programData['urutan'],
                                    ]);

                                    $totalAnggaran += $programData['anggaran'];

                                    // 4. Simpan Kegiatan jika ada
                                    if (isset($programData['kegiatan']) && is_array($programData['kegiatan'])) {
                                        foreach ($programData['kegiatan'] as $kegiatanData) {
                                            $kegiatan = PkSubKegiatan::create([
                                                'program_id' => $program->id,
                                                'kode_kegiatan' => $kegiatanData['kode_kegiatan'],
                                                'nama_kegiatan' => $kegiatanData['nama_kegiatan'],
                                                'anggaran' => $kegiatanData['anggaran'],
                                                'urutan' => $kegiatanData['urutan'],
                                            ]);

                                            // 5. Simpan Sub Kegiatan jika ada
                                            if (isset($kegiatanData['subkegiatan']) && is_array($kegiatanData['subkegiatan'])) {
                                                foreach ($kegiatanData['subkegiatan'] as $subKegiatanData) {
                                                    PkSubKegiatan::create([
                                                        'kegiatan_id' => $kegiatan->id,
                                                        'kode_sub_kegiatan' => $subKegiatanData['kode_sub_kegiatan'],
                                                        'nama_sub_kegiatan' => $subKegiatanData['nama_sub_kegiatan'],
                                                        'anggaran' => $subKegiatanData['anggaran'],
                                                        'target_value' => $subKegiatanData['target_value'],
                                                        'satuan' => $subKegiatanData['satuan'],
                                                        'urutan' => $subKegiatanData['urutan'] ?? 1,
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Update total anggaran
            $pk->update(['total_anggaran' => $totalAnggaran]);

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.show', $pk->id)
                ->with('success', 'Perjanjian Kinerja berhasil dibuat dengan ' . count($validated['sasaran'] ?? []) . ' sasaran');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating perjanjian kinerja: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat Perjanjian Kinerja: ' . $e->getMessage());
        }
    }


    /**
     * Show the form for editing the specified perjanjian kinerja
     */
    public function edit($id)
    {
        $pk = PkPerjanjianKinerja::with([
            'pegawai.jabatan',
            'pegawai.bidang',
            'atasan.jabatan',
            'template',
            'sasaran.indikator'
        ])->findOrFail($id);

        // Cek apakah sudah dikunci
        if ($pk->is_locked) {
            return redirect()
                ->route('perjanjian-kinerja.show', $pk->id)
                ->with('warning', 'Dokumen sudah ditandatangani dan tidak dapat diedit');
        }

        // Ambil pegawai yang aktif dan punya atasan
        $pegawai = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereNotNull('atasan_langsung_id')
            ->with(['jabatan', 'bidang', 'atasanLangsung'])
            ->orderBy('nama')
            ->get();

        // Ambil semua atasan (untuk keperluan jika ingin mengubah atasan manual)
        $atasan = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereHas('jabatan', function ($q) {
                $q->where('is_struktural', true);
            })
            ->with(['jabatan'])
            ->orderBy('nama')
            ->get();

        // Ambil template yang aktif
        $templates = PkTemplate::where('is_active', true)
            ->with('jabatan')
            ->get();

        return view('perjanjiankinerja::edit', compact(
            'pk',
            'pegawai',
            'atasan',
            'templates'
        ));
    }

    /**
     * Update the specified perjanjian kinerja
     */
    // public function update(Request $request, $id)
    // {
    //     $pk = PkPerjanjianKinerja::findOrFail($id);

    //     // Cek apakah sudah dikunci
    //     if ($pk->is_locked) {
    //         return redirect()
    //             ->route('perjanjian-kinerja.show', $pk->id)
    //             ->with('error', 'Dokumen sudah ditandatangani dan tidak dapat diedit');
    //     }

    //     // Validasi input
    //     $validated = $request->validate([
    //         'pegawai_id' => 'required|exists:master_pegawai,id',
    //         'atasan_id' => 'required|exists:master_pegawai,id',
    //         'template_id' => 'required|exists:pk_template,id',
    //         'tahun' => 'required|integer|min:2020|max:2100',
    //         'periode_mulai' => 'required|date',
    //         'periode_selesai' => 'required|date|after:periode_mulai',
    //         'catatan' => 'nullable|string',
    //         'sasaran' => 'nullable|array',
    //         'sasaran.*.id' => 'nullable|exists:pk_sasaran,id',
    //         'sasaran.*.sasaran_strategis' => 'required_with:sasaran.*|string',
    //         'sasaran.*.urutan' => 'required_with:sasaran.*|integer|min:1',
    //         'sasaran.*.indikator' => 'nullable|array',
    //         'sasaran.*.indikator.*.id' => 'nullable|exists:pk_indikator,id',
    //         'sasaran.*.indikator.*.indikator_sasaran' => 'required_with:sasaran.*.indikator.*|string',
    //         'sasaran.*.indikator.*.target_value' => 'required_with:sasaran.*.indikator.*|numeric',
    //         'sasaran.*.indikator.*.satuan' => 'required_with:sasaran.*.indikator.*|string',
    //     ]);

    //     DB::beginTransaction();
    //     try {
    //         // Update perjanjian kinerja
    //         $pk->update([
    //             'pegawai_id' => $validated['pegawai_id'],
    //             'atasan_id' => $validated['atasan_id'],
    //             'template_id' => $validated['template_id'],
    //             'tahun' => $validated['tahun'],
    //             'periode_mulai' => $validated['periode_mulai'],
    //             'periode_selesai' => $validated['periode_selesai'],
    //             'catatan' => $validated['catatan'] ?? null,
    //         ]);

    //         // Tracking ID sasaran yang sudah ada
    //         $existingSasaranIds = [];

    //         // Update atau create sasaran
    //         if (isset($validated['sasaran']) && is_array($validated['sasaran'])) {
    //             foreach ($validated['sasaran'] as $sasaranData) {
    //                 // Reset tracking indikator untuk setiap sasaran - INI PENTING!
    //                 $existingIndikatorIds = [];

    //                 if (isset($sasaranData['id']) && !empty($sasaranData['id'])) {
    //                     // Update existing sasaran
    //                     $sasaran = PkSasaran::where('id', $sasaranData['id'])
    //                         ->where('perjanjian_kinerja_id', $pk->id)
    //                         ->first();

    //                     if ($sasaran) {
    //                         $sasaran->update([
    //                             'sasaran_strategis' => $sasaranData['sasaran_strategis'],
    //                             'urutan' => $sasaranData['urutan'],
    //                         ]);
    //                         $existingSasaranIds[] = $sasaran->id;
    //                     }
    //                 } else {
    //                     // Create new sasaran
    //                     $sasaran = PkSasaran::create([
    //                         'perjanjian_kinerja_id' => $pk->id,
    //                         'sasaran_strategis' => $sasaranData['sasaran_strategis'],
    //                         'urutan' => $sasaranData['urutan'],
    //                     ]);
    //                     $existingSasaranIds[] = $sasaran->id;
    //                 }

    //                 // Update atau create indikator
    //                 if (isset($sasaranData['indikator']) && is_array($sasaranData['indikator'])) {
    //                     foreach ($sasaranData['indikator'] as $indikatorData) {
    //                         if (isset($indikatorData['id']) && !empty($indikatorData['id'])) {
    //                             // Update existing indikator
    //                             $indikator = PkIndikator::where('id', $indikatorData['id'])
    //                                 ->where('sasaran_id', $sasaran->id)
    //                                 ->first();

    //                             if ($indikator) {
    //                                 $indikator->update([
    //                                     'indikator_sasaran' => $indikatorData['indikator_sasaran'],
    //                                     'target_value' => $indikatorData['target_value'],
    //                                     'satuan' => $indikatorData['satuan'],
    //                                 ]);
    //                                 $existingIndikatorIds[] = $indikator->id;
    //                             }
    //                         } else {
    //                             // Create new indikator
    //                             $indikator = PkIndikator::create([
    //                                 'sasaran_id' => $sasaran->id,
    //                                 'indikator_sasaran' => $indikatorData['indikator_sasaran'],
    //                                 'target_value' => $indikatorData['target_value'],
    //                                 'satuan' => $indikatorData['satuan'],
    //                             ]);
    //                             $existingIndikatorIds[] = $indikator->id;
    //                         }
    //                     }
    //                 }

    //                 // Hapus indikator yang tidak ada di request untuk sasaran ini
    //                 if (!empty($existingIndikatorIds)) {
    //                     PkIndikator::where('sasaran_id', $sasaran->id)
    //                         ->whereNotIn('id', $existingIndikatorIds)
    //                         ->delete();
    //                 } else {
    //                     // Jika tidak ada indikator di request, hapus semua indikator sasaran ini
    //                     PkIndikator::where('sasaran_id', $sasaran->id)->delete();
    //                 }
    //             }
    //         }

    //         // Hapus sasaran yang tidak ada di request
    //         if (!empty($existingSasaranIds)) {
    //             PkSasaran::where('perjanjian_kinerja_id', $pk->id)
    //                 ->whereNotIn('id', $existingSasaranIds)
    //                 ->delete();
    //         } else {
    //             // Jika tidak ada sasaran di request, hapus semua sasaran
    //             PkSasaran::where('perjanjian_kinerja_id', $pk->id)->delete();
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('perjanjian-kinerja.show', $pk->id)
    //             ->with('success', 'Perjanjian Kinerja berhasil diupdate');
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Error updating perjanjian kinerja: ' . $e->getMessage(), [
    //             'pk_id' => $id,
    //             'request_data' => $request->all(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return redirect()
    //             ->back()
    //             ->withInput()
    //             ->with('error', 'Gagal mengupdate Perjanjian Kinerja: ' . $e->getMessage());
    //     }
    // }
    /**
     * Update the specified perjanjian kinerja
     */
    public function update(Request $request, $id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        // Cek apakah sudah dikunci
        if ($pk->is_locked) {
            return redirect()
                ->route('perjanjian-kinerja.show', $pk->id)
                ->with('error', 'Dokumen sudah ditandatangani dan tidak dapat diedit');
        }

        // Validasi input lengkap
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'atasan_id' => 'required|exists:master_pegawai,id',
            'template_id' => 'required|exists:pk_template,id',
            'tahun' => 'required|integer|min:2020|max:2100',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
            'catatan' => 'nullable|string',

            // Validasi Sasaran
            'sasaran' => 'nullable|array',
            'sasaran.*.id' => 'nullable|exists:pk_sasaran,id',
            'sasaran.*.sasaran_strategis' => 'required_with:sasaran|string',
            'sasaran.*.urutan' => 'required_with:sasaran|integer|min:1',

            // Validasi Indikator
            'sasaran.*.indikator' => 'nullable|array',
            'sasaran.*.indikator.*.id' => 'nullable|exists:pk_indikator,id',
            'sasaran.*.indikator.*.indikator_sasaran' => 'required_with:sasaran.*.indikator|string',
            'sasaran.*.indikator.*.target_value' => 'required_with:sasaran.*.indikator|numeric',
            'sasaran.*.indikator.*.satuan' => 'required_with:sasaran.*.indikator|string',

            // Validasi Program
            'sasaran.*.indikator.*.program' => 'nullable|array',
            'sasaran.*.indikator.*.program.*.id' => 'nullable|exists:pk_program,id',
            'sasaran.*.indikator.*.program.*.kode_program' => 'required_with:sasaran.*.indikator.*.program|string|max:50',
            'sasaran.*.indikator.*.program.*.nama_program' => 'required_with:sasaran.*.indikator.*.program|string',
            'sasaran.*.indikator.*.program.*.anggaran' => 'required_with:sasaran.*.indikator.*.program|numeric|min:0',
            'sasaran.*.indikator.*.program.*.urutan' => 'required_with:sasaran.*.indikator.*.program|integer|min:1',

            // Validasi Kegiatan
            'sasaran.*.indikator.*.program.*.kegiatan' => 'nullable|array',
            'sasaran.*.indikator.*.program.*.kegiatan.*.id' => 'nullable|exists:pk_kegiatan,id',
            'sasaran.*.indikator.*.program.*.kegiatan.*.kode_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|string|max:50',
            'sasaran.*.indikator.*.program.*.kegiatan.*.nama_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|string',
            'sasaran.*.indikator.*.program.*.kegiatan.*.anggaran' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|numeric|min:0',
            'sasaran.*.indikator.*.program.*.kegiatan.*.urutan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan|integer|min:1',

            // Validasi Sub Kegiatan
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan' => 'nullable|array',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.id' => 'nullable|exists:pk_sub_kegiatan,id',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.kode_sub_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|string|max:50',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.nama_sub_kegiatan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|string',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.anggaran' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|numeric|min:0',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.target_value' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|numeric',
            'sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan.*.satuan' => 'required_with:sasaran.*.indikator.*.program.*.kegiatan.*.subkegiatan|string|max:50',
        ]);

        DB::beginTransaction();
        try {
            // Update perjanjian kinerja
            $pk->update([
                'pegawai_id' => $validated['pegawai_id'],
                'atasan_id' => $validated['atasan_id'],
                'template_id' => $validated['template_id'],
                'tahun' => $validated['tahun'],
                'periode_mulai' => $validated['periode_mulai'],
                'periode_selesai' => $validated['periode_selesai'],
                'catatan' => $validated['catatan'] ?? null,
            ]);

            // Tracking ID yang sudah ada
            $existingSasaranIds = [];
            $totalAnggaran = 0;

            // Update atau create sasaran
            if (isset($validated['sasaran']) && is_array($validated['sasaran'])) {
                foreach ($validated['sasaran'] as $sasaranData) {
                    $existingIndikatorIds = [];

                    // 1. Simpan/Update Sasaran
                    if (isset($sasaranData['id']) && !empty($sasaranData['id'])) {
                        $sasaran = PkSasaran::where('id', $sasaranData['id'])
                            ->where('perjanjian_kinerja_id', $pk->id)
                            ->first();

                        if ($sasaran) {
                            $sasaran->update([
                                'sasaran_strategis' => $sasaranData['sasaran_strategis'],
                                'urutan' => $sasaranData['urutan'],
                            ]);
                            $existingSasaranIds[] = $sasaran->id;
                        }
                    } else {
                        $sasaran = PkSasaran::create([
                            'perjanjian_kinerja_id' => $pk->id,
                            'sasaran_strategis' => $sasaranData['sasaran_strategis'],
                            'urutan' => $sasaranData['urutan'],
                        ]);
                        $existingSasaranIds[] = $sasaran->id;
                    }

                    // 2. Simpan/Update Indikator
                    if (isset($sasaranData['indikator']) && is_array($sasaranData['indikator'])) {
                        foreach ($sasaranData['indikator'] as $indikatorData) {
                            $existingProgramIds = [];

                            if (isset($indikatorData['id']) && !empty($indikatorData['id'])) {
                                $indikator = PkIndikator::where('id', $indikatorData['id'])
                                    ->where('sasaran_id', $sasaran->id)
                                    ->first();

                                if ($indikator) {
                                    $indikator->update([
                                        'indikator_sasaran' => $indikatorData['indikator_sasaran'],
                                        'target_value' => $indikatorData['target_value'],
                                        'satuan' => $indikatorData['satuan'],
                                    ]);
                                    $existingIndikatorIds[] = $indikator->id;
                                }
                            } else {
                                $indikator = PkIndikator::create([
                                    'sasaran_id' => $sasaran->id,
                                    'indikator_sasaran' => $indikatorData['indikator_sasaran'],
                                    'target_value' => $indikatorData['target_value'],
                                    'satuan' => $indikatorData['satuan'],
                                ]);
                                $existingIndikatorIds[] = $indikator->id;
                            }

                            // 3. Simpan/Update Program
                            if (isset($indikatorData['program']) && is_array($indikatorData['program'])) {
                                foreach ($indikatorData['program'] as $programData) {
                                    $existingKegiatanIds = [];

                                    if (isset($programData['id']) && !empty($programData['id'])) {
                                        $program = PkProgram::where('id', $programData['id'])
                                            ->where('indikator_id', $indikator->id)
                                            ->first();

                                        if ($program) {
                                            $program->update([
                                                'kode_program' => $programData['kode_program'],
                                                'nama_program' => $programData['nama_program'],
                                                'anggaran' => $programData['anggaran'],
                                                'urutan' => $programData['urutan'],
                                            ]);
                                            $existingProgramIds[] = $program->id;
                                        }
                                    } else {
                                        $program = PkProgram::create([
                                            'indikator_id' => $indikator->id,
                                            'kode_program' => $programData['kode_program'],
                                            'nama_program' => $programData['nama_program'],
                                            'anggaran' => $programData['anggaran'],
                                            'urutan' => $programData['urutan'],
                                        ]);
                                        $existingProgramIds[] = $program->id;
                                    }

                                    $totalAnggaran += $programData['anggaran'];

                                    // 4. Simpan/Update Kegiatan
                                    if (isset($programData['kegiatan']) && is_array($programData['kegiatan'])) {
                                        foreach ($programData['kegiatan'] as $kegiatanData) {
                                            $existingSubKegiatanIds = [];

                                            if (isset($kegiatanData['id']) && !empty($kegiatanData['id'])) {
                                                $kegiatan = PkKegiatan::where('id', $kegiatanData['id'])
                                                    ->where('program_id', $program->id)
                                                    ->first();

                                                if ($kegiatan) {
                                                    $kegiatan->update([
                                                        'kode_kegiatan' => $kegiatanData['kode_kegiatan'],
                                                        'nama_kegiatan' => $kegiatanData['nama_kegiatan'],
                                                        'anggaran' => $kegiatanData['anggaran'],
                                                        'urutan' => $kegiatanData['urutan'],
                                                    ]);
                                                    $existingKegiatanIds[] = $kegiatan->id;
                                                }
                                            } else {
                                                $kegiatan = PkKegiatan::create([
                                                    'program_id' => $program->id,
                                                    'kode_kegiatan' => $kegiatanData['kode_kegiatan'],
                                                    'nama_kegiatan' => $kegiatanData['nama_kegiatan'],
                                                    'anggaran' => $kegiatanData['anggaran'],
                                                    'urutan' => $kegiatanData['urutan'],
                                                ]);
                                                $existingKegiatanIds[] = $kegiatan->id;
                                            }

                                            // 5. Simpan/Update Sub Kegiatan
                                            if (isset($kegiatanData['subkegiatan']) && is_array($kegiatanData['subkegiatan'])) {
                                                foreach ($kegiatanData['subkegiatan'] as $subKegiatanData) {
                                                    if (isset($subKegiatanData['id']) && !empty($subKegiatanData['id'])) {
                                                        $subKegiatan = PkSubKegiatan::where('id', $subKegiatanData['id'])
                                                            ->where('kegiatan_id', $kegiatan->id)
                                                            ->first();

                                                        if ($subKegiatan) {
                                                            $subKegiatan->update([
                                                                'kode_sub_kegiatan' => $subKegiatanData['kode_sub_kegiatan'],
                                                                'nama_sub_kegiatan' => $subKegiatanData['nama_sub_kegiatan'],
                                                                'anggaran' => $subKegiatanData['anggaran'],
                                                                'target_value' => $subKegiatanData['target_value'],
                                                                'satuan' => $subKegiatanData['satuan'],
                                                                'urutan' => $subKegiatanData['urutan'] ?? 1,
                                                            ]);
                                                            $existingSubKegiatanIds[] = $subKegiatan->id;
                                                        }
                                                    } else {
                                                        $subKegiatan = PkSubKegiatan::create([
                                                            'kegiatan_id' => $kegiatan->id,
                                                            'kode_sub_kegiatan' => $subKegiatanData['kode_sub_kegiatan'],
                                                            'nama_sub_kegiatan' => $subKegiatanData['nama_sub_kegiatan'],
                                                            'anggaran' => $subKegiatanData['anggaran'],
                                                            'target_value' => $subKegiatanData['target_value'],
                                                            'satuan' => $subKegiatanData['satuan'],
                                                            'urutan' => $subKegiatanData['urutan'] ?? 1,
                                                        ]);
                                                        $existingSubKegiatanIds[] = $subKegiatan->id;
                                                    }
                                                }
                                            }

                                            // Hapus sub kegiatan yang tidak ada di request
                                            if (!empty($existingSubKegiatanIds)) {
                                                PkSubKegiatan::where('kegiatan_id', $kegiatan->id)
                                                    ->whereNotIn('id', $existingSubKegiatanIds)
                                                    ->delete();
                                            } else {
                                                PkSubKegiatan::where('kegiatan_id', $kegiatan->id)->delete();
                                            }
                                        }
                                    }

                                    // Hapus kegiatan yang tidak ada di request
                                    if (!empty($existingKegiatanIds)) {
                                        PkKegiatan::where('program_id', $program->id)
                                            ->whereNotIn('id', $existingKegiatanIds)
                                            ->delete();
                                    } else {
                                        PkKegiatan::where('program_id', $program->id)->delete();
                                    }
                                }
                            }

                            // Hapus program yang tidak ada di request
                            if (!empty($existingProgramIds)) {
                                PkProgram::where('indikator_id', $indikator->id)
                                    ->whereNotIn('id', $existingProgramIds)
                                    ->delete();
                            } else {
                                PkProgram::where('indikator_id', $indikator->id)->delete();
                            }
                        }
                    }

                    // Hapus indikator yang tidak ada di request
                    if (!empty($existingIndikatorIds)) {
                        PkIndikator::where('sasaran_id', $sasaran->id)
                            ->whereNotIn('id', $existingIndikatorIds)
                            ->delete();
                    } else {
                        PkIndikator::where('sasaran_id', $sasaran->id)->delete();
                    }
                }
            }

            // Hapus sasaran yang tidak ada di request
            if (!empty($existingSasaranIds)) {
                PkSasaran::where('perjanjian_kinerja_id', $pk->id)
                    ->whereNotIn('id', $existingSasaranIds)
                    ->delete();
            } else {
                PkSasaran::where('perjanjian_kinerja_id', $pk->id)->delete();
            }

            // Update total anggaran
            $pk->update(['total_anggaran' => $totalAnggaran]);

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.show', $pk->id)
                ->with('success', 'Perjanjian Kinerja berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating perjanjian kinerja: ' . $e->getMessage(), [
                'pk_id' => $id,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal mengupdate Perjanjian Kinerja: ' . $e->getMessage());
        }
    }
    /**
     * Generate nomor perjanjian unik
     */
    private function generateNomorPerjanjian($tahun)
    {
        // Format: PK/BAPPEDA/TAHUN/COUNTER
        $prefix = 'PK/BAPPEDA/' . $tahun . '/';

        // Cari counter terakhir untuk tahun ini
        $lastPK = PkPerjanjianKinerja::where('tahun', $tahun)
            ->where('nomor_perjanjian', 'like', $prefix . '%')
            ->orderBy('nomor_perjanjian', 'desc')
            ->first();

        if ($lastPK) {
            // Extract counter dari nomor terakhir
            $lastNumber = (int) substr($lastPK->nomor_perjanjian, -3);
            $counter = $lastNumber + 1;
        } else {
            $counter = 1;
        }

        // Format counter dengan 3 digit
        $formattedCounter = str_pad($counter, 3, '0', STR_PAD_LEFT);

        return $prefix . $formattedCounter;
    }

    /**
     * Get atasan by pegawai ID (untuk AJAX request)
     */
    public function getAtasan($pegawaiId)
    {
        try {
            $pegawai = MasterPegawai::with(['atasanLangsung.jabatan'])
                ->findOrFail($pegawaiId);

            if (!$pegawai->atasanLangsung) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai tidak memiliki atasan langsung'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $pegawai->atasanLangsung->id,
                    'nama' => $pegawai->atasanLangsung->nama,
                    'nip' => $pegawai->atasanLangsung->nomor_identitas,
                    'jabatan' => $pegawai->atasanLangsung->jabatan->nama ?? '-',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Remove the specified perjanjian kinerja
     */
    public function destroy($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        if ($pk->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian Kinerja tidak dapat dihapus karena sudah dikunci.'
            ], 422);
        }

        if ($pk->status_dokumen !== 'Draft') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya Perjanjian Kinerja berstatus Draft yang dapat dihapus.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Delete all dokumen files
            foreach ($pk->dokumen as $dokumen) {
                if (Storage::disk('public')->exists($dokumen->file_path)) {
                    Storage::disk('public')->delete($dokumen->file_path);
                }
            }

            // Delete related records (cascade will handle most, but let's be explicit)
            $pk->sasaran()->each(function ($sasaran) {
                $sasaran->indikator()->each(function ($indikator) {
                    $indikator->program()->each(function ($program) {
                        $program->kegiatan()->each(function ($kegiatan) {
                            $kegiatan->subKegiatan()->delete();
                        });
                        $program->kegiatan()->delete();
                    });
                    $indikator->program()->delete();
                });
                $sasaran->indikator()->delete();
            });
            $pk->sasaran()->delete();
            $pk->dokumen()->delete();

            // Delete perjanjian kinerja
            $pk->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perjanjian Kinerja berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting PK: ' . $e->getMessage(), [
                'pk_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Perjanjian Kinerja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate total anggaran
     */
    public function calculateAnggaran($id)
    {
        $pk = PkPerjanjianKinerja::with('sasaran.indikator.program.kegiatan.subKegiatan')
            ->findOrFail($id);

        try {
            $total = $pk->calculateTotalAnggaran();

            return response()->json([
                'success' => true,
                'message' => 'Total anggaran berhasil dihitung',
                'total_anggaran' => $total,
                'formatted' => 'Rp ' . number_format($total, 0, ',', '.')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung total anggaran: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lock perjanjian kinerja
     */
    public function lock($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        if ($pk->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian Kinerja sudah dalam keadaan terkunci.'
            ], 422);
        }

        try {
            $pk->update(['is_locked' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Perjanjian Kinerja berhasil dikunci.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunci Perjanjian Kinerja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlock perjanjian kinerja
     */
    public function unlock($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        if (!$pk->is_locked) {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian Kinerja sudah dalam keadaan tidak terkunci.'
            ], 422);
        }

        // Check if document is signed, don't allow unlocking if signed
        if ($pk->tanggal_ttd) {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian Kinerja yang sudah ditandatangani tidak dapat dibuka kuncinya.'
            ], 422);
        }

        try {
            $pk->update(['is_locked' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Perjanjian Kinerja berhasil dibuka kuncinya.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuka kunci Perjanjian Kinerja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF dokumen menggunakan template dari database
     */
    // public function generate($id)
    // {
    //     $pk = PkPerjanjianKinerja::with([
    //         'pegawai.jabatan',
    //         'pegawai.bidang',
    //         'atasan.jabatan',
    //         'template.sections',
    //         'sasaran' => function ($query) {
    //             $query->orderBy('urutan')
    //                 ->with([
    //                     'indikator' => function ($qi) {
    //                         $qi->with([
    //                             'program' => function ($qp) {
    //                                 $qp->orderBy('urutan')
    //                                     ->with([
    //                                         'kegiatan' => function ($qk) {
    //                                             $qk->orderBy('urutan')
    //                                                 ->with([
    //                                                     'subKegiatan' => function ($qs) {
    //                                                         $qs->orderBy('urutan');
    //                                                     }
    //                                                 ]);
    //                                         }
    //                                     ]);
    //                             }
    //                         ]);
    //                     }
    //                 ]);
    //         }
    //     ])->findOrFail($id);

    //     DB::beginTransaction();
    //     try {
    //         // Validate template exists
    //         if (!$pk->template) {
    //             throw new \Exception('Template tidak ditemukan untuk perjanjian kinerja ini.');
    //         }

    //         // Calculate version number
    //         $latestVersion = $pk->dokumen()->max('versi') ?? 0;
    //         $newVersion = $latestVersion + 1;

    //         // Reset previous latest
    //         $pk->dokumen()->where('is_latest', true)->update(['is_latest' => false]);

    //         // Create PDF menggunakan template
    //         $data = [
    //             'pk' => $pk,
    //             'title' => 'Perjanjian Kinerja - ' . $pk->nomor_perjanjian
    //         ];

    //         $pdf = PDF::loadView('perjanjiankinerja::pdf.perjanjian_kinerja', $data);

    //         // Set paper size dan orientation dari template
    //         $pageSize = $pk->template->page_size ?? 'A4';
    //         $orientation = strtolower($pk->template->orientation ?? 'Portrait');
    //         $pdf->setPaper($pageSize, $orientation);

    //         // Set margin untuk DomPDF
    //         if (method_exists($pdf, 'getDomPDF')) {
    //             $dompdf = $pdf->getDomPDF();
    //             $dompdf->set_option('isHtml5ParserEnabled', true);
    //             $dompdf->set_option('isRemoteEnabled', false);

    //             // Margin dalam points (1mm = 2.83465 points)
    //             // 20mm = 56.7pt, 25mm = 70.9pt
    //             $dompdf->set_option('margin_top', 56.7);
    //             $dompdf->set_option('margin_right', 70.9);
    //             $dompdf->set_option('margin_bottom', 56.7);
    //             $dompdf->set_option('margin_left', 70.9);
    //         }

    //         // Set options untuk Snappy/wkhtmltopdf (jika digunakan)
    //         if (method_exists($pdf, 'setOption')) {
    //             $pdf->setOption('margin-top', '20mm');
    //             $pdf->setOption('margin-right', '25mm');
    //             $pdf->setOption('margin-bottom', '20mm');
    //             $pdf->setOption('margin-left', '25mm');
    //             $pdf->setOption('enable-local-file-access', true);
    //         }

    //         // Generate filename - Format: PK_NIP_2024_v1.pdf
    //         $nip = $pk->pegawai->nomor_identitas ?? 'NONIP';
    //         $fileName = sprintf('PK_%s_%s_v%d.pdf', $nip, $pk->tahun, $newVersion);

    //         // Path relatif untuk database
    //         $relativePath = 'perjanjian/' . $pk->tahun . '/' . $fileName;

    //         // Ensure directory exists
    //         $directory = 'perjanjian/' . $pk->tahun;
    //         if (!Storage::disk('public')->exists($directory)) {
    //             Storage::disk('public')->makeDirectory($directory);
    //         }

    //         // Get PDF output
    //         $pdfOutput = $pdf->output();

    //         // Save to storage
    //         $saved = Storage::disk('public')->put($relativePath, $pdfOutput);

    //         if (!$saved) {
    //             throw new \Exception('Gagal menyimpan file PDF ke storage');
    //         }

    //         // Full path untuk verifikasi
    //         $fullPath = Storage::disk('public')->path($relativePath);

    //         if (!file_exists($fullPath)) {
    //             throw new \Exception('File PDF tidak ditemukan setelah disimpan: ' . $fullPath);
    //         }

    //         $fileHash = hash_file('sha256', $fullPath);
    //         $fileSizeKb = (int) round(filesize($fullPath) / 1024);

    //         // Count pages
    //         $totalPages = $this->estimatePdfPages($pdfOutput);

    //         // Generate nomor dokumen
    //         $nomorDokumen = $pk->nomor_perjanjian . '/V' . $newVersion;

    //         // Create document record
    //         $dokumen = $pk->dokumen()->create([
    //             'jenis_dokumen' => 'Pernyataan',
    //             'nomor_dokumen' => $nomorDokumen,
    //             'file_name' => $fileName,
    //             'file_path' => $relativePath,
    //             'file_hash' => $fileHash,
    //             'file_size_kb' => $fileSizeKb,
    //             'versi' => $newVersion,
    //             'total_pages' => $totalPages,
    //             'generated_by' => Auth::id(),
    //             'generated_at' => now(),
    //             'is_latest' => true,
    //             'perjanjian_kinerja_id' => $pk->id,
    //         ]);

    //         // Update PK status if still Draft
    //         if ($pk->status_dokumen === 'Draft') {
    //             $pk->update(['status_dokumen' => 'Generated']);
    //         }

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Dokumen PDF berhasil di-generate (Versi ' . $newVersion . ')',
    //             'dokumen_id' => $dokumen->id,
    //             'nomor_dokumen' => $nomorDokumen,
    //             'versi' => $newVersion,
    //             'file_name' => $fileName,
    //             'file_size_kb' => $fileSizeKb,
    //             'total_pages' => $totalPages,
    //             'download_url' => route('perjanjian-kinerja.download', $pk->id)
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Error generating PDF: ' . $e->getMessage(), [
    //             'pk_id' => $id,
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Gagal membuat dokumen PDF: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }
    /**
     * Generate PDF dokumen menggunakan template dari database
     */
    public function generate($id)
    {

        // Load data dengan relasi lengkap
        $pk = PkPerjanjianKinerja::with([
            'pegawai.jabatan',
            'pegawai.bidang',
            'atasan.jabatan',
            'template.sections',
            'sasaran.indikator.program.kegiatan.subKegiatan'
        ])->findOrFail($id);

        DB::beginTransaction();
        try {
            // Validate template exists
            if (!$pk->template) {
                throw new \Exception('Template tidak ditemukan untuk perjanjian kinerja ini.');
            }

            // Calculate version number
            $latestVersion = $pk->dokumen()->max('versi') ?? 0;
            $newVersion = $latestVersion + 1;

            // Reset previous latest
            $pk->dokumen()->where('is_latest', true)->update(['is_latest' => false]);

            // Calculate total anggaran
            $totalAnggaran = 0;
            foreach ($pk->sasaran as $sasaran) {
                foreach ($sasaran->indikator as $indikator) {
                    foreach ($indikator->program as $program) {
                        foreach ($program->kegiatan as $kegiatan) {
                            foreach ($kegiatan->subKegiatan as $subKegiatan) {
                                $totalAnggaran += $subKegiatan->anggaran ?? 0;
                            }
                        }
                    }
                }
            }

            // Update total anggaran di PK
            $pk->update(['total_anggaran' => $totalAnggaran]);

            // Create PDF menggunakan template
            $data = [
                'pk' => $pk,
                'title' => 'Perjanjian Kinerja - ' . $pk->nomor_perjanjian
            ];

            $pdf = PDF::loadView('perjanjiankinerja::pdf.perjanjian_kinerja', $data);

            // Set paper size dan orientation dari template
            $pageSize = $pk->template->page_size ?? 'A4';
            $orientation = strtolower($pk->template->orientation ?? 'Portrait');
            $pdf->setPaper($pageSize, $orientation);

            // Set margin untuk DomPDF
            if (method_exists($pdf, 'getDomPDF')) {
                $dompdf = $pdf->getDomPDF();
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $dompdf->set_option('isRemoteEnabled', false);

                // Margin dalam points (1mm = 2.83465 points)
                $dompdf->set_option('margin_top', 56.7);
                $dompdf->set_option('margin_right', 70.9);
                $dompdf->set_option('margin_bottom', 56.7);
                $dompdf->set_option('margin_left', 70.9);
            }

            // Set options untuk Snappy/wkhtmltopdf (jika digunakan)
            if (method_exists($pdf, 'setOption')) {
                $pdf->setOption('margin-top', '20mm');
                $pdf->setOption('margin-right', '25mm');
                $pdf->setOption('margin-bottom', '20mm');
                $pdf->setOption('margin-left', '25mm');
                $pdf->setOption('enable-local-file-access', true);
            }

            // Generate filename - Format: PK_NIP_2024_v1.pdf
            $nip = $pk->pegawai->nomor_identitas ?? 'NONIP';
            $fileName = sprintf('PK_%s_%s_v%d.pdf', $nip, $pk->tahun, $newVersion);

            // Path relatif untuk database
            $relativePath = 'perjanjian/' . $pk->tahun . '/' . $fileName;

            // Ensure directory exists
            $directory = 'perjanjian/' . $pk->tahun;
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Get PDF output
            $pdfOutput = $pdf->output();

            // Save to storage
            $saved = Storage::disk('public')->put($relativePath, $pdfOutput);

            if (!$saved) {
                throw new \Exception('Gagal menyimpan file PDF ke storage');
            }

            // Full path untuk verifikasi
            $fullPath = Storage::disk('public')->path($relativePath);

            if (!file_exists($fullPath)) {
                throw new \Exception('File PDF tidak ditemukan setelah disimpan: ' . $fullPath);
            }

            $fileHash = hash_file('sha256', $fullPath);
            $fileSizeKb = (int) round(filesize($fullPath) / 1024);

            // Count pages
            $totalPages = preg_match_all("/\/Page\W/", $pdfOutput, $matches);
            $totalPages = $totalPages > 0 ? $totalPages : 2;

            // Generate nomor dokumen
            $nomorDokumen = $pk->nomor_perjanjian . '/V' . $newVersion;

            // Create document record
            $dokumen = $pk->dokumen()->create([
                'jenis_dokumen' => 'Pernyataan',
                'nomor_dokumen' => $nomorDokumen,
                'file_name' => $fileName,
                'file_path' => $relativePath,
                'file_hash' => $fileHash,
                'file_size_kb' => $fileSizeKb,
                'versi' => $newVersion,
                'total_pages' => $totalPages,
                'generated_by' => Auth::id(),
                'generated_at' => now(),
                'is_latest' => true,
                'perjanjian_kinerja_id' => $pk->id,
            ]);

            // Update PK status if still Draft
            if ($pk->status_dokumen === 'Draft') {
                $pk->update(['status_dokumen' => 'Generated']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen PDF berhasil di-generate (Versi ' . $newVersion . ')',
                'dokumen_id' => $dokumen->id,
                'nomor_dokumen' => $nomorDokumen,
                'versi' => $newVersion,
                'file_name' => $fileName,
                'file_size_kb' => $fileSizeKb,
                'total_pages' => $totalPages,
                'download_url' => route('perjanjian-kinerja.download', $pk->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error generating PDF: ' . $e->getMessage(), [
                'pk_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat dokumen PDF: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Validate data completeness before generating PDF
     */
    private function validateDataCompleteness($pk)
    {
        // Validate pegawai
        if (!$pk->pegawai) {
            throw new \Exception('Data pegawai tidak ditemukan');
        }

        if (!$pk->pegawai->nama) {
            throw new \Exception('Nama pegawai tidak boleh kosong');
        }

        if (!$pk->pegawai->nomor_identitas) {
            throw new \Exception('NIP pegawai tidak boleh kosong');
        }

        // Validate atasan
        if (!$pk->atasan) {
            throw new \Exception('Data atasan tidak ditemukan');
        }

        if (!$pk->atasan->nama) {
            throw new \Exception('Nama atasan tidak boleh kosong');
        }

        // Validate sasaran
        if ($pk->sasaran->count() === 0) {
            throw new \Exception('Belum ada sasaran yang ditambahkan');
        }

        // Validate each sasaran has indicators
        foreach ($pk->sasaran as $sasaran) {
            if ($sasaran->indikator->count() === 0) {
                throw new \Exception('Sasaran "' . $sasaran->nama_sasaran . '" belum memiliki indikator');
            }

            // Validate each indicator has programs
            foreach ($sasaran->indikator as $indikator) {
                if ($indikator->program->count() === 0) {
                    throw new \Exception('Indikator "' . $indikator->indikator_sasaran . '" belum memiliki program');
                }

                // Validate each program has kegiatan
                foreach ($indikator->program as $program) {
                    if ($program->kegiatan->count() === 0) {
                        throw new \Exception('Program "' . $program->nama_program . '" belum memiliki kegiatan');
                    }

                    // Validate each kegiatan has sub kegiatan
                    foreach ($program->kegiatan as $kegiatan) {
                        if ($kegiatan->subKegiatan->count() === 0) {
                            throw new \Exception('Kegiatan "' . $kegiatan->nama_kegiatan . '" belum memiliki sub kegiatan');
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Calculate total anggaran from all sub kegiatan
     */
    private function calculateTotalAnggaran($pk)
    {
        $totalAnggaran = 0;

        foreach ($pk->sasaran as $sasaran) {
            foreach ($sasaran->indikator as $indikator) {
                foreach ($indikator->program as $program) {
                    foreach ($program->kegiatan as $kegiatan) {
                        foreach ($kegiatan->subKegiatan as $subKegiatan) {
                            $totalAnggaran += $subKegiatan->anggaran ?? 0;
                        }
                    }
                }
            }
        }

        return $totalAnggaran;
    }

    /**
     * Get statistics from perjanjian kinerja
     */
    private function getStatistics($pk)
    {
        $totalSasaran = $pk->sasaran->count();

        $totalIndikator = 0;
        $totalProgram = 0;
        $totalKegiatan = 0;
        $totalSubKegiatan = 0;

        foreach ($pk->sasaran as $sasaran) {
            $totalIndikator += $sasaran->indikator->count();

            foreach ($sasaran->indikator as $indikator) {
                $totalProgram += $indikator->program->count();

                foreach ($indikator->program as $program) {
                    $totalKegiatan += $program->kegiatan->count();

                    foreach ($program->kegiatan as $kegiatan) {
                        $totalSubKegiatan += $kegiatan->subKegiatan->count();
                    }
                }
            }
        }

        return [
            'total_sasaran' => $totalSasaran,
            'total_indikator' => $totalIndikator,
            'total_program' => $totalProgram,
            'total_kegiatan' => $totalKegiatan,
            'total_sub_kegiatan' => $totalSubKegiatan,
            'total_anggaran' => $pk->total_anggaran,
            'total_anggaran_formatted' => 'Rp ' . number_format($pk->total_anggaran, 0, ',', '.'),
        ];
    }

    /**
     * Estimate PDF pages from PDF output
     */
    // private function estimatePdfPages($pdfOutput)
    // {
    //     try {
    //         // Try to count pages from PDF
    //         $pageCount = preg_match_all("/\/Page\W/", $pdfOutput, $matches);
    //         return $pageCount > 0 ? $pageCount : 2; // Default 2 pages (pernyataan + formulir)
    //     } catch (\Exception $e) {
    //         Log::warning('Failed to count PDF pages: ' . $e->getMessage());
    //         return 2; // Default fallback
    //     }
    // }

    // /**
    //  * Preview PDF in browser
    //  */
    // public function preview($id)
    // {
    //     try {
    //         $pk = PkPerjanjianKinerja::with([
    //             'pegawai.jabatan',
    //             'atasan.jabatan',
    //             'template',
    //             'sasaran' => function ($query) {
    //                 $query->orderBy('urutan')
    //                     ->with([
    //                         'indikator' => function ($qi) {
    //                             $qi->orderBy('urutan')
    //                                 ->with([
    //                                     'program' => function ($qp) {
    //                                         $qp->orderBy('urutan')
    //                                             ->with([
    //                                                 'kegiatan' => function ($qk) {
    //                                                     $qk->orderBy('urutan')
    //                                                         ->with([
    //                                                             'subKegiatan' => function ($qs) {
    //                                                                 $qs->orderBy('urutan');
    //                                                             }
    //                                                         ]);
    //                                                 }
    //                                             ]);
    //                                     }
    //                                 ]);
    //                         }
    //                     ]);
    //             }
    //         ])->findOrFail($id);

    //         // Validasi data wajib
    //         if (!$pk->pegawai) {
    //             return back()->with('error', 'Data pegawai tidak ditemukan.');
    //         }

    //         if (!$pk->atasan) {
    //             return back()->with('error', 'Data atasan tidak ditemukan.');
    //         }

    //         $data = [
    //             'pk' => $pk,
    //             'title' => 'Preview - Perjanjian Kinerja Tahun ' . $pk->tahun
    //         ];

    //         // Load view PDF
    //         $pdf = PDF::loadView('perjanjiankinerja::pdf.perjanjian_kinerja', $data);

    //         // Set paper size dan orientation
    //         $pageSize = $pk->template->page_size ?? 'A4';
    //         $orientation = strtolower($pk->template->orientation ?? 'portrait');
    //         $pdf->setPaper($pageSize, $orientation);

    //         // Set options untuk preview yang lebih baik
    //         $pdf->setOptions([
    //             'isHtml5ParserEnabled' => true,
    //             'isRemoteEnabled' => true,
    //             'defaultFont' => 'Times New Roman'
    //         ]);

    //         // Stream ke browser
    //         return $pdf->stream('preview_pk_' . $pk->pegawai->nama . '_' . $pk->tahun . '.pdf');
    //     } catch (\Exception $e) {
    //         Log::error('Error Preview PDF: ' . $e->getMessage());
    //         return back()->with('error', 'Gagal menampilkan preview PDF: ' . $e->getMessage());
    //     }
    // }
    // /**
    //  * Preview PDF dokumen
    //  */
    public function preview($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);
        $dokumen = $pk->dokumen()->where('is_latest', true)->first();

        if (!$dokumen) {
            return back()->with('error', 'Dokumen belum di-generate.');
        }

        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            return back()->with('error', 'File PDF tidak ditemukan di storage.');
        }

        $fullPath = Storage::disk('public')->path($dokumen->file_path);

        return response()->file(
            $fullPath,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Sign perjanjian kinerja
     */
    public function sign(Request $request, $id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        if ($pk->tanggal_ttd) {
            return response()->json([
                'success' => false,
                'message' => 'Perjanjian Kinerja sudah ditandatangani sebelumnya.'
            ], 422);
        }

        if (!in_array($pk->status_dokumen, ['Menunggu_TTD', 'Generated'])) {
            return response()->json([
                'success' => false,
                'message' => 'Status dokumen harus Menunggu TTD atau Generated untuk dapat ditandatangani.'
            ], 422);
        }

        $validated = $request->validate([
            'tanggal_ttd' => 'required|date',
            'tempat_ttd' => 'required|string|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Generate new PDF if needed (with signature info)
            $latestDokumen = $pk->dokumen()->where('is_latest', true)->first();
            if (!$latestDokumen) {
                // Generate PDF first
                $generateResponse = $this->generate($pk->id);
                $responseData = $generateResponse->getData();

                if (!$responseData->success) {
                    throw new \Exception('Gagal generate dokumen: ' . $responseData->message);
                }
            }

            // Update PK
            $pk->update([
                'tanggal_ttd' => $validated['tanggal_ttd'],
                'tempat_ttd' => $validated['tempat_ttd'],
                'status_dokumen' => 'Aktif',
                'is_locked' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perjanjian Kinerja berhasil ditandatangani dan diaktifkan.',
                'pk_id' => $pk->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error signing PK: ' . $e->getMessage(), [
                'pk_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menandatangani Perjanjian Kinerja: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Estimate PDF pages from output
     */
    private function estimatePdfPages($pdfOutput)
    {
        try {
            // Count "/Type /Page" occurrences in PDF
            $pageCount = substr_count($pdfOutput, '/Type /Page');
            return $pageCount > 0 ? $pageCount : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }
}
