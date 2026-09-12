<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Gives kpi_objective_info a description and its allowed marks, and drops
 * kpi_objective_mark.
 *
 * The mark table held one row per objective and nothing but a JSON array, so
 * it was a column wearing a table. Moving it onto the catalog entry also makes
 * the marks part of the objective's definition: "Ship on time" allows the same
 * values wherever it is used, rather than being re-decided per position.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_objective_info', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->json('allowed_marks')->nullable()->after('description');
        });

        // Carry each objective's marks up to the catalog entry it uses. Where
        // several objectives share an entry the first non-empty set wins -
        // they are the same objective, so they should agree anyway.
        $rows = DB::table('kpi_objective_mark as m')
            ->join('kpi_objective as o', 'o.id', '=', 'm.objective_id')
            ->select('o.objective_info_id', 'm.allowed_marks')
            ->get();

        $seen = [];

        foreach ($rows as $row) {
            if (isset($seen[$row->objective_info_id]) || blank($row->allowed_marks)) {
                continue;
            }

            $seen[$row->objective_info_id] = true;

            DB::table('kpi_objective_info')
                ->where('id', $row->objective_info_id)
                ->update(['allowed_marks' => $row->allowed_marks]);
        }

        Schema::dropIfExists('kpi_objective_mark');
    }

    public function down(): void
    {
        Schema::create('kpi_objective_mark', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('objective_id');
            $table->json('allowed_marks')->nullable();

            $table->foreign('objective_id')->references('id')->on('kpi_objective')->onDelete('cascade');
        });

        foreach (DB::table('kpi_objective')->get() as $objective) {
            $marks = DB::table('kpi_objective_info')
                ->where('id', $objective->objective_info_id)
                ->value('allowed_marks');

            DB::table('kpi_objective_mark')->insert([
                'objective_id' => $objective->id,
                'allowed_marks' => $marks,
            ]);
        }

        Schema::table('kpi_objective_info', function (Blueprint $table) {
            $table->dropColumn(['description', 'allowed_marks']);
        });
    }
};
