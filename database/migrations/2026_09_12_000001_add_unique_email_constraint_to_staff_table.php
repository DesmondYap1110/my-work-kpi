<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforces unique staff emails at the database level.
 *
 * The form requests already validate uniqueness, but validation alone leaves a
 * race: two concurrent submissions can both pass the check and then both
 * insert. Only a constraint closes that.
 *
 * A plain unique index won't do, because staff are soft-deleted and a deleted
 * person's email must be reusable. MySQL has no partial ("WHERE deleted_at IS
 * NULL") indexes, so this uses a generated column that is the email while the
 * row is live and NULL once it is soft-deleted. MySQL treats NULLs as distinct
 * in a unique index, so any number of deleted rows can share an email while
 * live rows cannot.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Fold away any pre-existing duplicates first, otherwise adding the
        // index fails. Keeps the earliest row and soft-deletes the rest.
        $duplicates = DB::table('staff')
            ->select('email')
            ->whereNull('deleted_at')
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('email');

        foreach ($duplicates as $email) {
            $keepId = DB::table('staff')
                ->where('email', $email)
                ->whereNull('deleted_at')
                ->orderBy('staff_id')
                ->value('staff_id');

            DB::table('staff')
                ->where('email', $email)
                ->whereNull('deleted_at')
                ->where('staff_id', '!=', $keepId)
                ->update(['deleted_at' => now()]);
        }

        DB::statement('
            ALTER TABLE `staff`
            ADD COLUMN `email_active` VARCHAR(255)
                GENERATED ALWAYS AS (IF(`deleted_at` IS NULL, `email`, NULL)) STORED
        ');

        DB::statement('
            ALTER TABLE `staff`
            ADD UNIQUE INDEX `staff_email_active_unique` (`email_active`)
        ');
    }

    public function down(): void
    {
        Schema::table('staff', function ($table) {
            $table->dropUnique('staff_email_active_unique');
        });

        DB::statement('ALTER TABLE `staff` DROP COLUMN `email_active`');
    }
};
