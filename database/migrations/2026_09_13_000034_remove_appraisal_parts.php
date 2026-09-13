<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the appraisal form's "Parts & Weighting".
 *
 * An appraisal is now scored exactly like a KPI: project marks plus KPI
 * objectives, split by the position's own Project KPI setting
 * (staff_position.project_weight). The form's separate parts, their weightings,
 * the category-to-part link and the per-project 1-5 ratings all duplicated
 * that, so they go.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_category', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });

        Schema::dropIfExists('assessment_project_score');
        Schema::dropIfExists('assessment_section');
    }

    /**
     * Brings the structure back empty; the rows removed in up() are not
     * recoverable from here.
     */
    public function down(): void
    {
        Schema::create('assessment_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_template')->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('rating');
            $table->decimal('weightage', 5, 2)->default(50);
            $table->string('calculation')->nullable();
            $table->boolean('is_optional')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('assessment_project_score', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessment')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('project')->nullOnDelete();
            $table->string('description')->nullable();
            $table->integer('employee_score')->nullable();
            $table->integer('reviewer_score')->nullable();
            $table->timestamps();
        });

        Schema::table('kpi_category', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('id')
                ->constrained('assessment_section')->nullOnDelete();
        });
    }
};
