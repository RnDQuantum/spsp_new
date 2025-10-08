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
            $table->foreignId('event_id')->constrained('assessment_events')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('position_formation_id')->nullable()->constrained('position_formations')->nullOnDelete();
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
            $table->index(['event_id', 'achievement_percentage'], 'idx_final_event_achievement');
            $table->index(['batch_id', 'achievement_percentage'], 'idx_final_batch_achievement');
            $table->index(['position_formation_id', 'achievement_percentage'], 'idx_final_position_achievement');
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
