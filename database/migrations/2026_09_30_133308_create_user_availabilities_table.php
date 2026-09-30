<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('day_of_week');
            $table->string('time_slot');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_availabilities');
    }
};
