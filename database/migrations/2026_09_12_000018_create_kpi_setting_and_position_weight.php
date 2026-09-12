<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * How much of a KPI score comes from delivering project work.
 *
 * One install serves one company, so the split is a company-level setting:
 * project_weight is a percentage and objectives take the remainder. 70/30 and
 * 50/50 are just values of it - a project-driven software house sets 70, a
 * service company 30, and a company that runs no projects leaves it at 0.
 *
 * staff_position.project_weight overrides it for one role, so an office admin
 * can sit at 0 while the engineers are at 70.
 *
 * Single row, id 1. A settings table rather than config/ because this is
 * edited in the UI by someone who will never touch a config file.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('project_weight')->default(0);
            $table->timestamps();
        });

        // Default 0: until someone sets a weight, scores come from objectives
        // alone - which is exactly how the app behaves today.
        DB::table('kpi_setting')->insert([
            'id' => 1,
            'project_weight' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::table('staff_position', function (Blueprint $table) {
            $table->unsignedTinyInteger('project_weight')->nullable()->after('job_scope');
        });
    }

    public function down(): void
    {
        Schema::table('staff_position', function (Blueprint $table) {
            $table->dropColumn('project_weight');
        });

        Schema::dropIfExists('kpi_setting');
    }
};
