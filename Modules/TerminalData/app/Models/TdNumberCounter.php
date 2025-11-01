<?php

namespace Modules\TerminalData\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\TerminalData\Database\Factories\TdNumberCounterFactory;

class TdNumberCounter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): TdNumberCounterFactory
    // {
    //     // return TdNumberCounterFactory::new();
    // }
}
