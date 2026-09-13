<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A heading that groups a position's objectives - "Soft Skill", "Service".
 *
 * Scoped to a position, so an F&B role is never offered a software role's
 * headings.
 */
class KpiCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_category';

    protected $fillable = [
        'position_id',
        'section_id',
        'name',
        'sort_order',
    ];

    /**
     * Takes its objectives - and through them, their items - down with it.
     *
     * See KpiObjective::booted() for why this cannot be left to the database
     * foreign key.
     */
    protected static function booted(): void
    {
        // A new heading is rated under the first part of the appraisal form
        // unless it says otherwise. Done here rather than in the controller so
        // that every way of adding a category - form, seeder, tinker - lands
        // somewhere an appraisal can find it, instead of silently missing from
        // the form.
        static::creating(function (KpiCategory $category) {
            if ($category->section_id === null) {
                $category->section_id = AssessmentSection::query()
                    ->where('type', AssessmentSection::TYPE_RATING)
                    ->orderBy('sort_order')
                    ->value('id');
            }
        });

        static::deleting(function (KpiCategory $category) {
            if ($category->isForceDeleting()) {
                return;
            }

            $category->objectives()->get()->each->delete();
        });
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_id', 'id');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(KpiObjective::class, 'category_id', 'id');
    }

    /**
     * Which part of the appraisal form this heading is rated under. Null means
     * free-standing, which is how categories behaved before the form existed.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(AssessmentSection::class, 'section_id', 'id');
    }

    public function scopeForPosition($query, int $positionId)
    {
        return $query->where('position_id', $positionId);
    }
}
