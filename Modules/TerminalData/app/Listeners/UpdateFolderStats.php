<?php

namespace Modules\TerminalData\Listeners;

use Modules\TerminalData\app\Events\TdFileUploaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateFolderStats
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(TdFileUploaded $event): void {}
}
