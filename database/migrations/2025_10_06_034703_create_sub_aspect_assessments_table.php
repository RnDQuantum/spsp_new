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
        Schema::create('sub_aspect_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspect_assessment_id')->constrained('aspect_assessments')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('assessment_events')->cascadeOnDelete();
            $table->foreignId('sub_aspect_id')->constrained('sub_aspects')->cascadeOnDelete();
            $table->integer('standard_rating');
            $table->integer('individual_rating');
            $table->string('rating_label');
            $table->timestamps();

            $table->index('aspect_assessment_id');
            $table->index('sub_aspect_id');
            $table->index(['event_id', 'sub_aspect_id'], 'idx_sub_event_subaspect');
            $table->index(['participant_id', 'sub_aspect_id'], 'idx_sub_participant_subaspect');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_aspect_assessments');
    }
};
