<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A weighted label for project work - "epic" is worth 13, "small-task" 0.2.
 *
 * The points are what turn delivery into a score, and every company weighs
 * effort differently, so tags are data the user maintains rather than a fixed
 * list in code.
 */
class ProjectTag extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_tag';

    protected $fillable = [
        'name',
        'points',
        'colour',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function phases(): HasMany
    {
        return $this->hasMany(ProjectPhase::class, 'tag_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
