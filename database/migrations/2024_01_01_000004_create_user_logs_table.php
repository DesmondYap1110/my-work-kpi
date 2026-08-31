<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_IP');
            $table->timestamp('access_date')->useCurrent();
            $table->boolean('access_type'); // 1 = login, 0 = logout
            $table->unsignedBigInteger('staff_id');

            $table->foreign('staff_id')->references('staff_id')->on('staff')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
