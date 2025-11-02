<?php

namespace Modules\TerminalData\Repositories;

use Modules\TerminalData\Models\TdFolder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TdFolderRepository
{
    /**
     * Get folders with filters
     */
    public function getFolders(array $filters = []): Collection
    {
        $query = TdFolder::with(['parent', 'bidang', 'creator', 'updater']);

        // Filter by level
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Filter by bidang
        if (isset($filters['bidang_id'])) {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        // Filter by starred
        if (isset($filters['is_starred'])) {
            $query->where('is_starred', $filters['is_starred']);
        }

        // Filter by public
        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        // Search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->get();
    }

    /**
     * Get paginated folders
     */
    public function getPaginatedFolders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TdFolder::with(['parent', 'bidang', 'creator', 'updater']);

        // Apply same filters as getFolders
        if (isset($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        if (isset($filters['bidang_id'])) {
            $query->where('bidang_id', $filters['bidang_id']);
        }

        if (isset($filters['is_starred'])) {
            $query->where('is_starred', $filters['is_starred']);
        }

        if (isset($filters['is_public'])) {
            $query->where('is_public', $filters['is_public']);
        }

        if (isset($filters['search']) && !empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get root folders (level 0 / bidang folders)
     */
    public function getRootFolders(): Collection
    {
        return TdFolder::with(['bidang', 'creator'])
            ->roots()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get folder by ID with relationships
     */
    public function findById(string $id): ?TdFolder
    {
        return TdFolder::with(['parent', 'bidang', 'creator', 'updater', 'subfolders'])
            ->find($id);
    }

    /**
     * Get folders by bidang
     */
    public function getByBidang(int $bidangId, int $level = null): Collection
    {
        $query = TdFolder::with(['parent', 'creator'])
            ->where('bidang_id', $bidangId);

        if ($level !== null) {
            $query->where('level', $level);
        }

        return $query->orderBy('level')->orderBy('name')->get();
    }

    /**
     * Get user's folders
     */
    public function getUserFolders(int $userId): Collection
    {
        return TdFolder::with(['bidang', 'parent'])
            ->where('created_by', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get starred folders for user
     */
    public function getStarredFolders(int $userId): Collection
    {
        return TdFolder::with(['bidang', 'parent', 'creator'])
            ->where('is_starred', true)
            ->where(function ($query) use ($userId) {
                $query->where('created_by', $userId)
                    ->orWhere('is_public', true);
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * Create new folder
     */
    public function create(array $data): TdFolder
    {
        return TdFolder::create($data);
    }

    /**
     * Update folder
     */
    public function update(TdFolder $folder, array $data): bool
    {
        return $folder->update($data);
    }

    /**
     * Delete folder
     */
    public function delete(TdFolder $folder): bool
    {
        return $folder->delete();
    }

    /**
     * Get folder statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_folders' => TdFolder::count(),
            'root_folders' => TdFolder::roots()->count(),
            'system_folders' => TdFolder::where('is_system', true)->count(),
            'public_folders' => TdFolder::where('is_public', true)->count(),
            'total_files' => TdFolder::sum('total_files'),
            'total_size' => TdFolder::sum('total_size'),
        ];
    }
}
