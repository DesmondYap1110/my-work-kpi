<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_position', function (Blueprint $table) {
            $table->id('position_ID');
            $table->string('position_name');
            $table->text('job_scope')->nullable();
            $table->boolean('kpistatus')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_position');
    }
};
