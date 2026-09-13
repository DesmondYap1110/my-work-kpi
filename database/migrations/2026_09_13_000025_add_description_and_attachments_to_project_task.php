<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gives a task somewhere to say what it actually involves, and a real place to
 * put files.
 *
 * A title alone is a label, not a brief. The person doing the work needs room
 * to write what was asked for and what they did about it, and the person
 * reviewing them at the end of the period needs to be able to read it.
 *
 * remark_file and invoice_file go. They were two fixed slots inherited from
 * the old project_phase table's billing vocabulary, never rendered in any
 * form, and NULL on every row - while project_task_files, a proper attachment
 * table, sat empty beside them. One task can now carry as many files as the
 * work needs, each recording who uploaded it and when.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_task', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->dropColumn(['remark_file', 'invoice_file']);
        });

    }

    public function down(): void
    {
        Schema::table('project_task', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->string('remark_file')->nullable()->after('due_date');
            $table->string('invoice_file')->nullable()->after('remark_file');
        });
    }
};
