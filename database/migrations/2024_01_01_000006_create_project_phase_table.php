<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_phase', function (Blueprint $table) {
            $table->id('p_PID');
            $table->unsignedBigInteger('p_ID');
            $table->string('p_PTitle');
            $table->unsignedTinyInteger('p_Type')->default(0); // 0 Standard, 1 Amendment
            $table->date('p_SDate');
            $table->date('p_DDate');
            $table->string('p_Remark')->nullable();
            $table->string('p_Invoice')->nullable();
            $table->unsignedTinyInteger('p_Status')->default(0); // 0 NoSubmission,1 Pending,2 Approved,3 Rejected
            $table->unsignedTinyInteger('p_ppstatus')->nullable(); // 1 Progress,2 OnHold,3 Complete
            $table->dateTime('p_SubmitDate')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('p_ID')->references('project_id')->on('project')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_phase');
    }
};
