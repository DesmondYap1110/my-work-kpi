<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the appraisal form's Rating Scale.
 *
 * The marks an item can be given are the item's own allowed marks
 * (kpi_objective_info.allowed_marks), set on the position's KPI Setting page -
 * so a separate company-wide scale of named marks only repeated them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('assessment_rating');
    }

    /**
     * Brings the table back empty; its rows are not recoverable from here.
     */
    public function down(): void
    {
        Schema::create('assessment_rating', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_template')->cascadeOnDelete();
            $table->integer('value');
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }
};
