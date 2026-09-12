<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Brings existing rows into line with the cascade the models now apply.
 *
 * The foreign keys are ON DELETE CASCADE, but that only fires on a real
 * DELETE. Every one of these tables soft deletes, which is an UPDATE that
 * stamps deleted_at - so the cascade never ran, and deleting an objective
 * left its items live underneath it.
 *
 * KpiObjective::booted() and KpiCategory::booted() close that off going
 * forward. This repairs what the old behaviour left behind, stamping each
 * child with the moment its parent went rather than "now", because that is
 * when it actually stopped counting.
 *
 * On a fresh database there is nothing to find and this does nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Items whose objective is already deleted.
        $items = DB::update('
            UPDATE `kpi_objective_info` AS i
            JOIN `kpi_objective` AS o ON o.`id` = i.`objective_id`
            SET i.`deleted_at` = o.`deleted_at`, i.`updated_at` = NOW()
            WHERE o.`deleted_at` IS NOT NULL AND i.`deleted_at` IS NULL
        ');

        // Objectives whose category is already deleted.
        $objectives = DB::update('
            UPDATE `kpi_objective` AS o
            JOIN `kpi_category` AS c ON c.`id` = o.`category_id`
            SET o.`deleted_at` = c.`deleted_at`, o.`updated_at` = NOW()
            WHERE c.`deleted_at` IS NOT NULL AND o.`deleted_at` IS NULL
        ');

        // ... and the items under those, now that they have gone too.
        $items += DB::update('
            UPDATE `kpi_objective_info` AS i
            JOIN `kpi_objective` AS o ON o.`id` = i.`objective_id`
            SET i.`deleted_at` = o.`deleted_at`, i.`updated_at` = NOW()
            WHERE o.`deleted_at` IS NOT NULL AND i.`deleted_at` IS NULL
        ');

        echo "  cascaded to {$objectives} objective(s) and {$items} item(s)".PHP_EOL;
    }

    public function down(): void
    {
        // Not reversible: which rows were deleted in their own right and which
        // were swept up here is no longer distinguishable. Restoring them all
        // would resurrect rows the user deleted deliberately.
    }
};
