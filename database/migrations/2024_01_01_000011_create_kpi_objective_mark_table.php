<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_objective_mark', function (Blueprint $table) {
            $table->id('kobjmark_id');
            $table->unsignedBigInteger('obj_id');
            $table->boolean('objmk_2')->default(false);
            $table->boolean('objmk_1')->default(false);
            $table->boolean('objmk_0')->default(false);
            $table->boolean('objmk_n1')->default(false);
            $table->boolean('objmk_n2')->default(false);

            $table->foreign('obj_id')->references('obj_id')->on('kpi_objective')->onDelete('cascade');
            $table->unique('obj_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_objective_mark');
    }
};
