<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPhaseFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_phase_files';

    protected $fillable = [
        'phase_id',
        'filename',
        'uploaded_at',
        'staff_id',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'phase_id', 'id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
}
