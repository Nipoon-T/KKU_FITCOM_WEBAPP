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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('sport_id')->constrained('sports');
            $table->unsignedBigInteger('community_id')->nullable();
            $table->foreignId('location_id')->constrained('locations');
            $table->unsignedTinyInteger('skill_level')->default(1);
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedInteger('max_participants');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
