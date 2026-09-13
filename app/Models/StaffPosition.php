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
        'project_weight',
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
     * The scoreable items under this position's objectives - the rows an
     * appraiser actually puts a mark against. Only those with marks to choose
     * from count: an item with no allowed marks cannot be scored.
     */
    public function scoreableItems(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(KpiObjectiveInfo::class, KpiObjective::class, 'position_id', 'objective_id')
            ->whereNotNull('kpi_objective_info.allowed_marks')
            ->where('kpi_objective_info.allowed_marks', '!=', '[]');
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
     * nothing behind it.
     *
     * The same trap one level down: an objective is only a heading. What gets
     * marked is the items under it, so a position with an objective and no
     * items still has nothing anybody can be scored on. Having at least one
     * item with marks IS having a KPI - and the Positions list, the dashboard's
     * "Pending KPI" count and the member form all ask this one question.
     *
     * Uses the eager-loaded count when the caller asked for one
     * (->withCount('scoreableItems')), so a list does not run a query per row.
     */
    public function hasKpi(): bool
    {
        if ($this->scoreable_items_count !== null) {
            return $this->scoreable_items_count > 0;
        }

        return $this->scoreableItems()->exists();
    }

    /**
     * Whether anyone may be put in this position.
     *
     * A member is scored against their position's KPI, so a position without
     * one would hold people who can never be assessed - their scorecard empty
     * and their appraisal with nothing to rate.
     *
     * The Administrator is the exception: it is the portal's access gate, never
     * assessed, so it needs no KPI to hold the administrator account.
     *
     * Asked by the member form's dropdown, its validation, and the Positions
     * list's add-member button, so the three cannot disagree.
     */
    public function acceptsMembers(): bool
    {
        return $this->isAdministrator() || $this->hasKpi();
    }

    /**
     * The positions acceptsMembers() allows, as a query.
     */
    public function scopeAcceptingMembers($query)
    {
        return $query->where(fn ($q) => $q->whereKey(self::ADMIN_ID)->orHas('scoreableItems'));
    }

    /**
     * Positions with at least one item a member can be marked on.
     */
    public function scopeWithKpi($query)
    {
        return $query->has('scoreableItems')->excludingAdmin();
    }

    /**
     * Positions that still need a KPI - including ones with objectives but no
     * items yet. Administrator is excluded rather than merely unassigned, so it
     * never turns up as a candidate.
     */
    public function scopeWithoutKpi($query)
    {
        return $query->doesntHave('scoreableItems')->excludingAdmin();
    }
}
