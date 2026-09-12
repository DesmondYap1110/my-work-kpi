<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An attachment on a task. Files live on the public disk under
 * project-task-files/; this records what was uploaded, when and by whom.
 */
class ProjectTaskFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_task_files';

    protected $fillable = [
        'task_id',
        'filename',
        'uploaded_at',
        'staff_id',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id', 'id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
}
