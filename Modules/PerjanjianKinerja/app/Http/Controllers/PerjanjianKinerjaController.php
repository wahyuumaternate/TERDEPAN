<?php

namespace Modules\PerjanjianKinerja\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\PerjanjianKinerja\Models\PkPerjanjianKinerja;
use Modules\PerjanjianKinerja\Models\PkTemplate;
use Modules\PerjanjianKinerja\Models\PkDokumen;
use App\Models\MasterPegawai;
use App\Models\MasterJabatan;
use App\Models\MasterBidang;
use Carbon\Carbon;
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
            return back()->with('error', 'Perjanjian Kinerja tidak dapat dihapus karena sudah dikunci.');
        }

        DB::beginTransaction();
        try {
            // Delete related data
            $pk->sasaran()->delete();
            $pk->dokumen()->delete();

            // Delete perjanjian kinerja
            $pk->delete();

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.index')
                ->with('success', 'Perjanjian Kinerja berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus Perjanjian Kinerja: ' . $e->getMessage());
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
            return back()->with('warning', 'Perjanjian Kinerja sudah dalam keadaan terkunci.');
        }

        try {
            $pk->update([
                'is_locked' => true
            ]);

            return back()->with('success', 'Perjanjian Kinerja berhasil dikunci.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengunci Perjanjian Kinerja: ' . $e->getMessage());
        }
    }

    /**
     * Unlock perjanjian kinerja
     */
    public function unlock($id)
    {
        $pk = PkPerjanjianKinerja::findOrFail($id);

        if (!$pk->is_locked) {
            return back()->with('warning', 'Perjanjian Kinerja sudah dalam keadaan tidak terkunci.');
        }

        // Check if document is signed, don't allow unlocking if signed
        if ($pk->tanggal_ttd) {
            return back()->with('error', 'Perjanjian Kinerja yang sudah ditandatangani tidak dapat dibuka kuncinya.');
        }

        try {
            $pk->update([
                'is_locked' => false
            ]);

            return back()->with('success', 'Perjanjian Kinerja berhasil dibuka kuncinya.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuka kunci Perjanjian Kinerja: ' . $e->getMessage());
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

            // Set PDF options based on template
            $pdf->setPaper($pk->template->page_size, $pk->template->orientation);

            // Generate filename
            $filename = 'PK_' . str_replace('/', '_', $pk->nomor_perjanjian) . '_v' . $newVersion . '.pdf';

            // Save to storage
            $filePath = 'perjanjian_kinerja/' . $pk->tahun . '/' . $filename;
            Storage::put('public/' . $filePath, $pdf->output());

            // Create document record
            $dokumen = $pk->dokumen()->create([
                'nama_dokumen' => $filename,
                'file_path' => $filePath,
                'versi' => $newVersion,
                'is_latest' => true,
                'keterangan' => 'Generated on ' . now()->format('d M Y H:i:s')
            ]);

            // Update PK status
            $pk->update([
                'status_dokumen' => 'Generated'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen PDF berhasil di-generate',
                'dokumen_id' => $dokumen->id,
                'download_url' => route('perjanjian-kinerja.download', $pk->id)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
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

        if (!Storage::exists('public/' . $dokumen->file_path)) {
            return back()->with('error', 'File PDF tidak ditemukan.');
        }

        return response()->download(
            storage_path('app/public/' . $dokumen->file_path),
            $dokumen->nama_dokumen,
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

        if (!Storage::exists('public/' . $dokumen->file_path)) {
            return back()->with('error', 'File PDF tidak ditemukan.');
        }

        return response()->file(
            storage_path('app/public/' . $dokumen->file_path),
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

        if ($pk->status_dokumen !== 'Menunggu_TTD' && $pk->status_dokumen !== 'Generated') {
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
            // Update PK
            $pk->update([
                'tanggal_ttd' => $validated['tanggal_ttd'],
                'tempat_ttd' => $validated['tempat_ttd'],
                'status_dokumen' => 'Aktif',
                'is_locked' => true,
            ]);

            // Generate new PDF if needed
            if (!$pk->dokumen()->where('is_latest', true)->exists()) {
                $this->generate($pk->id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Perjanjian Kinerja berhasil ditandatangani dan diaktifkan.',
                'pk_id' => $pk->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandatangani Perjanjian Kinerja: ' . $e->getMessage()
            ], 500);
        }
    }
}
