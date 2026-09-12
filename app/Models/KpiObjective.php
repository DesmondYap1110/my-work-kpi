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
