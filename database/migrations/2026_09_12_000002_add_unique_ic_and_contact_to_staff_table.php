<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Enforces unique staff IC and contact numbers at the database level, using
 * the same generated-column approach as the email constraint.
 *
 * Unlike email these two are optional, so the generated value is also NULL
 * when the column is empty - otherwise every staff member left without an IC
 * would collide with the next one. MySQL treats NULLs as distinct in a unique
 * index, so blanks and soft-deleted rows are both simply ignored.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private array $fields = ['ic', 'contact'];

    public function up(): void
    {
        foreach ($this->fields as $field) {
            $this->normaliseBlanks($field);
            $this->foldDuplicates($field);

            DB::statement("
                ALTER TABLE `staff`
                ADD COLUMN `{$field}_active` VARCHAR(255)
                    GENERATED ALWAYS AS (
                        IF(`deleted_at` IS NULL AND `{$field}` IS NOT NULL AND `{$field}` <> '', `{$field}`, NULL)
                    ) STORED
            ");

            DB::statement("
                ALTER TABLE `staff`
                ADD UNIQUE INDEX `staff_{$field}_active_unique` (`{$field}_active`)
            ");
        }
    }

    public function down(): void
    {
        foreach ($this->fields as $field) {
            Schema::table('staff', function ($table) use ($field) {
                $table->dropUnique("staff_{$field}_active_unique");
            });

            DB::statement("ALTER TABLE `staff` DROP COLUMN `{$field}_active`");
        }
    }

    /**
     * Existing rows may hold '' where the form posted an empty field. Those
     * become NULL so they read the same as "not provided".
     */
    private function normaliseBlanks(string $field): void
    {
        DB::table('staff')->where($field, '')->update([$field => null]);
    }

    /**
     * Adding the index fails if duplicates already exist, so keep the
     * earliest row's value and clear the rest rather than losing records.
     */
    private function foldDuplicates(string $field): void
    {
        $duplicates = DB::table('staff')
            ->select($field)
            ->whereNull('deleted_at')
            ->whereNotNull($field)
            ->groupBy($field)
            ->havingRaw('COUNT(*) > 1')
            ->pluck($field);

        foreach ($duplicates as $value) {
            $keepId = DB::table('staff')
                ->where($field, $value)
                ->whereNull('deleted_at')
                ->orderBy('staff_id')
                ->value('staff_id');

            DB::table('staff')
                ->where($field, $value)
                ->whereNull('deleted_at')
                ->where('staff_id', '!=', $keepId)
                ->update([$field => null]);
        }
    }
};
