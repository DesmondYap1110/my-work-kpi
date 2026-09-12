<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A named group of scored items under a position, optionally filed under a
 * category. The items themselves - with their own marks and Standard/Extra
 * type - are KpiObjectiveInfo rows hanging off this one.
 */
class KpiObjective extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_objective';

    protected $fillable = [
        'position_id',
        'category_id',
        'title',
        'description',
    ];

    /**
     * Takes its items down with it.
     *
     * The foreign key is ON DELETE CASCADE, but that only fires on a real
     * DELETE. A soft delete is an UPDATE that stamps deleted_at, so the
     * database cascade never runs and the items would be left live under a
     * deleted parent. Deleting them here keeps deleted_at meaningful: every
     * row records the moment it went, and a force delete still hands the job
     * back to the database.
     */
    protected static function booted(): void
    {
        static::deleting(function (KpiObjective $objective) {
            if ($objective->isForceDeleting()) {
                return;
            }

            $objective->infos()->get()->each->delete();
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(KpiCategory::class, 'category_id', 'id');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_id', 'id');
    }

    /**
     * The scored items under this objective.
     */
    public function infos(): HasMany
    {
        return $this->hasMany(KpiObjectiveInfo::class, 'objective_id', 'id');
    }

    /**
     * Best total achievable here: the sum of each item's highest allowed mark.
     */
    public function maxMark(): int
    {
        return (int) $this->infos->sum(fn (KpiObjectiveInfo $info) => $info->maxMark());
    }
}
