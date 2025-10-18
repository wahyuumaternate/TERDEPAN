<?php

namespace Modules\PerjanjianKinerja\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;
use Modules\PerjanjianKinerja\Models\PkDokumen;
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
     * Show the form for creating a new perjanjian kinerja
     */
    public function create()
    {
        $pegawais = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereNotNull('atasan_langsung_id')
            ->with(['jabatan', 'bidang'])
            ->orderBy('nama')
            ->get();

        $templates = PkTemplate::where('is_active', true)
            ->where('tahun', date('Y'))
            ->with('jabatan')
            ->get();

        $currentYear = date('Y');

        return view('perjanjiankinerja::create', compact(
            'pegawais',
            'templates',
            'currentYear'
        ));
    }

    /**
     * Store a newly created perjanjian kinerja
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'template_id' => 'required|exists:pk_template,id',
            'tahun' => 'required|integer|min:2020|max:2100',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
            'tempat_ttd' => 'required|string|max:100',
            'catatan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Get pegawai data
            $pegawai = MasterPegawai::findOrFail($validated['pegawai_id']);

            if (!$pegawai->atasan_langsung_id) {
                throw new \Exception('Pegawai tidak memiliki atasan langsung.');
            }

            // Generate nomor perjanjian
            $nomor = PkPerjanjianKinerja::generateNomorPerjanjian($validated['tahun']);

            // Create perjanjian kinerja
            $pk = PkPerjanjianKinerja::create([
                'nomor_perjanjian' => $nomor,
                'pegawai_id' => $validated['pegawai_id'],
                'atasan_id' => $pegawai->atasan_langsung_id,
                'template_id' => $validated['template_id'],
                'tahun' => $validated['tahun'],
                'periode_mulai' => $validated['periode_mulai'],
                'periode_selesai' => $validated['periode_selesai'],
                'tempat_ttd' => $validated['tempat_ttd'],
                'status_dokumen' => 'Draft',
                'catatan' => $validated['catatan'],
                'is_locked' => false,
                'total_anggaran' => 0,
            ]);

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.show', $pk->id)
                ->with('success', 'Perjanjian Kinerja berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat Perjanjian Kinerja: ' . $e->getMessage());
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
     * Show the form for editing the specified perjanjian kinerja
     */
    public function edit($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        if (!$pk->isEditable()) {
            return back()->with('warning', 'Perjanjian Kinerja tidak dapat diedit karena sudah dikunci atau status tidak memungkinkan.');
        }

        $pegawais = MasterPegawai::where('status_aktif', 'Aktif')
            ->whereNotNull('atasan_langsung_id')
            ->with(['jabatan', 'bidang'])
            ->orderBy('nama')
            ->get();

        $templates = PkTemplate::where('tahun', $pk->tahun)
            ->with('jabatan')
            ->get();

        return view('perjanjiankinerja::edit', compact('pk', 'pegawais', 'templates'));
    }

    /**
     * Update the specified perjanjian kinerja
     */
    public function update(Request $request, $id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        if (!$pk->isEditable()) {
            return back()->with('error', 'Perjanjian Kinerja tidak dapat diedit karena sudah dikunci atau status tidak memungkinkan.');
        }

        $validated = $request->validate([
            'pegawai_id' => 'required|exists:master_pegawai,id',
            'template_id' => 'required|exists:pk_template,id',
            'periode_mulai' => 'required|date',
            'periode_selesai' => 'required|date|after:periode_mulai',
            'tempat_ttd' => 'required|string|max:100',
            'catatan' => 'nullable|string',
            'status_dokumen' => 'required|in:Draft,Generated,Menunggu_TTD,Aktif,Selesai,Dibatalkan',
        ]);

        DB::beginTransaction();
        try {
            // Get pegawai data
            $pegawai = MasterPegawai::findOrFail($validated['pegawai_id']);

            if (!$pegawai->atasan_langsung_id) {
                throw new \Exception('Pegawai tidak memiliki atasan langsung.');
            }

            $pk->update([
                'pegawai_id' => $validated['pegawai_id'],
                'atasan_id' => $pegawai->atasan_langsung_id,
                'template_id' => $validated['template_id'],
                'periode_mulai' => $validated['periode_mulai'],
                'periode_selesai' => $validated['periode_selesai'],
                'tempat_ttd' => $validated['tempat_ttd'],
                'catatan' => $validated['catatan'],
                'status_dokumen' => $validated['status_dokumen'],
            ]);

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.show', $pk->id)
                ->with('success', 'Perjanjian Kinerja berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate Perjanjian Kinerja: ' . $e->getMessage());
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
     * Generate PDF dokumen
     */
    public function generate($id)
    {
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

            // Create PDF
            $data = [
                'pk' => $pk,
                'title' => 'Perjanjian Kinerja - ' . $pk->nomor_perjanjian
            ];

            $pdf = PDF::loadView('perjanjiankinerja::pdf.perjanjian_kinerja', $data);

            // Set paper size
            $pdf->setPaper($pk->template->page_size ?? 'A4', $pk->template->orientation ?? 'Portrait');

            // ⭐ PENTING: SET MARGIN SECARA EKSPLISIT
            // Untuk wkhtmltopdf (jika menggunakan snappy/laravel-snappy):
            if (method_exists($pdf, 'setOption')) {
                $pdf->setOption('margin-top', '20mm');
                $pdf->setOption('margin-right', '25mm');
                $pdf->setOption('margin-bottom', '20mm');
                $pdf->setOption('margin-left', '25mm');
                $pdf->setOption('enable-local-file-access', true);
            }

            // Untuk dompdf (jika menggunakan barryvdh/laravel-dompdf):
            if (method_exists($pdf, 'getDomPDF')) {
                $dompdf = $pdf->getDomPDF();
                $dompdf->set_option('isHtml5ParserEnabled', true);
                $dompdf->set_option('isRemoteEnabled', false);

                // Dompdf margin setting (dalam points: 1mm = 2.83465 points)
                // 20mm = 56.7pt, 25mm = 70.9pt
                $dompdf->set_option('margin_top', 56.7);
                $dompdf->set_option('margin_right', 70.9);
                $dompdf->set_option('margin_bottom', 56.7);
                $dompdf->set_option('margin_left', 70.9);
            }

            // Generate filename - Format: PK_NIP_2024_v1.pdf
            $nip = $pk->pegawai->nomor_identitas ?? 'NONIP';
            $fileName = sprintf('PK_%s_%s_v%d.pdf', $nip, $pk->tahun, $newVersion);

            // Path yang akan disimpan di DATABASE (relatif dari public disk)
            // Format: perjanjian/2025/PK_NONIP_2025_v1.pdf
            $relativePath = 'perjanjian/' . $pk->tahun . '/' . $fileName;

            // Path lengkap untuk operasi Storage Laravel (dengan disk 'public')
            // Storage facade akan otomatis menambahkan 'storage/app/public/'
            $storageDiskPath = $relativePath; // Tidak perlu tambah 'public/' lagi

            // Ensure directory exists dalam disk public
            $directory = 'perjanjian/' . $pk->tahun;
            if (!Storage::disk('public')->exists($directory)) {
                Storage::disk('public')->makeDirectory($directory);
            }

            // Get PDF output
            $pdfOutput = $pdf->output();

            // Save to storage menggunakan disk 'public'
            $saved = Storage::disk('public')->put($storageDiskPath, $pdfOutput);

            if (!$saved) {
                throw new \Exception('Gagal menyimpan file PDF ke storage');
            }

            // Full path untuk verifikasi dan hash
            // storage/app/public/perjanjian/2025/PK_NONIP_2025_v1.pdf
            $fullPath = Storage::disk('public')->path($storageDiskPath);

            if (!file_exists($fullPath)) {
                throw new \Exception('File PDF tidak ditemukan setelah disimpan: ' . $fullPath);
            }

            $fileHash = hash_file('sha256', $fullPath);
            $fileSizeKb = (int) round(filesize($fullPath) / 1024);

            // Count pages (simple estimation)
            $totalPages = $this->estimatePdfPages($pdfOutput);

            // Generate nomor dokumen - sama dengan nomor_perjanjian + versi
            $nomorDokumen = $pk->nomor_perjanjian . '/V' . $newVersion;

            // Create document record - simpan path RELATIF saja
            $dokumen = $pk->dokumen()->create([
                'jenis_dokumen' => 'Pernyataan', // Sesuai ENUM: Pernyataan, Formulir, Lampiran
                'nomor_dokumen' => $nomorDokumen,
                'file_name' => $fileName,
                'file_path' => $relativePath, // Path relatif: perjanjian/2025/...
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
     * Download PDF dokumen
     */
    public function download($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);
        $dokumen = $pk->dokumen()->where('is_latest', true)->first();

        if (!$dokumen) {
            return back()->with('error', 'Dokumen belum di-generate.');
        }

        // Gunakan disk 'public' untuk akses file
        if (!Storage::disk('public')->exists($dokumen->file_path)) {
            return back()->with('error', 'File PDF tidak ditemukan di storage.');
        }

        $fullPath = Storage::disk('public')->path($dokumen->file_path);

        return response()->download(
            $fullPath,
            $dokumen->file_name,
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Preview PDF dokumen
     */
    public function preview($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);
        $dokumen = $pk->dokumen()->where('is_latest', true)->first();

        if (!$dokumen) {
            return back()->with('error', 'Dokumen belum di-generate.');
        }

        // Gunakan disk 'public' untuk akses file
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
     * Simple estimation based on page break markers
     */
    private function estimatePdfPages($pdfOutput)
    {
        try {
            // Count "/Type /Page" occurrences in PDF
            $pageCount = substr_count($pdfOutput, '/Type /Page');
            return $pageCount > 0 ? $pageCount : 1;
        } catch (\Exception $e) {
            return 1; // Default to 1 page if estimation fails
        }
    }
}
