<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiObjectiveInfo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'kpi_objective_info';

    protected $primaryKey = 'kojbInfo_id';

    protected $fillable = [
        'kojbInfo_title',
    ];

    public function objectives(): HasMany
    {
        return $this->hasMany(KpiObjective::class, 'kojbInfo_id', 'kojbInfo_id');
    }
}
