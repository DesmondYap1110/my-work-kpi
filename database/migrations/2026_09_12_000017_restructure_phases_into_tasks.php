<?php

use App\Enums\TaskStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns project phases into tasks.
 *
 * A "phase" was already the only work breakdown a project had, so this renames
 * it rather than building a second table beside it - the tag, the remark and
 * invoice files and the approval trail all carry business meaning and come
 * along unchanged.
 *
 * What it gains is everything ClickUp-style tracking needs: a parent (for
 * subtasks), an assignee, a milestone flag, a richer status, priority, progress
 * and - the one that matters for scoring - completed_at, because the delivery
 * score sums tag points over a review period and updated_at is not a record of
 * when work was finished.
 */
return new class extends Migration
{
    /**
     * Old PhaseProgressStatus => new TaskStatus.
     */
    private const PROGRESS_MAP = [
        1 => TaskStatus::InProgress,   // Progress
        2 => TaskStatus::Blocked,      // OnHold
        3 => TaskStatus::Done,         // Complete
    ];

    public function up(): void
    {
        Schema::rename('project_phase', 'project_task');
        Schema::rename('project_phase_files', 'project_task_files');

        Schema::table('project_task_files', function (Blueprint $table) {
            $table->renameColumn('phase_id', 'task_id');
        });

        Schema::table('project_task', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('project_id')
                ->constrained('project_task')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->after('parent_id')
                ->constrained('staff')->nullOnDelete();
            $table->boolean('is_milestone')->default(false)->after('title');
            $table->tinyInteger('status')->default(TaskStatus::ToDo->value)->after('is_milestone');
            $table->tinyInteger('priority')->nullable()->after('status');
            $table->unsignedTinyInteger('progress')->default(0)->after('priority');
            $table->timestamp('completed_at')->nullable()->after('submitted_date');
            $table->integer('sort_order')->default(0)->after('completed_at');
        });

        $this->carryProgressStatusOver();

        Schema::table('project_task', function (Blueprint $table) {
            $table->dropColumn('progress_status');
        });
    }

    public function down(): void
    {
        Schema::table('project_task', function (Blueprint $table) {
            $table->tinyInteger('progress_status')->default(1)->after('approval_status');
        });

        // Best effort: the richer statuses collapse back onto the three the old
        // column knew about.
        foreach (self::PROGRESS_MAP as $old => $new) {
            DB::table('project_task')->where('status', $new->value)->update(['progress_status' => $old]);
        }

        DB::table('project_task')->whereIn('status', [TaskStatus::ToDo->value, TaskStatus::Review->value])
            ->update(['progress_status' => 1]);

        Schema::table('project_task', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['assignee_id']);
            $table->dropColumn([
                'parent_id', 'assignee_id', 'is_milestone', 'status',
                'priority', 'progress', 'completed_at', 'sort_order',
            ]);
        });

        Schema::table('project_task_files', function (Blueprint $table) {
            $table->renameColumn('task_id', 'phase_id');
        });

        Schema::rename('project_task_files', 'project_phase_files');
        Schema::rename('project_task', 'project_phase');
    }

    /**
     * Existing rows keep the state they were in. A phase already marked
     * complete is stamped completed_at from its submitted_date where there is
     * one - the closest record of when the work actually landed - and its
     * progress filled in, so the new bar doesn't read 0% for finished work.
     */
    private function carryProgressStatusOver(): void
    {
        foreach (self::PROGRESS_MAP as $old => $new) {
            DB::table('project_task')->where('progress_status', $old)->update([
                'status' => $new->value,
                'progress' => $new->isDone() ? 100 : 0,
            ]);
        }

        DB::statement('
            UPDATE `project_task`
            SET `completed_at` = COALESCE(`submitted_date`, `updated_at`)
            WHERE `status` = ? AND `completed_at` IS NULL
        ', [TaskStatus::Done->value]);
    }
};
