<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The position a project tag is for - "campaign" for marketing, "bug" for
 * developers.
 *
 * Null is every position, so every existing tag keeps working exactly as
 * before until someone narrows it. Deleting a position opens its tags back up
 * to everyone rather than deleting them, because tasks still carry them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_tag', function (Blueprint $table) {
            $table->unsignedBigInteger('position_id')->nullable()->after('points');
            $table->foreign('position_id')->references('id')->on('staff_position')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('project_tag', function (Blueprint $table) {
            $table->dropForeign(['position_id']);
            $table->dropColumn('position_id');
        });
    }
};
