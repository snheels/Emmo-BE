<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mood extends Model
{
    //
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'mood',
        'date'
    ];

}
