<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames every primary key to `id`, the Laravel default.
 *
 * Foreign keys keep their descriptive names (staff.position_id,
 * project_phase.project_id, ...), which is the standard convention: the owning
 * table says `id`, and anything pointing at it says `<thing>_id`. It also
 * removes the need for $primaryKey on the models.
 *
 * MySQL 8's RENAME COLUMN carries dependent foreign keys across, so the
 * constraints need no rebuilding.
 */
return new class extends Migration
{
    /**
     * table => current primary key
     */
    private const PRIMARY_KEYS = [
        'staff_position' => 'position_id',
        'team' => 'team_id',
        'staff' => 'staff_id',
        'kpi_category' => 'category_id',
        'kpi_objective' => 'objective_id',
        'kpi_objective_info' => 'objective_info_id',
        'kpi_objective_mark' => 'mark_id',
        'project' => 'project_id',
        'project_phase' => 'phase_id',
        'project_kpi' => 'project_kpi_id',
    ];

    public function up(): void
    {
        foreach (self::PRIMARY_KEYS as $table => $key) {
            DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `{$key}` TO `id`");
        }
    }

    public function down(): void
    {
        foreach (self::PRIMARY_KEYS as $table => $key) {
            DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `id` TO `{$key}`");
        }
    }
};
