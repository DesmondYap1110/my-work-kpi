<?php

namespace App\Models;

use App\Enums\ObjectiveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiObjective extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_objective';

    protected $primaryKey = 'obj_id';

    protected $fillable = [
        'kpi_ID',
        'kojbInfo_id',
        'obj_type',
    ];

    protected $casts = [
        'obj_type' => ObjectiveType::class,
    ];

    public function kpi(): BelongsTo
    {
        return $this->belongsTo(Kpi::class, 'kpi_ID', 'kpi_id');
    }

    public function info(): BelongsTo
    {
        return $this->belongsTo(KpiObjectiveInfo::class, 'kojbInfo_id', 'kojbInfo_id');
    }

    public function mark(): HasOne
    {
        return $this->hasOne(KpiObjectiveMark::class, 'obj_id', 'obj_id');
    }

    /**
     * Maximum achievable mark for this single objective, based on the
     * highest allowed value flagged on its mark record (2 > 1 > 0).
     */
    public function maxMark(): int
    {
        if (! $this->mark) {
            return 0;
        }

        return match (true) {
            $this->mark->objmk_2 => 2,
            $this->mark->objmk_1 => 1,
            default => 0,
        };
    }
}
