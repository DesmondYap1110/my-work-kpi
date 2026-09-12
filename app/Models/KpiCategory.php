<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A heading that groups objectives, e.g. "Delivery" or "Quality".
 */
class KpiCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'kpi_category';

    protected $fillable = [
        'name',
    ];

    public function objectives(): HasMany
    {
        return $this->hasMany(KpiObjective::class, 'category_id', 'id');
    }
}
