<?php

namespace Modules\TerminalData\Classes\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Modules\TerminalData\Models\TdActivity;

class TdActivityService
{
    public function __construct() {}

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(Model $trackable, string $action, User $user, ?string $description = null, array $metadata = []): TdActivity
    {
        return $trackable->activities()->create([
            'action' => $action,
            'user_id' => $user->id,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
