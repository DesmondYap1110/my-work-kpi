<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_kpi', function (Blueprint $table) {
            $table->id('kpiproject_id');
            $table->unsignedBigInteger('staff_id');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('kpi_id');
            $table->unsignedBigInteger('kojbInfo_id');
            $table->integer('mark')->nullable();
            $table->unsignedTinyInteger('status')->nullable(); // null=not submitted, 1=Approved, 2=Rejected
            $table->dateTime('createddate')->nullable(); // submitted-at

            $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
            $table->foreign('project_id')->references('project_id')->on('project')->onDelete('cascade');
            $table->foreign('kpi_id')->references('kpi_id')->on('kpi')->onDelete('cascade');
            $table->foreign('kojbInfo_id')->references('kojbInfo_id')->on('kpi_objective_info')->onDelete('restrict');

            $table->unique(['staff_id', 'project_id', 'kpi_id', 'kojbInfo_id'], 'project_kpi_unique_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_kpi');
    }
};
