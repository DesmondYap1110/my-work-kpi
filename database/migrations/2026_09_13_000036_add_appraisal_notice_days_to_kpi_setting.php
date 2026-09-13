<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many days before an appraisal is due the administrator is told about it -
 * a company's own lead time, so it is a setting rather than a number in code.
 * See App\Services\AppraisalScheduleService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_setting', function (Blueprint $table) {
            $table->unsignedTinyInteger('appraisal_notice_days')->default(3)->after('project_weight')
                ->comment('Days before a member\'s next appraisal is due that it shows as "due soon" on the dashboard and Review Schedule. 0 = only on the due date itself.');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_setting', function (Blueprint $table) {
            $table->dropColumn('appraisal_notice_days');
        });
    }
};
