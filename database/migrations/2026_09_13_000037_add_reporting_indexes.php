<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Composite indexes for the queries that grow with the company: KPI scores,
 * KPI > Report, the Review Schedule and the searchable lists.
 *
 * Each one matches a WHERE the app actually runs, leftmost column first -
 * equality columns before the range column - so MySQL can seek straight to the
 * rows instead of scanning the table. Where a composite starts with a foreign
 * key's column, MySQL retires that key's own single-column index in its
 * favour; down() puts it back.
 *
 * Names say what they serve; the comment on each line says which query.
 */
return new class extends Migration
{
    private const INDEXES = [
        'project_task' => [
            // A member's tasks due in a period - ProjectDeliveryScoreService::tasksFor().
            'project_task_assignee_due_index' => ['assignee_id', 'due_date'],
            // ... or finished in it (the OR half of the same query).
            'project_task_assignee_completed_index' => ['assignee_id', 'completed_at'],
            // Company-wide work finished in a period - KPI Report, unfiltered.
            'project_task_status_completed_index' => ['status', 'completed_at'],
            // A project's task counts by status - project list and Project Report.
            'project_task_project_status_index' => ['project_id', 'status'],
        ],
        'project' => [
            // Completed projects in a period - StaffKpiScoreService::completedProjectsFor().
            'project_status_complete_date_index' => ['status', 'complete_date'],
        ],
        'project_kpi' => [
            // A member's approved marks on given projects - totalScore() and the report.
            'project_kpi_staff_status_project_index' => ['staff_id', 'status', 'project_id'],
            // Marks waiting for approval, oldest first - Manage Pending.
            'project_kpi_status_submitted_index' => ['status', 'submitted_at'],
        ],
        'assessment' => [
            // A member's latest generated appraisal - Review Schedule, KPI period.
            'assessment_staff_status_period_index' => ['staff_id', 'status', 'period_to'],
            // Appraisals by status over a period - the Appraisal list filters.
            'assessment_status_period_index' => ['status', 'period_from', 'period_to'],
        ],
        'staff' => [
            // Active members of a team / position - every report and filter.
            'staff_active_team_index' => ['is_active', 'team_id'],
            'staff_active_position_index' => ['is_active', 'position_id'],
            // Sorting and prefix search by name.
            'staff_name_index' => ['staff_name'],
        ],
        'kpi_objective' => [
            // A position's objectives grouped by category - KPI Setting and report.
            'kpi_objective_position_category_index' => ['position_id', 'category_id'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            Schema::table($table, function (Blueprint $blueprint) use ($indexes) {
                foreach ($indexes as $name => $columns) {
                    $blueprint->index($columns, $name);
                }
            });
        }
    }

    /**
     * MySQL drops a foreign key's own single-column index once a composite
     * index starting with that column exists, since the composite serves the
     * key. So before a composite goes, the foreign key gets a plain index back
     * - otherwise MySQL refuses the drop (error 1553).
     */
    public function down(): void
    {
        foreach (self::INDEXES as $table => $indexes) {
            $foreignColumns = collect(DB::select(
                'SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                [$table]
            ))->pluck('COLUMN_NAME')->all();

            // Leading column of every index that stays.
            $keptLeading = collect(DB::select("SHOW INDEX FROM `{$table}`"))
                ->where('Seq_in_index', 1)
                ->reject(fn ($index) => array_key_exists($index->Key_name, $indexes))
                ->pluck('Column_name')
                ->all();

            Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes, $foreignColumns, $keptLeading) {
                foreach ($indexes as $columns) {
                    $leading = $columns[0];

                    if (in_array($leading, $foreignColumns, true) && ! in_array($leading, $keptLeading, true)) {
                        $blueprint->index([$leading], "{$table}_{$leading}_foreign");
                        $keptLeading[] = $leading;
                    }
                }

                foreach (array_keys($indexes) as $name) {
                    $blueprint->dropIndex($name);
                }
            });
        }
    }
};
