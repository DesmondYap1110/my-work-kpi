<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project', function (Blueprint $table) {
            $table->id('project_id');
            $table->string('p_Title');
            $table->date('p_addDate');
            $table->date('p_SDate');
            $table->date('p_EDate');
            $table->unsignedBigInteger('team_id');
            $table->date('date_assign')->nullable();
            $table->unsignedTinyInteger('p_status')->default(1); // 1 Active, 2 Completed, 3 In-Progress, 4 Cancelled
            $table->dateTime('complete_date')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('team_id')->references('team_id')->on('team')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project');
    }
};
