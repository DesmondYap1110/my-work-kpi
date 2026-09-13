<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What one measurement was rated in one appraisal.
 *
 * The paper form has two columns, Employee and Reviewer, and both are kept.
 * score() decides which one counts: the reviewer's word is the assessment, and
 * the employee figure stands in only where the reviewer left the box empty.
 */
class AssessmentScore extends Model
{
    use HasFactory;

    protected $table = 'assessment_score';

    protected $fillable = [
        'assessment_id',
        'objective_info_id',
        'employee_score',
        'reviewer_score',
    ];

    protected $casts = [
        'employee_score' => 'integer',
        'reviewer_score' => 'integer',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'id');
    }

    public function objectiveInfo(): BelongsTo
    {
        return $this->belongsTo(KpiObjectiveInfo::class, 'objective_info_id', 'id');
    }

    /**
     * The figure this row contributes, or null when it was left unrated.
     *
     * Null is not zero: an unrated item is dropped from the maximum as well as
     * the total, which is what makes the form's "(if applicable)" groups work
     * without a flag - skip Leadership entirely and it counts for nothing
     * either way.
     */
    public function score(): ?int
    {
        return $this->reviewer_score ?? $this->employee_score;
    }
}
