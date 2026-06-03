<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Task extends Model
{
    //
    use SoftDeletes;
    protected $fillable = [
        'project_id',
        'assignee_id',
        'name',
        'notes',
        'priority',
        'status',
        'review_status',
        'due_date',
        'completed_at',
        'revision_notes'
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime'
    ];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function assignee(){
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function attachments(){
        return $this->hasMany(Attachment::class);
    }
}
