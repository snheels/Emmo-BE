<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'project_manager_id',
        'title',
        'description',
        'start_date',
        'resource_links',
    ];

    protected $casts = [
        'start_date' => 'date',
        'resource_links' => 'array',
    ];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }
}
