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
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'position_id', 'id');
    }

    /**
     * A position's KPI is simply its objectives - there is no separate KPI
     * record, and no stored flag either: see hasKpi().
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
     * Whether this position has a KPI.
     *
     * Derived, not stored. A has_kpi column used to record it, and it drifted:
     * assigning a KPI set the flag and dropped you on the objectives page, so
     * walking away without adding anything left a position reading "Yes" with
     * nothing behind it. Having objectives IS having a KPI.
     *
     * Uses the eager-loaded count when the caller asked for one
     * (->withCount('objectives')), so a list does not run a query per row.
     */
    public function hasKpi(): bool
    {
        if ($this->objectives_count !== null) {
            return $this->objectives_count > 0;
        }

        return $this->objectives()->exists();
    }

    /**
     * Positions that already have objectives.
     */
    public function scopeWithKpi($query)
    {
        return $query->has('objectives')->excludingAdmin();
    }

    /**
     * Positions that can still be given a KPI. Administrator is excluded
     * rather than merely unassigned, so it never turns up as a candidate.
     */
    public function scopeWithoutKpi($query)
    {
        return $query->doesntHave('objectives')->excludingAdmin();
    }
}
