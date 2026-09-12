<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Company-wide KPI settings. One row, id 1.
 *
 * project_weight is how much of a score comes from delivering project work;
 * objectives take the rest. One install serves one company, so this is the
 * company's answer to "how much of the job is shipping things?" - 70 for a
 * project-driven software house, 30 for a service company, 0 for one that
 * runs no projects at all.
 */
class KpiSetting extends Model
{
    protected $table = 'kpi_setting';

    protected $fillable = [
        'project_weight',
    ];

    protected $casts = [
        'project_weight' => 'integer',
    ];

    /**
     * The settings row, created on demand so a fresh install and a half-run
     * seeder both behave.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], ['project_weight' => 0]);
    }

    /**
     * Delivery's share as a fraction. Objectives take 1 - this.
     */
    public function projectShare(): float
    {
        return max(0, min(100, $this->project_weight)) / 100;
    }
}
