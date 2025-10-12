<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Dokumen\Models\Template;
use Modules\Dokumen\Models\JenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\TemplateService;

class TemplateController extends Controller
{
    protected $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * Display a listing of templates
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $query = Template::with(['jenis', 'creator'])
                    ->orderBy('created_at', 'desc');

                if ($request->filled('jenis_id')) {
                    $query->where('jenis_id', $request->jenis_id);
                }

                if ($request->filled('is_active')) {
                    $query->where('is_active', $request->is_active);
                }

                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                            ->orWhere('kode', 'like', "%{$search}%")
                            ->orWhere('deskripsi', 'like', "%{$search}%");
                    });
                }

                $templates = $query->get();

                return response()->json($templates);
            } catch (\Exception $e) {
                Log::error('Error loading templates: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        return view('dokumen::template.index');
    }

    /**
     * Get available variables
     */
    public function getVariables()
    {
        try {
            $variables = $this->templateService->getSystemVariables();
            return response()->json($variables);
        } catch (\Exception $e) {
            Log::error('Error getting variables: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'jenis_id' => 'required|exists:doc_jenis,id',
                'nama' => 'required|string|max:255',
                'kode' => 'required|string|max:50|unique:doc_template,kode',
                'deskripsi' => 'nullable|string',
                'content' => 'required|string',
                'header' => 'nullable|string',
                'footer' => 'nullable|string',
                'format_output' => 'required|in:html,docx,pdf',
                'is_active' => 'boolean',
                'settings' => 'nullable|array',
            ]);

            $validated['created_by'] = auth()->id();

            // Extract variables from content
            preg_match_all('/\{\{([^}]+)\}\}/', $validated['content'], $matches);
            $validated['variables'] = array_unique($matches[1] ?? []);

            $template = Template::create($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dibuat',
                'data' => $template->load('jenis')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified template
     */
    public function show($id)
    {
        try {
            $template = Template::with(['jenis', 'creator', 'updater'])->findOrFail($id);
            return response()->json($template);
        } catch (\Exception $e) {
            Log::error('Error showing template: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $template = Template::findOrFail($id);

            $validated = $request->validate([
                'jenis_id' => 'required|exists:doc_jenis,id',
                'nama' => 'required|string|max:255',
                'kode' => 'required|string|max:50|unique:doc_template,kode,' . $id,
                'deskripsi' => 'nullable|string',
                'content' => 'required|string',
                'header' => 'nullable|string',
                'footer' => 'nullable|string',
                'format_output' => 'required|in:html,docx,pdf',
                'is_active' => 'boolean',
                'settings' => 'nullable|array',
            ]);

            $validated['updated_by'] = auth()->id();

            // Extract variables
            preg_match_all('/\{\{([^}]+)\}\}/', $validated['content'], $matches);
            $validated['variables'] = array_unique($matches[1] ?? []);

            $template->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diupdate',
                'data' => $template->load('jenis')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal update template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified template
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $template = Template::findOrFail($id);
            $template->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview template
     */
    public function preview(Request $request, $id)
    {
        try {
            $template = Template::findOrFail($id);
            $sampleData = $request->input('sample_data', []);

            $html = $this->templateService->previewTemplate($template, $sampleData);

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Error previewing template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal preview template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate document from template
     */
    public function generate(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $template = Template::findOrFail($id);
            $user = auth()->user();
            $additionalData = $request->input('data', []);

            $result = $this->templateService->generateFromTemplate($template, $user, $additionalData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil di-generate',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error generating document: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate dokumen: ' . $e->getMessage()
            ], 500);
        }
    }
}
