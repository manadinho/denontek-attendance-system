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
        Schema::create('file_uploads', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('school_id');
            $table->string('file_name', 255)->nullable();
            $table->enum('status', ['uploaded', 'completed', 'failed'])->default('uploaded');
            $table->integer('total_records');
            $table->integer('good');
            $table->integer('cannot_upload');
            $table->string('type', 255)->nullable();
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');
    }
};
