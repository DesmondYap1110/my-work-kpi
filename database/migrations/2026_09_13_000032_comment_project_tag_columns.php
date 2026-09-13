<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Describes every project_tag column in the database itself, so anyone reading
 * the table in phpMyAdmin or a SQL client knows what each one holds.
 *
 * MODIFY repeats each column's existing definition exactly - only the comment
 * is new. No doctrine/dbal here, hence raw SQL.
 */
return new class extends Migration
{
    private const COLUMNS = [
        'id' => ['bigint unsigned NOT NULL AUTO_INCREMENT', 'Primary key.'],
        'name' => ['varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL', 'Tag label shown on tasks, e.g. "bug", "epic". Unique.'],
        'points' => ["decimal(6,2) NOT NULL DEFAULT '0.00'", 'Marks a completed task with this tag earns towards the assignee\'s project KPI, e.g. 0.60.'],
        'position_ids' => ['json DEFAULT NULL', 'JSON array of staff_position.id this tag is for, e.g. [2,4]. NULL = all positions.'],
        'colour' => ['varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'Optional display colour for the tag. Not used by scoring.'],
        'is_active' => ["tinyint(1) NOT NULL DEFAULT '1'", '1 = offered on the task form, 0 = hidden but kept on existing tasks.'],
        'sort_order' => ["int unsigned NOT NULL DEFAULT '0'", 'Order in tag lists and dropdowns, lowest first.'],
        'deleted_at' => ['timestamp NULL DEFAULT NULL', 'Soft delete time. NULL = not deleted.'],
        'created_at' => ['timestamp NULL DEFAULT NULL', 'When the tag was created.'],
        'updated_at' => ['timestamp NULL DEFAULT NULL', 'When the tag was last changed.'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $column => [$definition, $comment]) {
            DB::statement(sprintf(
                'ALTER TABLE `project_tag` MODIFY `%s` %s COMMENT %s',
                $column,
                $definition,
                DB::getPdo()->quote($comment)
            ));
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $column => [$definition]) {
            // position_ids keeps its comment - it was added with the column.
            $comment = $column === 'position_ids' ? " COMMENT 'JSON array of staff_position.id this tag is for, e.g. [2,4]. NULL = all positions.'" : '';
            DB::statement(sprintf('ALTER TABLE `project_tag` MODIFY `%s` %s%s', $column, $definition, $comment));
        }
    }
};
