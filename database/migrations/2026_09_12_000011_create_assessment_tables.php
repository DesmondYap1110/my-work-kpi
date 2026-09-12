<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The assessment module: a configurable review form.
 *
 * A template is made of weighted sections. Two kinds exist:
 *
 *   rating  - manually scored measurements (soft skill, hard skill), reusing
 *             the existing kpi_category -> kpi_objective -> kpi_objective_info
 *             hierarchy
 *   project - scored from the employee's actual project work, using a named
 *             calculation so different companies can weigh delivery their own
 *             way
 *
 * Weightage is per section and set by the user, so one company can run
 * 50 skills / 50 project and another 30 / 70.
 *
 * This sits alongside the existing project_kpi flow rather than replacing it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_template', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('assessment_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_template')->cascadeOnDelete();
            $table->string('title');
            // 'rating' = scored measurements, 'project' = computed from projects
            $table->string('type')->default('rating');
            // Percentage of the final score this section contributes.
            $table->decimal('weightage', 5, 2)->default(50);
            // Which algorithm scores a project section - see ProjectScoreCalculator.
            $table->string('calculation')->nullable();
            // "(if applicable)" sections, e.g. Leadership, skipped when unused.
            $table->boolean('is_optional')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Rating sections own categories; a category without a section is
        // free-standing, which is how they behaved before this module.
        Schema::table('kpi_category', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('id')
                ->constrained('assessment_section')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0)->after('name');
        });

        // The 1-5 scale and what each point means, per template.
        Schema::create('assessment_rating', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_template')->cascadeOnDelete();
            $table->integer('value');
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 90-100 Outstanding, 80-89 Very Good, ...
        Schema::create('assessment_band', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_template')->cascadeOnDelete();
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->string('label');
            // What this band means for the review, e.g. Pass / Extend / Fail.
            $table->string('outcome')->nullable();
            $table->timestamps();
        });

        // One employee's filled-in form.
        Schema::create('assessment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_template')->restrictOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->date('review_date')->nullable();
            $table->date('next_assessment_date')->nullable();
            $table->string('status')->default('draft');
            $table->text('comments')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Employee self-score and reviewer score, per measurement.
        Schema::create('assessment_score', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessment')->cascadeOnDelete();
            $table->foreignId('objective_info_id')->constrained('kpi_objective_info')->cascadeOnDelete();
            $table->integer('employee_score')->nullable();
            $table->integer('reviewer_score')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'objective_info_id'], 'assessment_score_unique');
        });

        // Rows under a project section - one per project being judged.
        Schema::create('assessment_project_score', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessment')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('project')->nullOnDelete();
            // Free text for companies that list work not tracked as a project.
            $table->string('description')->nullable();
            $table->integer('employee_score')->nullable();
            $table->integer('reviewer_score')->nullable();
            $table->timestamps();
        });

        // The monthly check-ins that precede the assessment itself.
        Schema::create('assessment_checkin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessment')->cascadeOnDelete();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->date('review_date')->nullable();
            $table->text('reviewer_feedback')->nullable();
            $table->text('reviewee_comments')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::table('kpi_category', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn(['section_id', 'sort_order']);
        });

        foreach ([
            'assessment_checkin',
            'assessment_project_score',
            'assessment_score',
            'assessment',
            'assessment_band',
            'assessment_rating',
            'assessment_section',
            'assessment_template',
        ] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
