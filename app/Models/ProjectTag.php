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
        // The positions the tag is for; empty is every position.
        'position_ids',
        'colour',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'is_active' => 'boolean',
        'position_ids' => 'array',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'tag_id', 'id');
    }

    /**
     * The ids of the positions this tag is for, as integers. Empty means every
     * position.
     *
     * @return array<int, int>
     */
    public function positionIds(): array
    {
        return array_values(array_unique(array_map('intval', $this->position_ids ?? [])));
    }

    /**
     * The positions this tag is for, by name - for showing, not for rules.
     */
    public function positions(): \Illuminate\Support\Collection
    {
        return $this->positionIds() === []
            ? collect()
            : StaffPosition::whereIn('id', $this->positionIds())->orderBy('position_name')->get();
    }

    /**
     * Whether a member of this position may be given a task with this tag.
     * An unassigned task (no position) can take any tag.
     */
    public function allowsPosition(?int $positionId): bool
    {
        return $positionId === null || $this->positionIds() === [] || in_array($positionId, $this->positionIds(), true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
