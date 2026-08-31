<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_objective_info', function (Blueprint $table) {
            $table->id('kojbInfo_id');
            $table->string('kojbInfo_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_objective_info');
    }
};
