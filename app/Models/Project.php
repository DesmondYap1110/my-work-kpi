<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'added_date',
        'start_date',
        'end_date',
        'team_id',
        'assigned_date',
        'status',
        'complete_date',
    ];

    protected $casts = [
        'added_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'assigned_date' => 'date',
        'complete_date' => 'datetime',
        'status' => ProjectStatus::class,
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class, 'project_id', 'id');
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
     * Seed a blank project_kpi row for every (active staff on this
     * project's team) x (objective on that staff's position's KPI)
     * combination, ready for the staff to self-add their mark later.
     *
     * The unique index on project_kpi plus firstOrCreate() is the
     * duplicate-guard the legacy cascade lacked, so calling this more
     * than once for the same project is safe and a no-op the 2nd time.
     */
    public function seedKpiEntriesForCompletion(): void
    {
        DB::transaction(function () {
            $activeStaff = $this->team->staff()->active()->with('position.objectives.infos')->get();

            foreach ($activeStaff as $staff) {
                $position = $staff->position;

                if (! $position || ! $position->has_kpi) {
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
}
