<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An attachment on a task. Files live on the public disk under
 * project-task-files/; this records what was uploaded, when and by whom.
 */
class ProjectTaskFile extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * What a task attachment may be.
     *
     * One list, read both by the validation rules and by the code that names
     * the file on disk. That second use is the security-relevant one: these
     * files are served from public storage, so the stored extension decides
     * what the web server will do with them. It is only ever taken from this
     * list, never from the name the browser sent - `mimes:` passes a PHP
     * script renamed to .txt, and storing that back under its client
     * extension would put an executable in a public directory.
     */
    public const ALLOWED_EXTENSIONS = [
        'pdf', 'jpg', 'jpeg', 'png', 'gif',
        'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'zip',
    ];

    protected $table = 'project_task_files';

    protected $fillable = [
        'task_id',
        'filename',
        'original_name',
        'uploaded_at',
        'staff_id',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * What to call it on screen: the name the uploader chose, falling back to
     * the name on disk for rows written before that was recorded.
     */
    public function displayName(): string
    {
        return $this->original_name ?: $this->filename;
    }

    /**
     * asset() rather than Storage::url(), matching how the staff photos are
     * linked.
     *
     * Both resolve to the same place now, but they get there differently:
     * Storage::url() builds from APP_URL, while asset() follows the scheme and
     * host the app is actually being served on. APP_URL said https here while
     * the app ran on http, and every attachment link came back unopenable -
     * so this takes the one that cannot drift out of step with reality.
     */
    public function url(): string
    {
        return asset('storage/project-task-files/'.$this->filename);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'task_id', 'id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'id');
    }
}
