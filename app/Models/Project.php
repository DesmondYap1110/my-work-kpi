<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project';

    protected $fillable = [
        'title',
        'start_date',
        'end_date',
        'assigned_date',
        'status',
        'complete_date',
        'cancel_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'assigned_date' => 'date',
        'complete_date' => 'datetime',
        'cancelled_at' => 'datetime',
        'status' => ProjectStatus::class,
    ];

    /**
     * Who cancelled the project - recorded with the reason, so a cancelled
     * project can always answer "why, and says who".
     */
    public function canceller(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Staff::class, 'cancelled_by', 'id');
    }

    /**
     * The people on this project: whoever has a task on it.
     *
     * A project used to belong to a team, which made everyone on that team
     * equally "on" every one of its projects. Assignment lives on the task now,
     * so this is both truer and already recorded.
     */
    public function assignees(): Builder
    {
        return Staff::query()
            ->whereIn('id', $this->tasks()->whereNotNull('assignee_id')->select('assignee_id'));
    }

    /**
     * Whether new work may be added to this project.
     *
     * A cancelled project has been stopped on purpose; a task added to it
     * afterwards is work nobody will do, and - since delivery points come from
     * tasks - work that could still be scored. Asked by the project page, which
     * hides Add Task, and by ProjectTaskController::store(), which refuses it.
     */
    /**
     * Whether the project's own details may still be edited. A completed or
     * cancelled project is closed; its record stays as it was left. Asked by
     * the project list's Edit button and the project page's Edit Project.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [ProjectStatus::Active, ProjectStatus::InProgress], true);
    }

    public function acceptsNewTasks(): bool
    {
        return $this->status !== ProjectStatus::Cancelled;
    }

    /**
     * Status pill colour, matching the project list: 1 green, 2 red, 4 blue,
     * 5 pink.
     */
    public function statusColourId(): int
    {
        return match ($this->status) {
            ProjectStatus::Active => 5,
            ProjectStatus::Cancelled => 2,
            ProjectStatus::InProgress => 4,
            ProjectStatus::Completed => 1,
        };
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'project_id', 'id');
    }

    public function projectKpis(): HasMany
    {
        return $this->hasMany(ProjectKpi::class, 'project_id', 'id');
    }

    public function scopeStatus($query, ProjectStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSpanningRange($query, $from, $to)
    {
        return $query->where('start_date', '<=', $from)->where('end_date', '>=', $to);
    }

    /**
     * Seed a blank project_kpi row for every (staff who worked on this
     * project) x (objective on that staff's position's KPI) combination,
     * ready for the staff to self-add their mark later.
     *
     * "Worked on" means assigned a task. It used to mean "is on the team",
     * which scored everyone identically no matter who did the work - the
     * assumption per-task assignees exist to correct. A project nobody is
     * assigned to scores nobody, which is the honest answer.
     *
     * The unique index on project_kpi plus firstOrCreate() is the
     * duplicate-guard the legacy cascade lacked, so calling this more
     * than once for the same project is safe and a no-op the 2nd time.
     */
    public function seedKpiEntriesForCompletion(): void
    {
        DB::transaction(function () {
            $activeStaff = $this->staffWhoWorkedOnIt();

            foreach ($activeStaff as $staff) {
                $position = $staff->position;

                if (! $position || ! $position->hasKpi()) {
                    continue;
                }

                foreach ($position->objectives as $objective) {
                    foreach ($objective->infos as $item) {
                        ProjectKpi::firstOrCreate([
                            'staff_id' => $staff->id,
                            'project_id' => $this->id,
                            'position_id' => $position->id,
                            'objective_info_id' => $item->id,
                        ]);
                    }
                }
            }
        });
    }

    /**
     * Active staff with a task on this project.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Staff>
     */
    private function staffWhoWorkedOnIt(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->assignees()
            ->active()
            ->with('position.objectives.infos')
            ->get();
    }
}
