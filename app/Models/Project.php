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

    protected $primaryKey = 'project_id';

    protected $fillable = [
        'p_Title',
        'p_addDate',
        'p_SDate',
        'p_EDate',
        'team_id',
        'date_assign',
        'p_status',
        'complete_date',
    ];

    protected $casts = [
        'p_addDate' => 'date',
        'p_SDate' => 'date',
        'p_EDate' => 'date',
        'date_assign' => 'date',
        'complete_date' => 'datetime',
        'p_status' => ProjectStatus::class,
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class, 'p_ID', 'project_id');
    }

    public function projectKpis(): HasMany
    {
        return $this->hasMany(ProjectKpi::class, 'project_id', 'project_id');
    }

    public function scopeStatus($query, ProjectStatus $status)
    {
        return $query->where('p_status', $status);
    }

    public function scopeSpanningRange($query, $from, $to)
    {
        return $query->where('p_SDate', '<=', $from)->where('p_EDate', '>=', $to);
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
            $activeStaff = $this->team->staff()->active()->with('position.kpi.objectives')->get();

            foreach ($activeStaff as $staff) {
                $kpi = $staff->position?->kpi;

                if (! $kpi) {
                    continue;
                }

                foreach ($kpi->objectives as $objective) {
                    ProjectKpi::firstOrCreate([
                        'staff_id' => $staff->staff_id,
                        'project_id' => $this->project_id,
                        'kpi_id' => $kpi->kpi_id,
                        'kojbInfo_id' => $objective->kojbInfo_id,
                    ]);
                }
            }
        });
    }
}
