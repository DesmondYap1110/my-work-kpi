<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Lets a staff record belong to no team.
 *
 * The System Administrator is not part of any team - it is the account that
 * sets teams up - but staff.team_id was NOT NULL, so a fresh install had to
 * invent a "Management" team purely to have something to point it at.
 *
 * Members are still required to have a team; that rule lives in the member
 * form requests, where it belongs, rather than in a column constraint that
 * also caught the one account it should not.
 *
 * Raw ALTER because this install has no doctrine/dbal for ->change(). Only the
 * nullability changes, so the existing foreign key is untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE staff MODIFY team_id BIGINT UNSIGNED NULL');

        // The administrator had a team only because the column demanded one.
        DB::table('staff')->where('position_id', 1)->update(['team_id' => null]);
    }

    public function down(): void
    {
        // Refuses while any staff record has no team, rather than inventing
        // one for it - that invented team is what this migration removed.
        DB::statement('ALTER TABLE staff MODIFY team_id BIGINT UNSIGNED NOT NULL');
    }
};
