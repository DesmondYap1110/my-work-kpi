<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records what an uploader called their file.
 *
 * `filename` is the name on disk, and it is generated rather than taken from
 * the upload: two people attaching "screenshot.png" must not overwrite one
 * another, and a name that arrived from a browser is user input. But an
 * attachment list showing generated names is unreadable, so the original is
 * kept beside it and is what the screen displays.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_task_files', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('filename');
        });
    }

    public function down(): void
    {
        Schema::table('project_task_files', function (Blueprint $table) {
            $table->dropColumn('original_name');
        });
    }
};
