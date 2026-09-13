<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One point on the rating scale - 5 "Outstanding", and what earning it means.
 *
 * Kept as rows rather than a hard-coded 1-5 because the wording is the part of
 * an appraisal form companies argue over, and it has to be theirs.
 */
class AssessmentRating extends Model
{
    use HasFactory;

    protected $table = 'assessment_rating';

    protected $fillable = [
        'template_id',
        'value',
        'label',
        'description',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'template_id', 'id');
    }
}
