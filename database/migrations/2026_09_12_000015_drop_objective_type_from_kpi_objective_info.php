<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the Standard/Extra flag from scored items.
 *
 * It came from the legacy app, where an "extra" item earned a flat +2 bonus
 * on top of the standard total. Nothing used it - every row was Standard -
 * and it made every item ask a question with only one sensible answer. An
 * item worth more than the others now simply allows higher marks.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('kpi_objective_info', 'objective_type')) {
            Schema::table('kpi_objective_info', function (Blueprint $table) {
                $table->dropColumn('objective_type');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('kpi_objective_info', 'objective_type')) {
            Schema::table('kpi_objective_info', function (Blueprint $table) {
                // 0 = Standard, which is what every row held.
                $table->tinyInteger('objective_type')->default(0)->after('allowed_marks');
            });
        }
    }
};
