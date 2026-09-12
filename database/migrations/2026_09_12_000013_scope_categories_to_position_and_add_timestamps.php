<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two corrections to the KPI tables.
 *
 * 1. kpi_category gains position_id. Categories were global, so every
 *    position saw every other position's headings - an F&B role would be
 *    offered "Technical Knowledge". A category now belongs to the position
 *    whose objectives it groups.
 *
 * 2. kpi_objective_info, project_kpi and user_logs gain timestamps, matching
 *    every other table. Soft deletes go on the two data tables but not on
 *    user_logs: that is an audit trail, and rows in it should not be
 *    hideable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_category', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('id')
                ->constrained('staff_position')->cascadeOnDelete();
        });

        $this->assignCategoriesToPositions();

        Schema::table('kpi_objective_info', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('project_kpi', function (Blueprint $table) {
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('user_logs', function (Blueprint $table) {
            $table->timestamps();
        });

        // Existing rows would otherwise read as never created. access_date and
        // submitted_at already record when the event happened, so they are the
        // honest value to backfill from.
        DB::statement('UPDATE `user_logs` SET `created_at` = `access_date`, `updated_at` = `access_date`');
        DB::statement('UPDATE `project_kpi` SET `created_at` = `submitted_at`, `updated_at` = `submitted_at` WHERE `submitted_at` IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('user_logs', function (Blueprint $table) {
            $table->dropTimestamps();
        });

        Schema::table('project_kpi', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
        });

        Schema::table('kpi_objective_info', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropTimestamps();
        });

        Schema::table('kpi_category', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }

    /**
     * Gives each category the position its objectives belong to.
     *
     * A category shared by several positions is split - the first keeps the
     * original row and the others get a copy, with their objectives repointed
     * - so nothing is lost and no position inherits another's headings.
     */
    private function assignCategoriesToPositions(): void
    {
        foreach (DB::table('kpi_category')->orderBy('id')->get() as $category) {
            $positionIds = DB::table('kpi_objective')
                ->where('category_id', $category->id)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck('position_id')
                ->filter()
                ->values();

            if ($positionIds->isEmpty()) {
                continue;
            }

            DB::table('kpi_category')
                ->where('id', $category->id)
                ->update(['position_id' => $positionIds->first()]);

            foreach ($positionIds->skip(1) as $positionId) {
                $copyId = DB::table('kpi_category')->insertGetId([
                    'position_id' => $positionId,
                    'name' => $category->name,
                    'sort_order' => $category->sort_order ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('kpi_objective')
                    ->where('category_id', $category->id)
                    ->where('position_id', $positionId)
                    ->update(['category_id' => $copyId]);
            }
        }
    }
};
