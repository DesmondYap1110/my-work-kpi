<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kpi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi';

    protected $primaryKey = 'kpi_id';

    protected $fillable = [
        'kpi_title',
        'position_ID',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_ID', 'position_ID');
    }

    public function objectives(): HasMany
    {
        return $this->hasMany(KpiObjective::class, 'kpi_ID', 'kpi_id');
    }
}
