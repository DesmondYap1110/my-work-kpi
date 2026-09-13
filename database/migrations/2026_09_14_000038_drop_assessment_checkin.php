<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Monthly check-ins are no longer part of the appraisal: the page section,
 * its routes and model are gone, so the table goes too. It held no rows when
 * removed. down() recreates the empty table, comments included.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('assessment_checkin');
    }

    public function down(): void
    {
        Schema::create('assessment_checkin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessment')->cascadeOnDelete()
                ->comment('The appraisal this check-in belongs to.');
            $table->date('period_from')->nullable()->comment('Start of the month covered. NULL = not set.');
            $table->date('period_to')->nullable()->comment('End of the month covered. NULL = not set.');
            $table->date('review_date')->nullable()->comment('When the check-in was held. NULL = not recorded.');
            $table->text('reviewer_feedback')->nullable()->comment('What the appraiser said. NULL = none recorded.');
            $table->text('reviewee_comments')->nullable()->comment('What the member said. NULL = none recorded.');
            $table->unsignedInteger('sort_order')->default(0)->comment('Display order within the appraisal, ascending.');
            $table->timestamps();
        });
    }
};
