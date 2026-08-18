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
        Schema::create('participant_personal_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->string('blood_type', 10)->default('O+');
            $table->string('hobbies')->nullable();
            $table->string('sports')->nullable();
            $table->string('zodiac', 50)->nullable();
            $table->string('chinese_zodiac', 50)->nullable();
            $table->string('weton', 50)->nullable();
            $table->text('medical_notes')->nullable();
            $table->text('cultural_notes')->nullable();
            $table->text('motto_or_values')->nullable();
            $table->timestamps();

            $table->unique('participant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_personal_profiles');
    }
};
