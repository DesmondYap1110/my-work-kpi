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
        'p_pID',
        'PPfilename',
        'PPdatetime',
        'staff_ID',
    ];

    protected $casts = [
        'PPdatetime' => 'datetime',
    ];

    public function phase(): BelongsTo
    {
        return $this->belongsTo(ProjectPhase::class, 'p_pID', 'p_PID');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_ID', 'staff_id');
    }
}
