<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id('staff_id');
            $table->string('staff_name');
            $table->string('staffimg')->default('default.jpg');
            $table->string('gender')->nullable();
            $table->string('ic')->nullable();
            $table->date('dob')->nullable();
            $table->string('contact')->nullable();
            // No DB-level unique constraint: uniqueness is enforced at the
            // validation layer excluding soft-deleted rows, so an email can
            // be reused once its previous owner is soft-deleted.
            $table->string('email')->index();
            $table->string('staff_address')->nullable();
            $table->string('postcode')->nullable();
            $table->string('city')->nullable();
            $table->string('states')->nullable();
            $table->date('datejointeam')->nullable();
            $table->date('datejoincompany')->nullable();
            $table->string('password');
            $table->boolean('staffstatus')->default(true);
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('team_id');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('position_id')->references('position_ID')->on('staff_position')->onDelete('restrict');
            $table->foreign('team_id')->references('team_id')->on('team')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
