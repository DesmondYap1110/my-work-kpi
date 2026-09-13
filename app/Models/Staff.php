<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Staff extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'staff_name',
        'photo',
        'gender',
        'ic',
        'dob',
        'contact',
        'email',
        'address',
        'postcode',
        'city',
        'states',
        'team_joined_date',
        'company_joined_date',
        'password',
        'is_active',
        'position_id',
        'team_id',
        // How often this member is appraised - see App\Enums\AppraisalCycle.
        'appraisal_cycle',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean',
        'dob' => 'date',
        'team_joined_date' => 'date',
        'company_joined_date' => 'date',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_id', 'id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }

    public function projectKpis(): HasMany
    {
        return $this->hasMany(ProjectKpi::class, 'staff_id', 'id');
    }

    public function userLogs(): HasMany
    {
        return $this->hasMany(UserLog::class, 'staff_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExcludingAdmin($query)
    {
        return $query->where('position_id', '!=', StaffPosition::ADMIN_ID);
    }

    /**
     * The system administrator: the only member who may set the company up -
     * its teams, positions, members, tags, KPIs and weighting. Everyone else
     * signs in to their own KPI and their own tasks.
     *
     * @see \App\Http\Middleware\EnsureIsAdmin
     */
    public function isAdmin(): bool
    {
        return (int) $this->position_id === StaffPosition::ADMIN_ID;
    }
}
