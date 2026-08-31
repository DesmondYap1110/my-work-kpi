<?php

namespace App\Models;

use App\Enums\ProjectKpiStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectKpi extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'project_kpi';

    protected $primaryKey = 'kpiproject_id';

    protected $fillable = [
        'staff_id',
        'project_id',
        'kpi_id',
        'kojbInfo_id',
        'mark',
        'status',
        'createddate',
    ];

    protected $casts = [
        'status' => ProjectKpiStatus::class,
        'createddate' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(Kpi::class, 'kpi_id', 'kpi_id');
    }

    public function objectiveInfo(): BelongsTo
    {
        return $this->belongsTo(KpiObjectiveInfo::class, 'kojbInfo_id', 'kojbInfo_id');
    }

    public function scopePending($query)
    {
        return $query->whereNull('status')->whereNotNull('createddate');
    }

    /**
     * The KPI objective (with its legal marks) that this entry's
     * kojbInfo_id resolves to for this entry's KPI - project_kpi only
     * stores the catalog id, not a direct FK to kpi_objective.
     */
    public function objective(): ?KpiObjective
    {
        return KpiObjective::where('kpi_ID', $this->kpi_id)
            ->where('kojbInfo_id', $this->kojbInfo_id)
            ->with('mark')
            ->first();
    }

    /**
     * Marks legally allowed for this entry's objective, excluding the mark
     * currently submitted - used to populate the reject-with-override UI.
     *
     * @return array<int, int>
     */
    public function allowedMarksExcludingCurrent(): array
    {
        $marks = $this->objective()?->mark?->allowedMarks() ?? [];

        return array_values(array_diff($marks, [$this->mark]));
    }
}
