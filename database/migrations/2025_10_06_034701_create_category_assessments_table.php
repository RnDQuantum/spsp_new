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
        Schema::create('category_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('category_type_id')->constrained('category_types')->cascadeOnDelete();
            $table->decimal('total_standard_rating', 8, 2);
            $table->decimal('total_standard_score', 8, 2);
            $table->decimal('total_individual_rating', 8, 2);
            $table->decimal('total_individual_score', 8, 2);
            $table->decimal('gap_rating', 8, 2);
            $table->decimal('gap_score', 8, 2);
            $table->string('conclusion_code');
            $table->string('conclusion_text');
            $table->timestamps();

            $table->unique(['participant_id', 'category_type_id']);
            $table->index('category_type_id');
            $table->index('conclusion_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_assessments');
    }
};
