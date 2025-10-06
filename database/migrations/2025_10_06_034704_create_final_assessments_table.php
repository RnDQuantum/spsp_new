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
        Schema::create('final_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->unique()->constrained('participants')->cascadeOnDelete();
            $table->integer('potensi_weight');
            $table->decimal('potensi_standard_score', 8, 2);
            $table->decimal('potensi_individual_score', 8, 2);
            $table->integer('kompetensi_weight');
            $table->decimal('kompetensi_standard_score', 8, 2);
            $table->decimal('kompetensi_individual_score', 8, 2);
            $table->decimal('total_standard_score', 8, 2);
            $table->decimal('total_individual_score', 8, 2);
            $table->decimal('achievement_percentage', 5, 2);
            $table->string('conclusion_code');
            $table->string('conclusion_text');
            $table->timestamps();

            $table->index('conclusion_code');
            $table->index('achievement_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('final_assessments');
    }
};
