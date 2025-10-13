<?php

namespace Modules\PerjanjianKinerja\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\PerjanjianKinerja\Models\PkTemplate;
use Modules\PerjanjianKinerja\Models\PkTemplateSection;
use App\Models\MasterJabatan;

class TemplateController extends Controller
{
    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        $query = PkTemplate::with(['jabatan', 'sections']);

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_template', 'like', "%{$request->search}%")
                    ->orWhere('nama_template', 'like', "%{$request->search}%");
            });
        }

        // Filter by jabatan
        if ($request->filled('jabatan_id')) {
            $query->where('jabatan_id', $request->jabatan_id);
        }

        // Filter by tahun
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $templates = $query->get();

        // Get filter options
        $jabatans = MasterJabatan::where('is_active', true)
            ->where('is_struktural', true)
            ->orderBy('level')
            ->get();

        $tahuns = PkTemplate::distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun');

        return view('perjanjiankinerja::template.index', compact(
            'templates',
            'jabatans',
            'tahuns'
        ));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        $jabatans = MasterJabatan::where('is_active', true)
            ->where('is_struktural', true)
            ->orderBy('level')
            ->get();

        $pageSizes = ['A4', 'Legal', 'Letter'];
        $orientations = ['Portrait', 'Landscape'];
        $currentYear = date('Y');

        return view('perjanjiankinerja::template.create', compact(
            'jabatans',
            'pageSizes',
            'orientations',
            'currentYear'
        ));
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_template' => 'required|string|max:50|unique:pk_template,kode_template',
            'nama_template' => 'required|string|max:255',
            'jabatan_id' => 'required|exists:master_jabatan,id',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kop_surat_html' => 'nullable|string',
            'header_template' => 'nullable|string',
            'pernyataan_pembuka' => 'nullable|string',
            'pernyataan_penutup' => 'nullable|string',
            'footer_template' => 'nullable|string',
            'page_size' => 'required|in:A4,Legal,Letter',
            'orientation' => 'required|in:Portrait,Landscape',
            'is_active' => 'boolean',
        ], [
            'kode_template.required' => 'Kode template wajib diisi',
            'kode_template.unique' => 'Kode template sudah digunakan',
            'nama_template.required' => 'Nama template wajib diisi',
            'jabatan_id.required' => 'Jabatan wajib dipilih',
            'jabatan_id.exists' => 'Jabatan tidak valid',
            'tahun.required' => 'Tahun wajib diisi',
            'page_size.required' => 'Ukuran halaman wajib dipilih',
            'orientation.required' => 'Orientasi wajib dipilih',
        ]);

        DB::beginTransaction();
        try {
            // Check if there's already active template for this jabatan+tahun
            if ($request->boolean('is_active')) {
                PkTemplate::where('jabatan_id', $validated['jabatan_id'])
                    ->where('tahun', $validated['tahun'])
                    ->update(['is_active' => false]);
            }

            $template = PkTemplate::create($validated);

            // Create default sections
            $this->createDefaultSections($template);

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.template.show', $template->id)
                ->with('success', 'Template berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal membuat template: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified template
     */
    public function show($id)
    {
        $template = PkTemplate::with(['jabatan', 'sections' => function ($query) {
            $query->orderBy('urutan');
        }, 'perjanjianKinerja'])
            ->findOrFail($id);

        $usageCount = $template->perjanjianKinerja()->count();
        $activeUsage = $template->perjanjianKinerja()
            ->whereIn('status_dokumen', ['Aktif', 'Menunggu_TTD'])
            ->count();

        return view('perjanjiankinerja::template.show', compact(
            'template',
            'usageCount',
            'activeUsage'
        ));
    }

    /**
     * Show the form for editing the specified template
     */
    public function edit($id)
    {
        $template = PkTemplate::with('sections')->findOrFail($id);

        // Check if template can be edited
        $activeUsage = $template->perjanjianKinerja()
            ->whereIn('status_dokumen', ['Aktif', 'Menunggu_TTD'])
            ->count();

        if ($activeUsage > 0) {
            return back()->with('warning', 'Template tidak dapat diedit karena sedang digunakan oleh perjanjian kinerja aktif.');
        }

        $jabatans = MasterJabatan::where('is_active', true)
            ->where('is_struktural', true)
            ->orderBy('level')
            ->get();

        $pageSizes = ['A4', 'Legal', 'Letter'];
        $orientations = ['Portrait', 'Landscape'];

        return view('perjanjiankinerja::template.edit', compact(
            'template',
            'jabatans',
            'pageSizes',
            'orientations'
        ));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, $id)
    {
        $template = PkTemplate::findOrFail($id);

        // Check if template can be edited
        $activeUsage = $template->perjanjianKinerja()
            ->whereIn('status_dokumen', ['Aktif', 'Menunggu_TTD'])
            ->count();

        if ($activeUsage > 0) {
            return back()->with('error', 'Template tidak dapat diedit karena sedang digunakan oleh perjanjian kinerja aktif.');
        }

        $validated = $request->validate([
            'kode_template' => 'required|string|max:50|unique:pk_template,kode_template,' . $id,
            'nama_template' => 'required|string|max:255',
            'jabatan_id' => 'required|exists:master_jabatan,id',
            'tahun' => 'required|integer|min:2020|max:2100',
            'kop_surat_html' => 'nullable|string',
            'header_template' => 'nullable|string',
            'pernyataan_pembuka' => 'nullable|string',
            'pernyataan_penutup' => 'nullable|string',
            'footer_template' => 'nullable|string',
            'page_size' => 'required|in:A4,Legal,Letter',
            'orientation' => 'required|in:Portrait,Landscape',
            'is_active' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            // Check if there's already active template for this jabatan+tahun
            if ($request->boolean('is_active') && !$template->is_active) {
                PkTemplate::where('jabatan_id', $validated['jabatan_id'])
                    ->where('tahun', $validated['tahun'])
                    ->where('id', '!=', $id)
                    ->update(['is_active' => false]);
            }

            // Increment version if content changed
            $contentFields = ['kop_surat_html', 'header_template', 'pernyataan_pembuka', 'pernyataan_penutup', 'footer_template'];
            $contentChanged = false;
            foreach ($contentFields as $field) {
                if ($template->$field !== $validated[$field]) {
                    $contentChanged = true;
                    break;
                }
            }

            if ($contentChanged) {
                $validated['versi'] = $template->versi + 1;
            }

            $template->update($validated);

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.template.show', $template->id)
                ->with('success', 'Template berhasil diupdate.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Gagal mengupdate template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified template
     */
    public function destroy($id)
    {
        $template = PkTemplate::findOrFail($id);

        // Check if template can be deleted
        $usageCount = $template->perjanjianKinerja()->count();

        if ($usageCount > 0) {
            return back()->with('error', 'Template tidak dapat dihapus karena masih digunakan oleh ' . $usageCount . ' perjanjian kinerja.');
        }

        DB::beginTransaction();
        try {
            // Delete sections first
            $template->sections()->delete();

            // Delete template
            $template->delete();

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.template.index')
                ->with('success', 'Template berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }

    /**
     * Activate template
     */
    public function activate($id)
    {
        $template = PkTemplate::findOrFail($id);

        DB::beginTransaction();
        try {
            // Deactivate other templates for same jabatan and tahun
            PkTemplate::where('jabatan_id', $template->jabatan_id)
                ->where('tahun', $template->tahun)
                ->where('id', '!=', $id)
                ->update(['is_active' => false]);

            // Activate this template
            $template->is_active = true;
            $template->save();

            DB::commit();

            return back()->with('success', 'Template berhasil diaktifkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengaktifkan template: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate template
     */
    public function duplicate(Request $request, $id)
    {
        $template = PkTemplate::with('sections')->findOrFail($id);

        $validated = $request->validate([
            'kode_template' => 'required|string|max:50|unique:pk_template,kode_template',
            'nama_template' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            // Create new template
            $newTemplate = $template->replicate();
            $newTemplate->kode_template = $validated['kode_template'];
            $newTemplate->nama_template = $validated['nama_template'];
            $newTemplate->is_active = false;
            $newTemplate->versi = 1;
            $newTemplate->save();

            // Duplicate sections
            foreach ($template->sections as $section) {
                $newSection = $section->replicate();
                $newSection->template_id = $newTemplate->id;
                $newSection->save();
            }

            DB::commit();

            return redirect()
                ->route('perjanjian-kinerja.template.show', $newTemplate->id)
                ->with('success', 'Template berhasil diduplikasi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menduplikasi template: ' . $e->getMessage());
        }
    }

    /**
     * Create default sections for new template
     */
    private function createDefaultSections(PkTemplate $template)
    {
        $defaultSections = [
            [
                'section_code' => 'KOP_SURAT',
                'section_name' => 'Kop Surat',
                'section_type' => 'static',
                'content_template' => '<div class="kop-surat">{kop_surat_html}</div>',
                'urutan' => 1,
                'is_required' => true,
            ],
            [
                'section_code' => 'PEMBUKA',
                'section_name' => 'Pernyataan Pembuka',
                'section_type' => 'static',
                'content_template' => '<div class="pembuka">{pernyataan_pembuka}</div>',
                'urutan' => 2,
                'is_required' => true,
            ],
            [
                'section_code' => 'INFO_PEGAWAI',
                'section_name' => 'Informasi Pegawai',
                'section_type' => 'dynamic',
                'content_template' => $this->getInfoPegawaiTemplate(),
                'urutan' => 3,
                'is_required' => true,
            ],
            [
                'section_code' => 'SASARAN',
                'section_name' => 'Sasaran dan Indikator',
                'section_type' => 'table',
                'content_template' => $this->getSasaranTableTemplate(),
                'urutan' => 4,
                'is_required' => true,
            ],
            [
                'section_code' => 'PROGRAM',
                'section_name' => 'Program dan Kegiatan',
                'section_type' => 'table',
                'content_template' => $this->getProgramTableTemplate(),
                'urutan' => 5,
                'is_required' => true,
            ],
            [
                'section_code' => 'ANGGARAN',
                'section_name' => 'Total Anggaran',
                'section_type' => 'dynamic',
                'content_template' => '<div class="total-anggaran"><strong>Total Anggaran:</strong> Rp {total_anggaran}</div>',
                'urutan' => 6,
                'is_required' => true,
            ],
            [
                'section_code' => 'PENUTUP',
                'section_name' => 'Pernyataan Penutup',
                'section_type' => 'static',
                'content_template' => '<div class="penutup">{pernyataan_penutup}</div>',
                'urutan' => 7,
                'is_required' => true,
            ],
            [
                'section_code' => 'TTD',
                'section_name' => 'Tanda Tangan',
                'section_type' => 'dynamic',
                'content_template' => $this->getTTDTemplate(),
                'urutan' => 8,
                'is_required' => true,
            ],
        ];

        foreach ($defaultSections as $section) {
            $template->sections()->create($section);
        }
    }

    /**
     * Preview template as PDF
     */
    public function previewPdf($id)
    {
        $template = PkTemplate::with(['jabatan', 'sections' => function ($query) {
            $query->orderBy('urutan');
        }])->findOrFail($id);

        $pdf = Pdf::loadView('perjanjiankinerja::template.pdf', compact('template'));

        // Set paper size and orientation
        $pdf->setPaper($template->page_size, strtolower($template->orientation));

        return $pdf->stream($template->kode_template . '_preview.pdf');
    }

    /**
     * Download template as PDF
     */
    public function downloadPdf($id)
    {
        $template = PkTemplate::with(['jabatan', 'sections' => function ($query) {
            $query->orderBy('urutan');
        }])->findOrFail($id);

        $pdf = PDF::loadView('perjanjiankinerja::template.pdf', compact('template'));

        // Set paper size and orientation
        $pdf->setPaper($template->page_size, strtolower($template->orientation));

        return $pdf->download($template->kode_template . '_template.pdf');
    }

    /**
     * Template helpers
     */
    private function getInfoPegawaiTemplate()
    {
        return '<table style="width: 100%; margin: 20px 0;">
            <tr><td width="150px">Nama</td><td>: {nama_pegawai}</td></tr>
            <tr><td>NIP</td><td>: {nip_pegawai}</td></tr>
            <tr><td>Jabatan</td><td>: {jabatan_pegawai}</td></tr>
            <tr><td>Unit Kerja</td><td>: {bidang_pegawai}</td></tr>
        </table>';
    }

    private function getSasaranTableTemplate()
    {
        return '<table border="1" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="padding: 8px;">No</th>
                    <th style="padding: 8px;">Sasaran Strategis</th>
                    <th style="padding: 8px;">Indikator</th>
                    <th style="padding: 8px;">Target</th>
                    <th style="padding: 8px;">Satuan</th>
                </tr>
            </thead>
            <tbody>
                {sasaran_rows}
            </tbody>
        </table>';
    }

    private function getProgramTableTemplate()
    {
        return '<table border="1" style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="padding: 8px;">No</th>
                    <th style="padding: 8px;">Program/Kegiatan</th>
                    <th style="padding: 8px;">Anggaran (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {program_rows}
            </tbody>
        </table>';
    }

    private function getTTDTemplate()
    {
        return '<table width="100%" style="margin-top: 30px;">
            <tr>
                <td width="50%" style="text-align: center;">
                    <p>Pihak Pertama,</p>
                    <br><br><br>
                    <p style="text-decoration: underline;">{nama_pegawai}</p>
                    <p>NIP. {nip_pegawai}</p>
                </td>
                <td width="50%" style="text-align: center;">
                    <p>Pihak Kedua,</p>
                    <br><br><br>
                    <p style="text-decoration: underline;">{nama_atasan}</p>
                    <p>NIP. {nip_atasan}</p>
                </td>
            </tr>
        </table>';
    }
}
