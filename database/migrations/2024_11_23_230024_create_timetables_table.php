<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('school_id');
            $table->string('name');
            $table->string('on_time');
            $table->string('off_time');
            $table->string('checkin_start');
            $table->string('checkin_end');
            $table->string('checkout_start');
            $table->string('checkout_end');
            $table->integer('late_time');
            $table->integer('leave_early_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
