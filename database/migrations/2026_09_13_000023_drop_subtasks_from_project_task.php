<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes subtasks. A task list is flat: one task, one owner, one status.
 *
 * The nesting was inherited from ClickUp's model and earned nothing here -
 * delivery is scored per task from its tag's points, so a subtask and a task
 * were already worth the same thing and counted the same way. What it did add
 * was a second shape for every listing to handle: roots and children, a
 * cascade on soft delete, and a parent-of-a-parent rule to enforce.
 *
 * Existing subtasks are kept, not deleted - dropping the column simply makes
 * them tasks in their own right, which is the honest reading of work somebody
 * has already done.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_task', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_task', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('project_id')
                ->constrained('project_task')->cascadeOnDelete();
        });
    }
};
