<?php

namespace Modules\TerminalData\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\TerminalData\Models\TdFile;
use Modules\TerminalData\Models\TdFolder;
use Exception;

class ProcessFileUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $folderId,
        public string $originalName,
        public string $tempPath,
        public int $fileSize,
        public string $mimeType,
        public int $userId,
        public string $uploadId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get folder
            $folder = TdFolder::find($this->folderId);
            if (!$folder) {
                throw new Exception('Folder tidak ditemukan');
            }

            // Generate unique filename
            $extension = pathinfo($this->originalName, PATHINFO_EXTENSION);
            $filename = Str::slug(pathinfo($this->originalName, PATHINFO_FILENAME)) . '_' . time() . '.' . $extension;

            // Define storage path
            $storagePath = "terminal-data/{$folder->bidang_id}/{$folder->id}";

            // Move file from temp to permanent storage
            $fullPath = $storagePath . '/' . $filename;
            Storage::put($fullPath, file_get_contents($this->tempPath));

            // Delete temp file
            if (file_exists($this->tempPath)) {
                unlink($this->tempPath);
            }

            // Create file record
            $file = TdFile::create([
                'folder_id' => $this->folderId,
                'name' => $this->originalName,
                'filename' => $filename,
                'path' => $fullPath,
                'extension' => $extension,
                'mime_type' => $this->mimeType,
                'size' => $this->fileSize,
                'size_kb' => round($this->fileSize / 1024, 2),
                'is_current' => true,
                'version' => 1,
                'uploaded_by' => $this->userId,
                'created_by' => $this->userId,
            ]);

            // Update folder stats
            $folder->updateStats();

            // Broadcast success event (optional - for real-time updates)
            // event(new \Modules\TerminalData\Events\FileUploadCompleted(
            //     $this->uploadId,
            //     $file,
            //     'success'
            // ));
        } catch (Exception $e) {
            // Broadcast error event (optional - for real-time updates)
            // event(new \Modules\TerminalData\Events\FileUploadCompleted(
            //     $this->uploadId,
            //     null,
            //     'error',
            //     $e->getMessage()
            // ));

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        // Broadcast error event (optional - for real-time updates)
        // event(new \Modules\TerminalData\Events\FileUploadCompleted(
        //     $this->uploadId,
        //     null,
        //     'failed',
        //     $exception->getMessage()
        // ));

        // Clean up temp file
        if (file_exists($this->tempPath)) {
            unlink($this->tempPath);
        }
    }
}
