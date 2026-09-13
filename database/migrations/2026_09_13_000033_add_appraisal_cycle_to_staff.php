<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How often each member is appraised, so the administrator is told when a
 * review is due rather than having to remember. See App\Enums\AppraisalCycle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('appraisal_cycle', 10)->default('manual')->after('team_id')
                ->comment('How often this member is appraised: manual (no schedule), 1w, 1m, 2m, 3m, 4m, 6m, 1y. Next due = last generated appraisal + cycle. See App\Enums\AppraisalCycle.');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('appraisal_cycle');
        });
    }
};
