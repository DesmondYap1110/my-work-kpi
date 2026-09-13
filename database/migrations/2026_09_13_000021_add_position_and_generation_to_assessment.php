<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two things an appraisal needs that the original assessment tables left out.
 *
 * position_id pins the form to the role the member held when the appraisal was
 * opened. The measurements come from the position's KPI, so without this a
 * promotion would silently rewrite a finished appraisal - items appearing,
 * disappearing, and the printed totals no longer matching the scores.
 *
 * generated_at records when the appraiser handed it over. The member may only
 * read an appraisal that has been generated, so "is it generated" is a fact
 * worth storing rather than inferring from the status string alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('staff_id')
                ->constrained('staff_position')->nullOnDelete();
            $table->timestamp('generated_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('assessment', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn(['position_id', 'generated_at']);
        });
    }
};
