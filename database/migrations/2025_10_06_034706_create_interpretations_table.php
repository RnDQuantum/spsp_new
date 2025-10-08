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
        Schema::create('interpretations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('assessment_events')->cascadeOnDelete();
            $table->foreignId('category_type_id')->nullable()->constrained('category_types')->cascadeOnDelete();
            $table->text('interpretation_text');
            $table->timestamps();

            $table->index('participant_id');
            $table->index('category_type_id');
            $table->index(['event_id', 'category_type_id'], 'idx_interp_event_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interpretations');
    }
};
