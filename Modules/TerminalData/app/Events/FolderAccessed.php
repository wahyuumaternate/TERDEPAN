<?php

namespace Modules\TerminalData\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\TerminalData\Models\TdFolder;

class FolderAccessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $action;
    public $folder;
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct($user, string $action, ?TdFolder $folder = null)
    {
        $this->user = $user;
        $this->action = $action;
        $this->folder = $folder;
        $this->timestamp = now();
    }
}
