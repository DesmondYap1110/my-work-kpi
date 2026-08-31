<?php

namespace App\Models;

use App\Enums\PhaseApprovalStatus;
use App\Enums\PhaseProgressStatus;
use App\Enums\PhaseType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPhase extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_phase';

    protected $primaryKey = 'p_PID';

    protected $fillable = [
        'p_ID',
        'p_PTitle',
        'p_Type',
        'p_SDate',
        'p_DDate',
        'p_Remark',
        'p_Invoice',
        'p_Status',
        'p_ppstatus',
        'p_SubmitDate',
    ];

    protected $casts = [
        'p_SDate' => 'date',
        'p_DDate' => 'date',
        'p_SubmitDate' => 'datetime',
        'p_Type' => PhaseType::class,
        'p_Status' => PhaseApprovalStatus::class,
        'p_ppstatus' => PhaseProgressStatus::class,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'p_ID', 'project_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectPhaseFile::class, 'p_pID', 'p_PID');
    }
}
