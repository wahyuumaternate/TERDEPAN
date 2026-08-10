<?php

namespace Modules\TerminalData\Services;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Modules\TerminalData\Classes\Services\TdActivityService;
use Modules\TerminalData\Events\FolderAccessed;
use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Repositories\TdFolderRepository;

class TdFolderService
{
    public function __construct(
        protected TdFolderRepository $repository,
        protected TdActivityService $activityService
    ) {}

    /**
     * Get folders with authorization check
     */
    public function getFolders(array $filters, $user): Collection
    {
        // Check authorization
        if (! Gate::forUser($user)->allows('viewAny', TdFolder::class)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk melihat folder.');
        }

        // Get folders from repository
        $folders = $this->repository->getFolders($filters);

        // Filter folders based on user access
        $folders = $folders->filter(function ($folder) use ($user) {
            return Gate::forUser($user)->allows('view', $folder);
        });

        return $folders;
    }

    /**
     * Get root folders (bidang level) with stats
     */
    public function getRootFolders($user): Collection
    {
        // Check authorization
        if (! Gate::forUser($user)->allows('viewAny', TdFolder::class)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk melihat folder.');
        }

        // Get root folders
        $folders = $this->repository->getRootFolders();

        // Filter berdasarkan akses user dan tambahkan stats
        $folders = $folders->filter(function ($folder) use ($user) {
            return Gate::forUser($user)->allows('view', $folder);
        })->map(function ($folder) {
            // Hitung subfolder count
            $folder->subfolders_count = $folder->subfolders()->count();

            return $folder;
        });

        // Fire event untuk logging
        event(new FolderAccessed($user, 'list_root_folders'));

        return $folders;
    }

    /**
     * Get folder by ID
     */
    public function getFolderById(string $id, $user): ?TdFolder
    {
        $folder = $this->repository->findById($id);

        if (! $folder) {
            return null;
        }

        // Check authorization
        if (! Gate::forUser($user)->allows('view', $folder)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk melihat folder ini.');
        }

        // Fire event
        event(new FolderAccessed($user, 'view_folder', $folder));

        return $folder;
    }

    /**
     * Create new folder
     */
    public function createFolder(array $data, $user): TdFolder
    {
        // Check authorization
        if (! Gate::forUser($user)->allows('create', TdFolder::class)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk membuat folder.');
        }

        // Set creator
        $data['created_by'] = $user->id;

        // If no bidang_id provided, use user's bidang
        if (! isset($data['bidang_id']) && $user->profile?->bidang_id) {
            $data['bidang_id'] = $user->profile?->bidang_id;
        }

        // Create folder
        $folder = $this->repository->create($data);

        // Fire event
        event(new FolderAccessed($user, 'create_folder', $folder));

        $this->activityService->log($folder, 'created', $user, "membuat folder \"{$folder->name}\"");

        return $folder;
    }

    /**
     * Update folder
     */
    public function updateFolder(TdFolder $folder, array $data, $user): TdFolder
    {
        // Check authorization
        if (! Gate::forUser($user)->allows('update', $folder)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk mengubah folder ini.');
        }

        // Set updater
        $data['updated_by'] = $user->id;
        $oldName = $folder->name;

        // Update folder
        $this->repository->update($folder, $data);

        // Refresh model
        $folder->refresh();

        // Fire event
        event(new FolderAccessed($user, 'update_folder', $folder));

        $this->activityService->log($folder, 'renamed', $user, "mengubah folder \"{$oldName}\" menjadi \"{$folder->name}\"", [
            'old_name' => $oldName,
            'new_name' => $folder->name,
        ]);

        return $folder;
    }

    /**
     * Delete folder
     */
    public function deleteFolder(TdFolder $folder, $user): bool
    {
        // Check authorization
        if (! Gate::forUser($user)->allows('delete', $folder)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk menghapus folder ini.');
        }

        // Fire event before deletion
        event(new FolderAccessed($user, 'delete_folder', $folder));

        $this->activityService->log($folder, 'trashed', $user, "memindahkan folder \"{$folder->name}\" ke sampah");

        // Delete folder
        return $this->repository->delete($folder);
    }

    /**
     * Move folder
     */
    public function moveFolder(TdFolder $folder, ?TdFolder $newParent, $user): TdFolder
    {
        // Check authorization for source folder
        if (! Gate::forUser($user)->allows('update', $folder)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk memindahkan folder ini.');
        }

        // Check authorization for new parent folder
        if ($newParent && ! Gate::forUser($user)->allows('update', $newParent)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk menempatkan folder di dalam folder tujuan.');
        }

        // Update parent_id
        $oldParentId = $folder->parent_id;
        $folder->parent_id = $newParent ? $newParent->id : null;
        $folder->updated_by = $user->id;
        $folder->save();

        // Fire event
        event(new FolderAccessed($user, 'move_folder', $folder));

        $this->activityService->log($folder, 'moved', $user, "memindahkan folder \"{$folder->name}\"".($newParent ? " ke \"{$newParent->name}\"" : ' ke root'), [
            'old_parent_id' => $oldParentId,
            'new_parent_id' => $folder->parent_id,
        ]);

        return $folder;
    }

    /**
     * Get folders by bidang
     */
    public function getFoldersByBidang(int $bidangId, $user, ?int $level = null): Collection
    {
        // Check authorization
        if (! Gate::forUser($user)->allows('viewAny', TdFolder::class)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk melihat folder.');
        }

        $folders = $this->repository->getByBidang($bidangId, $level);

        // Filter by access
        return $folders->filter(function ($folder) use ($user) {
            return Gate::forUser($user)->allows('view', $folder);
        });
    }

    /**
     * Get folder statistics
     */
    public function getStatistics($user): array
    {
        // Check authorization
        if (! Gate::forUser($user)->allows('viewAny', TdFolder::class)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk melihat statistik.');
        }

        return $this->repository->getStatistics();
    }

    /**
     * Toggle star folder
     */
    public function toggleStar(TdFolder $folder, $user): TdFolder
    {
        // Check if user can view the folder
        if (! Gate::forUser($user)->allows('view', $folder)) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk folder ini.');
        }

        $folder->is_starred = ! $folder->is_starred;
        $folder->save();

        // Fire event
        event(new FolderAccessed($user, 'toggle_star', $folder));

        $this->activityService->log($folder, $folder->is_starred ? 'starred' : 'unstarred', $user, $folder->is_starred
            ? "menandai folder \"{$folder->name}\" sebagai favorit"
            : "membatalkan tanda favorit folder \"{$folder->name}\"");

        return $folder;
    }
}
