<?php

namespace Modules\TerminalData\Classes\Repositories;

use Modules\TerminalData\Models\TdFolder;
use Illuminate\Database\Eloquent\Collection;

class TdFolderRepository
{
    /**
     * Create folder
     */
    public function create(array $data): TdFolder
    {
        return TdFolder::create($data);
    }
    
    /**
     * Update folder
     */
    public function update(TdFolder $folder, array $data): TdFolder
    {
        $folder->update($data);
        return $folder;
    }
    
    /**
     * Delete folder
     */
    public function delete(TdFolder $folder): bool
    {
        return $folder->delete();
    }
    
    /**
     * Find folder by ID
     */
    public function find(string $id): ?TdFolder
    {
        return TdFolder::find($id);
    }
    
    /**
     * Get folder tree structure
     */
    public function getTree(?string $parentId = null, int $depth = 3, int $currentDepth = 0): array
    {
        if ($currentDepth >= $depth) {
            return [];
        }
        
        $folders = TdFolder::where('parent_id', $parentId)
            ->with(['creator', 'bidang'])
            ->orderBy('name')
            ->get();
        
        return $folders->map(function ($folder) use ($depth, $currentDepth) {
            return [
                'id' => $folder->id,
                'name' => $folder->name,
                'path' => $folder->path,
                'level' => $folder->level,
                'color' => $folder->color,
                'icon' => $folder->icon,
                'total_files' => $folder->total_files,
                'total_subfolders' => $folder->total_subfolders,
                'is_public' => $folder->is_public,
                'is_locked' => $folder->is_locked,
                'children' => $this->getTree($folder->id, $depth, $currentDepth + 1)
            ];
        })->toArray();
    }
    
    /**
     * Search folders
     */
    public function search(string $query, array $filters = [])
    {
        $queryBuilder = TdFolder::query()
            ->with(['creator', 'bidang', 'tags'])
            ->search($query);
        
        if (isset($filters['bidang_id'])) {
            $queryBuilder->where('bidang_id', $filters['bidang_id']);
        }
        
        if (isset($filters['created_by'])) {
            $queryBuilder->where('created_by', $filters['created_by']);
        }
        
        if (isset($filters['is_public'])) {
            $queryBuilder->where('is_public', $filters['is_public']);
        }
        
        return $queryBuilder->get();
    }
    
    /**
     * Get shared folders for user
     */
    public function getSharedFolders($userId): Collection
    {
        return TdFolder::whereHas('shares', function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->where(function($q) {
                  $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
              });
        })->with(['creator', 'bidang'])->get();
    }
    
    /**
     * Get root folders
     */
    public function getRootFolders(): Collection
    {
        return TdFolder::roots()
            ->with(['creator', 'bidang'])
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get starred folders for user
     */
    public function getStarredFolders($userId): Collection
    {
        return TdFolder::where('created_by', $userId)
            ->starred()
            ->with(['creator', 'bidang'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}
