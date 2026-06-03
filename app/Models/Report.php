<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    //
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'date',
        'todays_work',
        'obstacle',
    ];

    protected $casts = [
        'todays_work' => 'array',
    ];
}
