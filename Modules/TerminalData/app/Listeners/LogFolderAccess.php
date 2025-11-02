<?php

namespace Modules\TerminalData\Listeners;

use Modules\TerminalData\Events\FolderAccessed;
use Illuminate\Support\Facades\Log;

class LogFolderAccess
{
    /**
     * Handle the event.
     */
    public function handle(FolderAccessed $event): void
    {
        $context = [
            'user_id' => $event->user->id,
            'user_name' => $event->user->nama,
            'action' => $event->action,
            'timestamp' => $event->timestamp,
        ];

        if ($event->folder) {
            $context['folder_id'] = $event->folder->id;
            $context['folder_name'] = $event->folder->name;
            $context['folder_path'] = $event->folder->path;
        }

        Log::channel('daily')->info('Folder accessed', $context);

        // Bisa tambahkan logic lain seperti:
        // - Simpan ke activity log table
        // - Trigger notification
        // - Update last accessed timestamp
        // - Increment view counter, dll
    }
}
