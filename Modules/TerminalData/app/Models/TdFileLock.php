<?php

namespace Modules\TerminalData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\TerminalData\Database\Factories\TdFileLockFactory;

class TdFileLock extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): TdFileLockFactory
    // {
    //     // return TdFileLockFactory::new();
    // }
}
