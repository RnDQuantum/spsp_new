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
        Schema::create('aspect_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_assessment_id')->constrained('category_assessments')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('assessment_events')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('batches')->nullOnDelete();
            $table->foreignId('position_formation_id')->nullable()->constrained('position_formations')->nullOnDelete();
            $table->foreignId('aspect_id')->constrained('aspects')->cascadeOnDelete();
            $table->decimal('standard_rating', 5, 2);
            $table->decimal('standard_score', 8, 2);
            $table->decimal('individual_rating', 5, 2);
            $table->decimal('individual_score', 8, 2);
            $table->decimal('gap_rating', 8, 2);
            $table->decimal('gap_score', 8, 2);
            $table->integer('percentage_score')->nullable();
            $table->string('conclusion_code')->nullable();
            $table->string('conclusion_text')->nullable();
            $table->text('description_text')->nullable();
            $table->timestamps();

            $table->index('category_assessment_id');
            $table->index('aspect_id');
            $table->index(['event_id', 'aspect_id'], 'idx_asp_event_aspect');
            $table->index(['batch_id', 'aspect_id'], 'idx_asp_batch_aspect');
            $table->index(['position_formation_id', 'aspect_id'], 'idx_asp_position_aspect');
            $table->index(['participant_id', 'aspect_id'], 'idx_asp_participant_aspect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aspect_assessments');
    }
};
