<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi', function (Blueprint $table) {
            $table->id('kpi_id');
            $table->string('kpi_title');
            $table->unsignedBigInteger('position_ID');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('position_ID')->references('position_ID')->on('staff_position')->onDelete('restrict');
            $table->unique('position_ID'); // one KPI template per position
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi');
    }
};
