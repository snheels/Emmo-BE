<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    //
    protected $fillable = [
        'task_id',
        'image'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
