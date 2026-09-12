<?php

namespace App\Models;

use App\Enums\AccessType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLog extends Model
{
    public $timestamps = false;

    protected $table = 'user_logs';

    protected $fillable = [
        'ip_address',
        'access_date',
        'access_type',
        'staff_id',
    ];

    protected $casts = [
        'access_date' => 'datetime',
        'access_type' => AccessType::class,
    ];

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
}
