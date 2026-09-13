<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * What a final percentage means - "80-89 Very Good, Pass".
 *
 * The outcome is the consequential half: it is the word that decides whether a
 * probation is confirmed, extended or ended, so it is stored beside the label
 * rather than being read off the label by eye.
 */
class AssessmentBand extends Model
{
    use HasFactory;

    protected $table = 'assessment_band';

    protected $fillable = [
        'template_id',
        'min_score',
        'max_score',
        'label',
        'outcome',
    ];

    protected $casts = [
        'min_score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AssessmentTemplate::class, 'template_id', 'id');
    }

    public function range(): string
    {
        return $this->trim($this->min_score).' - '.$this->trim($this->max_score);
    }

    private function trim($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2), '0'), '.');
    }
}
