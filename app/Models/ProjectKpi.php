<?php

namespace App\Models;

use App\Enums\ProjectKpiStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectKpi extends Model
{
    use HasFactory;

    protected $table = 'project_kpi';

    protected $fillable = [
        'staff_id',
        'project_id',
        'position_id',
        'objective_info_id',
        'mark',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'status' => ProjectKpiStatus::class,
        'submitted_at' => 'datetime',
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_id', 'id');
    }

    public function objectiveInfo(): BelongsTo
    {
        return $this->belongsTo(KpiObjectiveInfo::class, 'objective_info_id', 'id');
    }

    public function scopePending($query)
    {
        return $query->whereNull('status')->whereNotNull('submitted_at');
    }

    /**
     * Marks legally allowed for this entry's scored item, excluding the mark
     * currently submitted - used to populate the reject-with-override UI.
     *
     * The allowed values live on the item itself now, so this no longer has
     * to walk back up to the objective to find them.
     *
     * @return array<int, int>
     */
    public function allowedMarksExcludingCurrent(): array
    {
        $marks = $this->objectiveInfo?->allowedMarks() ?? [];

        return array_values(array_diff($marks, [$this->mark]));
    }
}
