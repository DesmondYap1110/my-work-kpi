<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A check-in during the review period - the conversations that precede the
 * appraisal rather than the appraisal itself.
 *
 * The table is on the form and modelled here so the relation exists; there is
 * no screen for it yet.
 */
class AssessmentCheckin extends Model
{
    use HasFactory;

    protected $table = 'assessment_checkin';

    protected $fillable = [
        'assessment_id',
        'period_from',
        'period_to',
        'review_date',
        'reviewer_feedback',
        'reviewee_comments',
        'sort_order',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'review_date' => 'date',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'id');
    }
}
