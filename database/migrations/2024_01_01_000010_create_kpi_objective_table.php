<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_objective', function (Blueprint $table) {
            $table->id('obj_id');
            $table->unsignedBigInteger('kpi_ID');
            $table->unsignedBigInteger('kojbInfo_id');
            $table->unsignedTinyInteger('obj_type')->default(0); // 0 Standard, 1 Extra/bonus
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('kpi_ID')->references('kpi_id')->on('kpi')->onDelete('cascade');
            $table->foreign('kojbInfo_id')->references('kojbInfo_id')->on('kpi_objective_info')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_objective');
    }
};
