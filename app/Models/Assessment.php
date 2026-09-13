<?php

namespace App\Models;

use App\Enums\AssessmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * One member's appraisal for one review period.
 *
 * The appraiser opens it, picks the period to judge, rates the form and
 * generates it; only then does the member see it. Scores live in the two child
 * tables - assessment_score for the rated measurements, assessment_project_score
 * for the project rows - and the arithmetic lives in AssessmentScoreService,
 * not here.
 */
class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assessment';

    protected $fillable = [
        'template_id',
        'staff_id',
        'position_id',
        'reviewer_id',
        'period_from',
        'period_to',
        'review_date',
        'next_assessment_date',
        'status',
        'generated_at',
        'comments',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'review_date' => 'date',
        'next_assessment_date' => 'date',
        'generated_at' => 'datetime',
        'status' => AssessmentStatus::class,
    ];

    /**
     * Scores go with the appraisal. The database foreign keys say cascade, but
     * this is a soft delete, so the rows would otherwise be left pointing at an
     * appraisal nobody can reach - see the same pattern in ProjectTask.
     */
    protected static function booted(): void
    {
        static::deleting(function (Assessment $assessment) {
            if ($assessment->isForceDeleting()) {
                return;
            }

            $assessment->scores()->delete();
        });
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'template_id', 'id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }

    /**
     * The role the member held when this was opened, not the one they hold
     * now - the form's measurements come from it.
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'reviewer_id', 'id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(AssessmentScore::class, 'assessment_id', 'id');
    }

    public function checkins(): HasMany
    {
        return $this->hasMany(AssessmentCheckin::class, 'assessment_id', 'id')->orderBy('sort_order');
    }

    public function scopeGenerated(Builder $query): Builder
    {
        return $query->where('status', AssessmentStatus::Generated);
    }

    public function scopeForStaff(Builder $query, int $staffId): Builder
    {
        return $query->where('staff_id', $staffId);
    }

    public function isGenerated(): bool
    {
        return $this->status === AssessmentStatus::Generated;
    }

    /**
     * The period as a person would say it. An appraisal with no period set is
     * possible while it is a draft, so this has something to fall back on.
     */
    public function periodLabel(): string
    {
        if (! $this->period_from || ! $this->period_to) {
            return 'Period not set';
        }

        return $this->period_from->format('d M Y').' - '.$this->period_to->format('d M Y');
    }
}
