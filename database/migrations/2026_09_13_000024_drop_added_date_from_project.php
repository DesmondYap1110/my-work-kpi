<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops project.added_date, which asked a person to type what the row already
 * knew: created_at records when the project was entered.
 *
 * Two fields for one fact is two fields that can disagree, and this one could
 * be back-dated by hand while created_at could not - so the form was asking
 * for a date whose only use was to be wrong.
 *
 * start_date loses its "not before added_date" rule with it. Nothing replaces
 * that: a project may legitimately have started before somebody got round to
 * entering it, and created_at is the wrong floor for the same reason.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project', function (Blueprint $table) {
            $table->dropColumn('added_date');
        });
    }

    public function down(): void
    {
        Schema::table('project', function (Blueprint $table) {
            $table->date('added_date')->nullable()->after('title');
        });
    }
};
