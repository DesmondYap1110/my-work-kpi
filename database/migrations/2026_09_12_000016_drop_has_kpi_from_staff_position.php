<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Drops staff_position.has_kpi.
 *
 * It recorded something already knowable - whether the position has any
 * objectives - and the two drifted apart. Assigning a KPI set the flag and
 * sent you off to add objectives; abandon that and the position read "Yes"
 * forever with nothing behind it, and the dashboard's "positions without KPI"
 * count skipped it too.
 *
 * StaffPosition::hasKpi() derives it now, so there is nothing left to drift.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('staff_position', 'has_kpi')) {
            Schema::table('staff_position', function (Blueprint $table) {
                $table->dropColumn('has_kpi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('staff_position', 'has_kpi')) {
            return;
        }

        Schema::table('staff_position', function (Blueprint $table) {
            $table->boolean('has_kpi')->default(false)->after('job_scope');
        });

        // Rebuild it from the objectives, which is where the truth was.
        DB::statement('
            UPDATE `staff_position` AS p
            SET p.`has_kpi` = EXISTS (
                SELECT 1 FROM `kpi_objective` AS o
                WHERE o.`position_id` = p.`id` AND o.`deleted_at` IS NULL
            )
        ');
    }
};
