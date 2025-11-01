<?php

namespace Modules\TerminalData\Classes\Services;

use Modules\TerminalData\Models\TdFolder;
use Modules\TerminalData\Repositories\TdFolderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class TdFolderService
{
    protected $folderRepository;
    protected $activityService;
    
    public function __construct(
        TdFolderRepository $folderRepository,
        TdActivityService $activityService
    ) {
        $this->folderRepository = $folderRepository;
        $this->activityService = $activityService;
    }
    
    /**
     * Create new folder
     */
    public function create(array $data): TdFolder
    {
        DB::beginTransaction();
        
        try {
            // Set creator
            $data['created_by'] = auth()->id();
            
            // Create folder
            $folder = $this->folderRepository->create($data);
            
            // Update parent stats if exists
            if ($folder->parent) {
                $folder->parent->updateStats();
            }
            
            // Log activity
            $this->activityService->log($folder, 'create', 'Folder dibuat');
            
            DB::commit();
            
            return $folder->fresh(['creator', 'bidang']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Update folder
     */
    public function update(TdFolder $folder, array $data): TdFolder
    {
        DB::beginTransaction();
        
        try {
            // Check if locked
            if ($folder->is_locked && !auth()->user()->hasRole('admin')) {
                throw new \Exception('Folder terkunci dan tidak bisa diubah');
            }
            
            // Set updater
            $data['updated_by'] = auth()->id();
            
            // Store old values for activity log
            $oldValues = $folder->only(['name', 'description', 'parent_id']);
            
            // Update folder
            $folder = $this->folderRepository->update($folder, $data);
            
            // Log activity
            $this->activityService->log($folder, 'edit', 'Folder diupdate', [
                'old' => $oldValues,
                'new' => $folder->only(['name', 'description', 'parent_id'])
            ]);
            
            DB::commit();
            
            return $folder->fresh(['creator', 'bidang']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Delete folder
     */
    public function delete(TdFolder $folder): bool
    {
        DB::beginTransaction();
        
        try {
            // Check if system folder
            if ($folder->is_system) {
                throw new \Exception('System folder tidak bisa dihapus');
            }
            
            // Check if locked
            if ($folder->is_locked && !auth()->user()->hasRole('admin')) {
                throw new \Exception('Folder terkunci dan tidak bisa dihapus');
            }
            
            // Log activity before delete
            $this->activityService->log($folder, 'delete', 'Folder dihapus');
            
            // Delete folder (will cascade to subfolders and files)
            $result = $this->folderRepository->delete($folder);
            
            DB::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Move folder to new parent
     */
    public function move(TdFolder $folder, ?string $newParentId): TdFolder
    {
        DB::beginTransaction();
        
        try {
            $oldParent = $folder->parent;
            
            // Move folder
            $folder->move($newParentId);
            
            // Update old parent stats
            if ($oldParent) {
                $oldParent->updateStats();
            }
            
            // Update new parent stats
            if ($folder->parent) {
                $folder->parent->updateStats();
            }
            
            // Log activity
            $this->activityService->log($folder, 'move', 'Folder dipindahkan', [
                'from' => $oldParent?->name ?? 'Root',
                'to' => $folder->parent?->name ?? 'Root'
            ]);
            
            DB::commit();
            
            return $folder->fresh(['creator', 'bidang', 'parent']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error moving folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Copy folder to new parent
     */
    public function copy(TdFolder $folder, ?string $newParentId, string $newName = null): TdFolder
    {
        DB::beginTransaction();
        
        try {
            $data = $folder->toArray();
            $data['parent_id'] = $newParentId;
            $data['name'] = $newName ?? $folder->name . ' (Copy)';
            $data['created_by'] = auth()->id();
            
            // Remove stats and dates
            unset($data['id'], $data['total_files'], $data['total_subfolders'], 
                  $data['total_size'], $data['created_at'], $data['updated_at']);
            
            $newFolder = $this->create($data);
            
            // Copy subfolders recursively
            foreach ($folder->subfolders as $subfolder) {
                $this->copy($subfolder, $newFolder->id);
            }
            
            // Note: Files should be copied separately with actual file duplication
            
            DB::commit();
            
            return $newFolder;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error copying folder: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get folder tree structure
     */
    public function getTree(?string $parentId = null, int $depth = 3): array
    {
        return $this->folderRepository->getTree($parentId, $depth);
    }
    
    /**
     * Search folders
     */
    public function search(string $query, array $filters = [])
    {
        return $this->folderRepository->search($query, $filters);
    }
    
    /**
     * Get shared folders for user
     */
    public function getSharedFolders($userId)
    {
        return $this->folderRepository->getSharedFolders($userId);
    }
    
    /**
     * Create system folder (e.g., for each bidang)
     */
    public function createSystemFolder(array $data): TdFolder
    {
        $data['is_system'] = true;
        $data['is_locked'] = true;
        $data['created_by'] = $data['created_by'] ?? 1; // System user
        
        return $this->create($data);
    }
}
