<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops kpi.kpi_title.
 *
 * There is one KPI per position, so the title only restated the position it
 * belonged to. The position name is now used wherever the title was shown.
 *
 * down() recreates the column but cannot restore the values - they are gone
 * once this runs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi', function (Blueprint $table) {
            $table->dropColumn('kpi_title');
        });
    }

    public function down(): void
    {
        Schema::table('kpi', function (Blueprint $table) {
            $table->string('kpi_title')->default('')->after('kpi_id');
        });
    }
};
