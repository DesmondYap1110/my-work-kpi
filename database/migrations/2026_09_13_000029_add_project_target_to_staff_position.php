<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The project marks a position is expected to earn - "a Tester needs 30".
 *
 * Without a target, the project part of a KPI was earned points divided by the
 * points of the tasks a member happened to be given, so someone given little
 * work could score 100% on very little. A target measures them against what the
 * role is expected to deliver instead. Blank keeps the old measure.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_position', function (Blueprint $table) {
            $table->decimal('project_target', 8, 2)->nullable()->after('project_weight');
        });
    }

    public function down(): void
    {
        Schema::table('staff_position', function (Blueprint $table) {
            $table->dropColumn('project_target');
        });
    }
};
