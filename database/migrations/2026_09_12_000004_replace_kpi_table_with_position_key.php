<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Removes the `kpi` table and keys KPI data off the position instead.
 *
 * Once kpi_title was dropped, a `kpi` row held nothing but a position_ID -
 * a pure junction between a position and its objectives. staff_position
 * already carries `kpistatus` to record whether a position has a KPI, so the
 * table earned nothing.
 *
 * Both dependants are repointed by translating their kpi id to the position
 * it belonged to, so existing objectives and submitted marks survive.
 */
return new class extends Migration
{
    public function up(): void
    {
        // kpi_id -> position_ID, captured before the table goes away.
        $positionByKpi = DB::table('kpi')->pluck('position_ID', 'kpi_id');

        $this->repoint('kpi_objective', 'kpi_ID', 'kpi_objective_kpi_id_foreign', $positionByKpi);
        $this->repoint('project_kpi', 'kpi_id', 'project_kpi_kpi_id_foreign', $positionByKpi);

        Schema::dropIfExists('kpi');
    }

    public function down(): void
    {
        // Recreates the table and a row per position that has a KPI, then
        // points the dependants back at it.
        Schema::create('kpi', function (Blueprint $table) {
            $table->id('kpi_id');
            $table->unsignedBigInteger('position_ID');
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('position_ID')->references('position_ID')->on('staff_position')->onDelete('cascade');
        });

        $positions = DB::table('kpi_objective')->distinct()->pluck('position_ID')
            ->merge(DB::table('project_kpi')->distinct()->pluck('position_ID'))
            ->unique()
            ->filter();

        $kpiByPosition = [];
        foreach ($positions as $positionId) {
            $kpiByPosition[$positionId] = DB::table('kpi')->insertGetId([
                'position_ID' => $positionId,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'kpi_id');
        }

        $this->restore('kpi_objective', 'kpi_ID', 'kpi_objective_kpi_id_foreign', $kpiByPosition);
        $this->restore('project_kpi', 'kpi_id', 'project_kpi_kpi_id_foreign', $kpiByPosition);
    }

    /**
     * Swaps a table's kpi foreign key for a position one, translating the
     * stored ids as it goes.
     */
    private function repoint(string $table, string $column, string $foreignKey, $positionByKpi): void
    {
        Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
            $blueprint->dropForeign($foreignKey);
        });

        // Written while the column still holds kpi ids; renamed afterwards so
        // there is never a moment where the name lies about the contents.
        foreach ($positionByKpi as $kpiId => $positionId) {
            DB::table($table)->where($column, $kpiId)->update([$column => $positionId]);
        }

        // Rows whose kpi no longer exists have nothing to point at.
        DB::table($table)->whereNotIn($column, $positionByKpi->values())->delete();

        DB::statement("ALTER TABLE `{$table}` CHANGE `{$column}` `position_ID` BIGINT UNSIGNED NOT NULL");

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->foreign('position_ID')->references('position_ID')->on('staff_position')->onDelete('cascade');
        });
    }

    private function restore(string $table, string $column, string $foreignKey, array $kpiByPosition): void
    {
        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropForeign(['position_ID']);
        });

        foreach ($kpiByPosition as $positionId => $kpiId) {
            DB::table($table)->where('position_ID', $positionId)->update(['position_ID' => $kpiId]);
        }

        DB::statement("ALTER TABLE `{$table}` CHANGE `position_ID` `{$column}` BIGINT UNSIGNED NOT NULL");

        Schema::table($table, function (Blueprint $blueprint) use ($column, $foreignKey) {
            $blueprint->foreign($column, $foreignKey)->references('kpi_id')->on('kpi')->onDelete('cascade');
        });
    }
};
