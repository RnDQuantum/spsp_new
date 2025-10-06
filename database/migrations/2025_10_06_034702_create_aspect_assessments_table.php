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
            $table->foreignId('aspect_id')->constrained('aspects')->cascadeOnDelete();
            $table->decimal('standard_rating', 5, 2);
            $table->decimal('standard_score', 8, 2);
            $table->decimal('individual_rating', 5, 2);
            $table->decimal('individual_score', 8, 2);
            $table->decimal('gap_rating', 8, 2);
            $table->decimal('gap_score', 8, 2);
            $table->integer('percentage_score');
            $table->string('conclusion_code');
            $table->string('conclusion_text');
            $table->text('description_text')->nullable();
            $table->timestamps();

            $table->index('category_assessment_id');
            $table->index('aspect_id');
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
