<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lets a task exist without dates.
 *
 * start_date and due_date came over from project_phase, where a phase always
 * had both - a billing period has to start and end somewhere. A task does not:
 * "write the release notes" is real work whether or not anyone has decided
 * when it is due, and refusing to record it until someone picks a date is how
 * a tracker ends up with dates nobody means.
 *
 * type came over the same way. It describes a phase (Standard/Amendment) and
 * has no meaning for an ordinary task, so it stops being mandatory too.
 *
 * Raw ALTERs rather than the schema builder: changing a column with
 * ->change() needs doctrine/dbal, which this project deliberately does not
 * install, and these are unambiguous on MySQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `project_task` MODIFY `start_date` DATE NULL');
        DB::statement('ALTER TABLE `project_task` MODIFY `due_date` DATE NULL');
        DB::statement('ALTER TABLE `project_task` MODIFY `type` TINYINT UNSIGNED NULL DEFAULT 0');
    }

    public function down(): void
    {
        // Rows added since could have no dates, and a NOT NULL column cannot
        // hold them - fall back to the project's own dates so nothing is lost.
        DB::statement('
            UPDATE `project_task` AS t
            JOIN `project` AS p ON p.`id` = t.`project_id`
            SET t.`start_date` = COALESCE(t.`start_date`, p.`start_date`),
                t.`due_date` = COALESCE(t.`due_date`, p.`end_date`)
        ');

        DB::statement('UPDATE `project_task` SET `type` = 0 WHERE `type` IS NULL');

        DB::statement('ALTER TABLE `project_task` MODIFY `start_date` DATE NOT NULL');
        DB::statement('ALTER TABLE `project_task` MODIFY `due_date` DATE NOT NULL');
        DB::statement('ALTER TABLE `project_task` MODIFY `type` TINYINT UNSIGNED NOT NULL DEFAULT 0');
    }
};
