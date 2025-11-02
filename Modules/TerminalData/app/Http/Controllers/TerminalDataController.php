<?php

namespace Modules\TerminalData\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\TerminalData\Http\Requests\GetFoldersRequest;
use Modules\TerminalData\Http\Resources\TdFolderResource;
use Modules\TerminalData\Services\TdFolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TerminalDataController extends Controller
{
    public function __construct(
        protected TdFolderService $folderService
    ) {}

    /**
     * Display dashboard / landing page
     */
    public function index(): View
    {
        return view('terminaldata::index');
    }

    /**
     * Display folders page with level 1 (bidang) folders
     */
    public function folderIndex(GetFoldersRequest $request): View|JsonResponse
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = $request->user();

            // Get validated filters
            $filters = $request->validated();

            // For folder index page, we want root folders (level 0 = bidang folders)
            $folders = $this->folderService->getRootFolders($user);

            // If AJAX request, return JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => TdFolderResource::collection($folders)->toArray($request),
                    'message' => 'Data folder berhasil dimuat'
                ]);
            }

            // Return view with folders
            return view('terminaldata::folder.index', compact('folders'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 403);
            }

            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            abort(500, 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function evidenIndex()
    {
        //
    }

    public function sampahIndex()
    {
        //
    }
}
