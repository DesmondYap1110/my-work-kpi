<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A project belongs to the people doing the work, not to a team.
 *
 * team_id was how the app decided who a project concerned: on completion it
 * scored every active member of that team identically, whoever had actually
 * done anything. Tasks carry an assignee now, so the people on a project are
 * simply the people with tasks on it - which is both truer and already
 * recorded.
 *
 * Teams themselves stay: staff still belong to one. It is only the project
 * that stops being a team-level thing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('project', 'team_id')) {
            return;
        }

        Schema::table('project', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('project', 'team_id')) {
            return;
        }

        Schema::table('project', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->after('end_date')
                ->constrained('team')->nullOnDelete();
        });

        // Best effort: put each project back with the team of whoever was
        // assigned the most tasks on it. There is no better answer - the link
        // genuinely did not survive.
        DB::statement('
            UPDATE `project` AS p
            SET p.`team_id` = (
                SELECT s.`team_id`
                FROM `project_task` AS t
                JOIN `staff` AS s ON s.`id` = t.`assignee_id`
                WHERE t.`project_id` = p.`id` AND t.`deleted_at` IS NULL
                GROUP BY s.`team_id`
                ORDER BY COUNT(*) DESC
                LIMIT 1
            )
        ');
    }
};
