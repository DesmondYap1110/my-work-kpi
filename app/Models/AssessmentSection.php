<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One weighted part of the form - "Part 1 Soft Skills, 50%".
 *
 * Two kinds, and the difference is where the rows come from:
 *
 *   rating  - the member's position supplies them, through the categories
 *             pointed at this section
 *   project - the member's own project work in the review period supplies
 *             them, so nothing is retyped
 */
class AssessmentSection extends Model
{
    use HasFactory;

    public const TYPE_RATING = 'rating';
    public const TYPE_PROJECT = 'project';

    protected $table = 'assessment_section';

    protected $fillable = [
        'template_id',
        'title',
        'type',
        'weightage',
        'calculation',
        'is_optional',
        'sort_order',
    ];

    protected $casts = [
        'weightage' => 'decimal:2',
        'is_optional' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'template_id', 'id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(KpiCategory::class, 'section_id', 'id');
    }

    public function isProject(): bool
    {
        return $this->type === self::TYPE_PROJECT;
    }

    public function weight(): float
    {
        return (float) $this->weightage;
    }
}
