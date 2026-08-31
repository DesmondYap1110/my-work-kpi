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

    protected $primaryKey = 'staff_id';

    protected $fillable = [
        'staff_name',
        'staffimg',
        'gender',
        'ic',
        'dob',
        'contact',
        'email',
        'staff_address',
        'postcode',
        'city',
        'states',
        'datejointeam',
        'datejoincompany',
        'password',
        'staffstatus',
        'position_id',
        'team_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'staffstatus' => 'boolean',
        'dob' => 'date',
        'datejointeam' => 'date',
        'datejoincompany' => 'date',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(StaffPosition::class, 'position_id', 'position_ID');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id', 'team_id');
    }

    public function projectKpis(): HasMany
    {
        return $this->hasMany(ProjectKpi::class, 'staff_id', 'staff_id');
    }

    public function userLogs(): HasMany
    {
        return $this->hasMany(UserLog::class, 'staff_id', 'staff_id');
    }

    public function scopeActive($query)
    {
        return $query->where('staffstatus', true);
    }

    public function scopeExcludingAdmin($query)
    {
        return $query->where('position_id', '!=', 1);
    }

    public function isAdmin(): bool
    {
        return (int) $this->position_id === 1;
    }
}
