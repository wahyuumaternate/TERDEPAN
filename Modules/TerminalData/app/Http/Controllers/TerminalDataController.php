<?php

namespace Modules\TerminalData\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Modules\TerminalData\Http\Requests\GetFoldersRequest;
use Modules\TerminalData\Http\Resources\TdFolderResource;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Services\TdFolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class TerminalDataController extends Controller
{
    use AuthorizesRequests;
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

            // Authorize viewAny folders
            $this->authorize('viewAny', TdFolder::class);

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

    public function folderDetail($folderId)
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = request()->user();

            // Get folder by ID using service
            $folder = $this->folderService->getFolderById($folderId, $user);

            if (!$folder) {
                abort(404, 'Folder tidak ditemukan');
            }

            // Authorize view
            $this->authorize('view', $folder);

            // Get subfolders and files with permission filtering
            $subfolders = $folder->subfolders()
                ->forUser($user)
                ->with(['creator', 'bidang'])
                ->get();
            $files = $folder->files()
                ->forUser($user)
                ->with(['creator'])
                ->get();

            // Add permissions to files
            $files = $files->map(function ($file) use ($user) {
                $file->permissions = [
                    'view' => $user->can('view', $file),
                    'update' => $user->can('update', $file),
                    'delete' => $user->can('delete', $file),
                    'download' => $user->can('download', $file),
                ];
                return $file;
            });

            return view('terminaldata::folder.detail', compact('folder', 'subfolders', 'files'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, $e->getMessage());
        } catch (\Exception $e) {
            abort(500, 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get children folders of a folder (AJAX)
     */
    public function getFolderChildren($folderId)
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = request()->user();

            // Get folder by ID using service
            $folder = $this->folderService->getFolderById($folderId, $user);

            if (!$folder) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder tidak ditemukan'
                ], 404);
            }

            // Authorize view
            $this->authorize('view', $folder);

            // Get subfolders with permission filtering
            $subfolders = $folder->subfolders()
                ->forUser($user)
                ->with(['creator', 'bidang'])
                ->get();

            return response()->json(TdFolderResource::collection($subfolders));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function evidenIndex()
    {
        //
    }

    public function sampahIndex()
    {
        try {
            /** @var \App\Models\MasterPegawai $user */
            $user = request()->user();

            // Get trashed folders and files with permission filtering
            $trashedFolders = \Modules\TerminalData\Models\TdFolder::onlyTrashed()
                ->deletableBy($user)
                ->with(['creator', 'bidang', 'parent'])
                ->orderBy('deleted_at', 'desc')
                ->get();

            $trashedFiles = \Modules\TerminalData\Models\TdFile::onlyTrashed()
                ->deletableBy($user)
                ->with(['creator', 'folder'])
                ->orderBy('deleted_at', 'desc')
                ->get();

            return view('terminaldata::sampah.index', compact('trashedFolders', 'trashedFiles'));
        } catch (\Exception $e) {
            abort(500, 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
