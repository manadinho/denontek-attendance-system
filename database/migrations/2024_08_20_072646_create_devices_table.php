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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('school_id');
            $table->string('name', length: 50)->nullable();
            $table->string('mac_address', length: 50)->unique();
            $table->string('chip_id', length: 50)->unique();
            $table->enum('type', ['receiver', 'push_to_server', 'registeration', 'attendance'])->default('registeration');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
