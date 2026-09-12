<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Turns the objective tables into a hierarchy:
 *
 *     kpi_category   -> many kpi_objective
 *     kpi_objective  -> many kpi_objective_info
 *
 * kpi_objective becomes a named group (title + description) owned by a
 * position, and kpi_objective_info becomes the scored items under it, each
 * with its own allowed marks.
 *
 * objective_type moves down to kpi_objective_info rather than being dropped:
 * the Standard/Extra distinction drives scoring (StaffKpiScoreService adds a
 * flat bonus when an Extra objective exists), and it belongs with the scored
 * item now that the parent is just a heading.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guarded so a partially-applied run can be completed rather than
        // failing on the columns it already created.
        if (! Schema::hasColumn('kpi_objective', 'title')) {
            Schema::table('kpi_objective', function (Blueprint $table) {
                $table->string('title')->nullable()->after('category_id');
                $table->text('description')->nullable()->after('title');
            });
        }

        if (! Schema::hasColumn('kpi_objective_info', 'objective_id')) {
            Schema::table('kpi_objective_info', function (Blueprint $table) {
                $table->unsignedBigInteger('objective_id')->nullable()->after('id');
                $table->unsignedTinyInteger('objective_type')->default(0)->after('allowed_marks');

                $table->foreign('objective_id')->references('id')->on('kpi_objective')->cascadeOnDelete();
            });
        }

        // Each objective takes its heading from the catalog entry it used, and
        // that entry becomes one of its children. Where two objectives shared
        // an entry the first claims it; the second is given a copy so nothing
        // is silently reparented away.
        $claimed = [];

        foreach (DB::table('kpi_objective')->orderBy('id')->get() as $objective) {
            $info = DB::table('kpi_objective_info')->where('id', $objective->objective_info_id)->first();

            if (! $info) {
                continue;
            }

            DB::table('kpi_objective')->where('id', $objective->id)->update([
                'title' => $info->title,
                'description' => $info->description,
            ]);

            if (! isset($claimed[$info->id])) {
                $claimed[$info->id] = true;

                DB::table('kpi_objective_info')->where('id', $info->id)->update([
                    'objective_id' => $objective->id,
                    'objective_type' => $objective->objective_type,
                ]);

                continue;
            }

            DB::table('kpi_objective_info')->insert([
                'objective_id' => $objective->id,
                'title' => $info->title,
                'description' => $info->description,
                'allowed_marks' => $info->allowed_marks,
                'objective_type' => $objective->objective_type,
            ]);
        }

        if (Schema::hasColumn('kpi_objective', 'objective_info_id')) {
            // The constraint still carries the name it was created with, back
            // when the column was kojbInfo_id - renaming a column doesn't
            // rename its foreign key - so it's dropped by its real name rather
            // than the one Laravel would infer.
            $this->dropForeignKeyFor('kpi_objective', 'objective_info_id');

            Schema::table('kpi_objective', function (Blueprint $table) {
                $table->dropColumn(['objective_info_id', 'objective_type']);
            });
        }
    }

    /**
     * Drops whatever foreign key currently constrains a column, whatever it
     * happens to be called.
     */
    private function dropForeignKeyFor(string $table, string $column): void
    {
        $constraint = DB::selectOne('
            SELECT CONSTRAINT_NAME AS name
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ', [$table, $column]);

        if ($constraint) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint->name}`");
        }
    }

    public function down(): void
    {
        Schema::table('kpi_objective', function (Blueprint $table) {
            $table->unsignedBigInteger('objective_info_id')->nullable()->after('category_id');
            $table->unsignedTinyInteger('objective_type')->default(0)->after('objective_info_id');
        });

        foreach (DB::table('kpi_objective_info')->whereNotNull('objective_id')->get() as $info) {
            DB::table('kpi_objective')->where('id', $info->objective_id)->update([
                'objective_info_id' => $info->id,
                'objective_type' => $info->objective_type,
            ]);
        }

        Schema::table('kpi_objective', function (Blueprint $table) {
            $table->dropColumn(['title', 'description']);
            $table->foreign('objective_info_id')->references('id')->on('kpi_objective_info');
        });

        Schema::table('kpi_objective_info', function (Blueprint $table) {
            $table->dropForeign(['objective_id']);
            $table->dropColumn(['objective_id', 'objective_type']);
        });
    }
};
