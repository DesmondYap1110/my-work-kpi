<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffPosition extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The Administrator position. Load-bearing: the whole portal gates login
     * on a staff member holding it (see EnsureIsAdminPosition), and it is
     * never itself assessed.
     */
    public const ADMIN_ID = 1;

    protected $table = 'staff_position';

    protected $fillable = [
        'position_name',
        'job_scope',
        'has_kpi',
    ];

    protected $casts = [
        'has_kpi' => 'boolean',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'position_id', 'id');
    }

    /**
     * A position's KPI is simply its objectives - there is no separate KPI
     * record; has_kpi records whether one has been assigned.
     */
    public function objectives(): HasMany
    {
        return $this->hasMany(KpiObjective::class, 'position_id', 'id');
    }

    /**
     * The Administrator position is the portal's access gate, not a job that
     * gets reviewed, so it never carries a KPI.
     */
    public function isAdministrator(): bool
    {
        return (int) $this->id === self::ADMIN_ID;
    }

    public function scopeExcludingAdmin($query)
    {
        return $query->where('id', '!=', self::ADMIN_ID);
    }

    /**
     * Positions that can still be given a KPI. Administrator is excluded
     * rather than merely unassigned, so it never turns up as a candidate.
     */
    public function scopeWithoutKpi($query)
    {
        return $query->where('has_kpi', false)->excludingAdmin();
    }
}
