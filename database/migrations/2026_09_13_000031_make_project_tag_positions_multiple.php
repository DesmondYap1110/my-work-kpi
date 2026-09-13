<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A project tag can be for several positions - "bug" for both developers and
 * testers - so the single position_id becomes position_ids, a JSON array of
 * staff_position ids.
 *
 * Null or empty is every position, as before. Any tag already given a position
 * keeps it, as a one-item list.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tag', function (Blueprint $table) {
            $table->json('position_ids')->nullable()->after('points')
                ->comment('JSON array of staff_position.id this tag is for, e.g. [2,4]. NULL = all positions.');
        });

        DB::table('project_tag')->whereNotNull('position_id')->orderBy('id')->each(function ($tag) {
            DB::table('project_tag')->where('id', $tag->id)
                ->update(['position_ids' => json_encode([(int) $tag->position_id])]);
        });

        Schema::table('project_tag', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('points');
            $table->foreign('position_id')->references('id')->on('staff_position')->nullOnDelete();
        });

        // Only the first position survives going back to a single column.
        DB::table('project_tag')->whereNotNull('position_ids')->orderBy('id')->each(function ($tag) {
            $ids = json_decode($tag->position_ids, true) ?: [];
            DB::table('project_tag')->where('id', $tag->id)->update(['position_id' => $ids[0] ?? null]);
        });

        Schema::table('project_tag', function (Blueprint $table) {
            $table->dropColumn('position_ids');
        });
    }
};
