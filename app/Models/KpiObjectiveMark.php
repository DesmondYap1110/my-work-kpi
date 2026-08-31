<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiObjectiveMark extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'kpi_objective_mark';

    protected $primaryKey = 'kobjmark_id';

    protected $fillable = [
        'obj_id',
        'objmk_2',
        'objmk_1',
        'objmk_0',
        'objmk_n1',
        'objmk_n2',
    ];

    protected $casts = [
        'objmk_2' => 'boolean',
        'objmk_1' => 'boolean',
        'objmk_0' => 'boolean',
        'objmk_n1' => 'boolean',
        'objmk_n2' => 'boolean',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(KpiObjective::class, 'obj_id', 'obj_id');
    }

    /**
     * All mark values legally allowed for this objective, e.g. [2, 1, 0, -1, -2].
     *
     * @return array<int, int>
     */
    public function allowedMarks(): array
    {
        $map = [
            2 => $this->objmk_2,
            1 => $this->objmk_1,
            0 => $this->objmk_0,
            -1 => $this->objmk_n1,
            -2 => $this->objmk_n2,
        ];

        return array_keys(array_filter($map));
    }
}
