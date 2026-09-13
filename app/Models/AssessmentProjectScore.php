<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row of Part 2: a piece of work being judged.
 *
 * Rows are generated from the member's own tasks in the review period, so the
 * appraiser rates what the system already knows they did rather than
 * remembering it. description carries the line as it read when the appraisal
 * was generated, so a project later renamed or deleted does not rewrite it.
 */
class AssessmentProjectScore extends Model
{
    use HasFactory;

    protected $table = 'assessment_project_score';

    protected $fillable = [
        'assessment_id',
        'project_id',
        'description',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
    }

    /**
     * @see AssessmentScore::score()
     */
    public function score(): ?int
    {
        return $this->reviewer_score ?? $this->employee_score;
    }
}
