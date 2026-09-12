<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Replaces the five objmk_* boolean columns with a single JSON column
 * holding the allowed mark values, e.g. [2, 1, 0].
 *
 * The old shape needed a schema change to support any other mark value, and
 * every consumer had to know the column-name-to-value mapping (objmk_n1 =>
 * -1). A JSON array says the same thing directly.
 */
return new class extends Migration
{
    /**
     * Old column name => the mark value it represented.
     */
    private const COLUMNS = [
        'objmk_2' => 2,
        'objmk_1' => 1,
        'objmk_0' => 0,
        'objmk_n1' => -1,
        'objmk_n2' => -2,
    ];

    public function up(): void
    {
        Schema::table('kpi_objective_mark', function (Blueprint $table) {
            $table->json('allowed_marks')->nullable()->after('obj_id');
        });

        foreach (DB::table('kpi_objective_mark')->get() as $row) {
            $allowed = [];

            foreach (self::COLUMNS as $column => $value) {
                if ($row->{$column}) {
                    $allowed[] = $value;
                }
            }

            DB::table('kpi_objective_mark')
                ->where('kobjmark_id', $row->kobjmark_id)
                ->update(['allowed_marks' => json_encode($allowed)]);
        }

        Schema::table('kpi_objective_mark', function (Blueprint $table) {
            $table->dropColumn(array_keys(self::COLUMNS));
        });
    }

    public function down(): void
    {
        Schema::table('kpi_objective_mark', function (Blueprint $table) {
            foreach (array_keys(self::COLUMNS) as $column) {
                $table->boolean($column)->default(false);
            }
        });

        foreach (DB::table('kpi_objective_mark')->get() as $row) {
            $allowed = json_decode($row->allowed_marks ?? '[]', true) ?: [];

            DB::table('kpi_objective_mark')
                ->where('kobjmark_id', $row->kobjmark_id)
                ->update(collect(self::COLUMNS)
                    ->map(fn ($value) => in_array($value, $allowed, true))
                    ->all());
        }

        Schema::table('kpi_objective_mark', function (Blueprint $table) {
            $table->dropColumn('allowed_marks');
        });
    }
};
