<?php

namespace Modules\Dokumen\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Dokumen\Models\TemplateGenerated;
use Modules\Dokumen\Models\JenisDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentHistoryController extends Controller
{
    /**
     * Display document history page
     */
    public function index()
    {
        return view('dokumen::template.riwayat');
    }

    /**
     * Get user's document history
     */
    public function getUserDocuments(Request $request)
    {
        try {
            $userId = Auth::id();

            $documents = TemplateGenerated::with([
                'template.jenis',
                'dokumen.jenis',
                'user'
            ])
                ->where('user_id', $userId)
                ->orderBy('generated_at', 'desc')
                ->get()
                ->map(function ($doc) {
                    // Determine format from file path
                    $format = 'unknown';
                    if ($doc->file_path) {
                        $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                        $format = strtolower($ext);
                    }

                    // Get file name
                    $fileName = $doc->file_path ? basename($doc->file_path) : null;

                    // Get jenis from dokumen or template
                    $jenis = $doc->dokumen?->jenis ?? $doc->template?->jenis;

                    return [
                        'id' => $doc->id,
                        'template_id' => $doc->template_id,
                        'dokumen_id' => $doc->dokumen_id,
                        'user_id' => $doc->user_id,
                        'file_path' => $doc->file_path,
                        'file_name' => $fileName,
                        'format' => $format,
                        'generated_at' => $doc->generated_at,
                        'created_at' => $doc->created_at,
                        'updated_at' => $doc->updated_at,
                        'template' => $doc->template ? [
                            'id' => $doc->template->id,
                            'nama' => $doc->template->nama,
                            'kode' => $doc->template->kode,
                            'format_output' => $doc->template->format_output,
                        ] : null,
                        'dokumen' => $doc->dokumen ? [
                            'id' => $doc->dokumen->id,
                            'nomor_dokumen' => $doc->dokumen->nomor_dokumen,
                        ] : null,
                        'jenis' => $jenis ? [
                            'id' => $jenis->id,
                            'nama' => $jenis->nama,
                            'kode' => $jenis->kode,
                        ] : null,
                        'user' => $doc->user ? [
                            'id' => $doc->user->id,
                            'nama' => $doc->user->nama_lengkap ?? $doc->user->name,
                        ] : null,
                    ];
                });

            return response()->json($documents);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memuat riwayat dokumen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific generated document
     */
    public function show($id)
    {
        try {
            $userId = Auth::id();

            $document = TemplateGenerated::with([
                'template.jenis',
                'dokumen.jenis',
                'user'
            ])
                ->where('id', $id)
                ->where('user_id', $userId) // Ensure user can only view their own documents
                ->firstOrFail();

            // Determine format from file path
            $format = 'unknown';
            if ($document->file_path) {
                $ext = pathinfo($document->file_path, PATHINFO_EXTENSION);
                $format = strtolower($ext);
            }

            // Get file name
            $fileName = $document->file_path ? basename($document->file_path) : null;

            // Get jenis from dokumen or template
            $jenis = $document->dokumen?->jenis ?? $document->template?->jenis;

            return response()->json([
                'id' => $document->id,
                'template_id' => $document->template_id,
                'dokumen_id' => $document->dokumen_id,
                'user_id' => $document->user_id,
                'file_path' => $document->file_path,
                'file_name' => $fileName,
                'format' => $format,
                'data_variables' => $document->data_variables,
                'generated_at' => $document->generated_at,
                'created_at' => $document->created_at,
                'updated_at' => $document->updated_at,
                'template' => $document->template ? [
                    'id' => $document->template->id,
                    'nama' => $document->template->nama,
                    'kode' => $document->template->kode,
                    'format_output' => $document->template->format_output,
                ] : null,
                'dokumen' => $document->dokumen ? [
                    'id' => $document->dokumen->id,
                    'nomor_dokumen' => $document->dokumen->nomor_dokumen,
                ] : null,
                'jenis' => $jenis ? [
                    'id' => $jenis->id,
                    'nama' => $jenis->nama,
                    'kode' => $jenis->kode,
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Dokumen tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Delete generated document
     */
    public function destroy($id)
    {
        try {
            $userId = Auth::id();

            $document = TemplateGenerated::where('id', $id)
                ->where('user_id', $userId) // Ensure user can only delete their own documents
                ->firstOrFail();

            // Delete file from storage
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete database record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus dokumen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download generated document
     */
    public function download($id)
    {
        try {
            $userId = Auth::id();

            $document = TemplateGenerated::where('id', $id)
                ->where('user_id', $userId)
                ->firstOrFail();

            if (!$document->file_path || !Storage::disk('public')->exists($document->file_path)) {
                return response()->json([
                    'message' => 'File tidak ditemukan'
                ], 404);
            }

            $fileName = $document->file_path ? basename($document->file_path) : 'document';
            $filePath = storage_path('app/public/' . $document->file_path);

            return response()->download($filePath, $fileName);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengunduh dokumen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get document statistics for user
     */
    public function getStatistics()
    {
        try {
            $userId = Auth::id();

            $total = TemplateGenerated::where('user_id', $userId)->count();

            $today = TemplateGenerated::where('user_id', $userId)
                ->whereDate('generated_at', today())
                ->count();

            $thisWeek = TemplateGenerated::where('user_id', $userId)
                ->whereBetween('generated_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            $thisMonth = TemplateGenerated::where('user_id', $userId)
                ->whereMonth('generated_at', now()->month)
                ->whereYear('generated_at', now()->year)
                ->count();

            $byFormat = TemplateGenerated::where('user_id', $userId)
                ->get()
                ->groupBy(function ($doc) {
                    if ($doc->file_path) {
                        return strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION));
                    }
                    return 'unknown';
                })
                ->map(function ($group) {
                    return $group->count();
                });

            return response()->json([
                'total' => $total,
                'today' => $today,
                'this_week' => $thisWeek,
                'this_month' => $thisMonth,
                'by_format' => $byFormat,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal memuat statistik',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
