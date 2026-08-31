<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'team';

    protected $primaryKey = 'team_id';

    protected $fillable = [
        'team_name',
        'team_status',
    ];

    protected $casts = [
        'team_status' => 'boolean',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'team_id', 'team_id');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'team_id', 'team_id');
    }

    public function scopeActive($query)
    {
        return $query->where('team_status', true);
    }

    public function hasActiveStaff(): bool
    {
        return $this->staff()->active()->exists();
    }
}
