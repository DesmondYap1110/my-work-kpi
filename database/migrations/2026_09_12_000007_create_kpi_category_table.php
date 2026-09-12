<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Groups objectives under headings, e.g. "Delivery", "Quality", "Teamwork".
 *
 * One category holds many objectives; an objective belongs to at most one.
 * Nullable so existing objectives stay valid and categorising is optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_category', function (Blueprint $table) {
            $table->id('category_id');
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('kpi_objective', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->after('position_id');

            $table->foreign('category_id')
                ->references('category_id')
                ->on('kpi_category')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kpi_objective', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('kpi_category');
    }
};
