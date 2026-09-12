<?php

namespace App\Models;

use App\Enums\PhaseApprovalStatus;
use App\Enums\PhaseProgressStatus;
use App\Enums\PhaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPhase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_phase';

    protected $fillable = [
        'project_id',
        'title',
        'type',
        'tag_id',
        'start_date',
        'due_date',
        'remark_file',
        'invoice_file',
        'approval_status',
        'progress_status',
        'submitted_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'submitted_date' => 'datetime',
        'type' => PhaseType::class,
        'approval_status' => PhaseApprovalStatus::class,
        'progress_status' => PhaseProgressStatus::class,
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(ProjectTag::class, 'tag_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectPhaseFile::class, 'phase_id', 'id');
    }
}
