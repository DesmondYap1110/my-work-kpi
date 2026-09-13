<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * The appraisal form itself: its parts, its rating scale and its bands.
 *
 * One company runs one form, so in practice there is a single active template
 * and current() is how everything reaches it. The table keeps an id and an
 * is_active flag anyway, because a company that changes its form mid-year must
 * not have last year's finished appraisals rewritten under them - those point
 * at the template they were scored against.
 */
class AssessmentTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assessment_template';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bands(): HasMany
    {
        return $this->hasMany(AssessmentBand::class, 'template_id', 'id')->orderByDesc('min_score');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'template_id', 'id');
    }

    /**
     * The form in use. Created on first ask so a fresh install can open an
     * appraisal without a seeder having been run.
     */
    public static function current(): self
    {
        return static::where('is_active', true)->orderBy('id')->first()
            ?? static::create(['name' => 'Confirmation Assessment Form']);
    }

    /**
     * The band a percentage falls into.
     *
     * The highest band starting at or below the score, rather than the first
     * whose printed range contains it. The form prints whole numbers - 80-89,
     * 70-79 - but a score is rarely whole, and 79.5 belongs to "Good" rather
     * than to the gap between two rows. Reading only the lower bound means
     * there are no gaps to fall into.
     */
    public function bandFor(?float $percentage): ?AssessmentBand
    {
        if ($percentage === null) {
            return null;
        }

        return $this->bands
            ->sortByDesc(fn (AssessmentBand $band) => (float) $band->min_score)
            ->first(fn (AssessmentBand $band) => $percentage >= (float) $band->min_score);
    }
}
