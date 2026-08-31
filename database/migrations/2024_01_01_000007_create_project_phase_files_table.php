<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_phase_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('p_pID');
            $table->string('PPfilename');
            $table->dateTime('PPdatetime');
            $table->unsignedBigInteger('staff_ID');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('p_pID')->references('p_PID')->on('project_phase')->onDelete('cascade');
            $table->foreign('staff_ID')->references('staff_id')->on('staff')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_phase_files');
    }
};
