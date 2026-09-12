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

    public function scopeForPosition($query, int $positionId)
    {
        return $query->where('position_id', $positionId);
    }
}
