<?php

namespace Modules\PerjanjianKinerja\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\PerjanjianKinerja\Database\Factories\PkProgramFactory;

class PkProgram extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): PkProgramFactory
    // {
    //     // return PkProgramFactory::new();
    // }
}
