<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Weighted tags for project work: epic 13, new feature 4, task 0.6, and so on.
 *
 * This is what makes the project half of an assessment scoreable - work is
 * tagged, each tag carries points, and the section score is the points earned
 * over the review period. Every company weighs effort differently, so the tags
 * and their points are data rather than code.
 *
 * Points are decimal because the scale runs from 0.2 (small task) upwards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_tag', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('points', 6, 2)->default(0);
            $table->string('colour')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('project_phase', function (Blueprint $table) {
            $table->foreignId('tag_id')->nullable()->after('type')
                ->constrained('project_tag')->nullOnDelete();
        });

        // A starting set matching a typical delivery scale; all editable.
        $now = now();
        $tags = [
            ['OT', 2], ['small-task', 0.2], ['task', 0.6], ['bug', 1],
            ['small-improvement', 1], ['improvement', 2], ['new feature', 4],
            ['campaign', 2], ['technical', 2], ['epic', 13], ['support', 3],
            ['support 2', 1.5], ['meeting', 1], ['outstation', 2.5],
        ];

        DB::table('project_tag')->insert(
            collect($tags)->map(fn ($tag, $index) => [
                'name' => $tag[0],
                'points' => $tag[1],
                'sort_order' => $index,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all()
        );
    }

    public function down(): void
    {
        Schema::table('project_phase', function (Blueprint $table) {
            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');
        });

        Schema::dropIfExists('project_tag');
    }
};
