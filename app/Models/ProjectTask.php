<?php

namespace App\Models;

use App\Enums\PhaseApprovalStatus;
use App\Enums\PhaseType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A unit of work on a project.
 *
 * One table covers what ClickUp calls tasks, subtasks and milestones: a
 * subtask is a task with a parent, and a milestone is a task with the flag
 * set. That keeps a single set of rules for status, assignment and scoring
 * rather than three near-identical tables.
 *
 * Finishing a task earns its tag's points for its assignee - see
 * App\Services\ProjectDeliveryScoreService.
 */
class ProjectTask extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_task';

    protected $fillable = [
        'project_id',
        'parent_id',
        'assignee_id',
        'title',
        'is_milestone',
        'status',
        'priority',
        'progress',
        'type',
        'tag_id',
        'start_date',
        'due_date',
        'remark_file',
        'invoice_file',
        'approval_status',
        'submitted_date',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'is_milestone' => 'boolean',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
        'type' => PhaseType::class,
        'approval_status' => PhaseApprovalStatus::class,
        'start_date' => 'date',
        'due_date' => 'date',
        'submitted_date' => 'date',
        'completed_at' => 'datetime',
    ];

    /**
     * Subtasks go with their parent.
     *
     * The foreign key is ON DELETE CASCADE, but that only fires on a real
     * DELETE - a soft delete is an UPDATE, so it has to be done here or the
     * children are left live under a deleted parent.
     */
    protected static function booted(): void
    {
        static::deleting(function (ProjectTask $task) {
            if ($task->isForceDeleting()) {
                return;
            }

            $task->children()->get()->each->delete();
        });

        // completed_at is what the delivery score counts against a review
        // period, so it tracks the status rather than waiting for someone to
        // set it by hand.
        static::saving(function (ProjectTask $task) {
            if (! $task->isDirty('status')) {
                return;
            }

            if ($task->status?->isDone()) {
                $task->completed_at ??= now();
                $task->progress = 100;

                return;
            }

            // Reopened: it is no longer finished, so it no longer counts.
            $task->completed_at = null;
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assignee_id', 'id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(ProjectTag::class, 'tag_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectTaskFile::class, 'task_id', 'id');
    }

    /**
     * Top-level tasks - the ones a project page lists, with their subtasks
     * nested underneath rather than repeated alongside.
     */
    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeDone(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::Done);
    }

    public function scopeCompletedBetween(Builder $query, $from, $to): Builder
    {
        return $query->done()->whereBetween('completed_at', [$from, $to]);
    }

    /**
     * What finishing this task is worth. Untagged work scores nothing - the
     * tag is where a company says how much a kind of work counts for.
     */
    public function points(): float
    {
        return (float) ($this->tag?->points ?? 0);
    }

    public function isOverdue(): bool
    {
        return $this->due_date !== null
            && ! $this->status->isDone()
            && $this->due_date->isPast();
    }
}
