<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class StaffPosition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'staff_position';

    protected $primaryKey = 'position_ID';

    protected $fillable = [
        'position_name',
        'job_scope',
        'kpistatus',
    ];

    protected $casts = [
        'kpistatus' => 'boolean',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class, 'position_id', 'position_ID');
    }

    public function kpi(): HasOne
    {
        return $this->hasOne(Kpi::class, 'position_ID', 'position_ID');
    }

    public function scopeWithoutKpi($query)
    {
        return $query->where('kpistatus', false);
    }
}
