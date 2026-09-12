<?php

namespace App\Models;

use App\Enums\ObjectiveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One scored item under a KpiObjective: what it is, which marks it allows,
 * and whether it counts as Standard or Extra for scoring.
 */
class KpiObjectiveInfo extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'kpi_objective_info';

    protected $fillable = [
        'objective_id',
        'title',
        'description',
        'allowed_marks',
        'objective_type',
    ];

    protected $casts = [
        'allowed_marks' => 'array',
        'objective_type' => ObjectiveType::class,
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(KpiObjective::class, 'objective_id', 'id');
    }

    /**
     * Mark values this item allows, e.g. [2, 1, 0].
     *
     * @return array<int, int>
     */
    public function allowedMarks(): array
    {
        // Cast to int: values arriving from form input can be numeric strings.
        return array_map('intval', $this->allowed_marks ?? []);
    }

    public function allows(int $mark): bool
    {
        return in_array($mark, $this->allowedMarks(), true);
    }

    /**
     * Highest allowed mark, or 0 when nothing is allowed.
     */
    public function maxMark(): int
    {
        return max([0, ...$this->allowedMarks()]);
    }
}
