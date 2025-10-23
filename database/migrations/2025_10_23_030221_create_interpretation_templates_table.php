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
        Schema::create('interpretation_templates', function (Blueprint $table) {
            $table->id();

            // Polymorphic relation to sub_aspects or aspects
            $table->enum('interpretable_type', ['sub_aspect', 'aspect'])
                ->comment('Type: sub_aspect for Potensi detail, aspect for Kompetensi');
            $table->unsignedBigInteger('interpretable_id')->nullable()
                ->comment('FK to sub_aspects.id or aspects.id (nullable for name-based matching)');
            $table->string('interpretable_name')->nullable()
                ->comment('Name of sub_aspect or aspect for flexible matching across templates');

            // Rating context (1-5)
            $table->tinyInteger('rating_value')->unsigned()
                ->comment('Rating value 1-5 that triggers this template');

            // Template text
            $table->text('template_text')
                ->comment('Interpretation narrative template');

            // Metadata for categorization
            $table->enum('tone', ['positive', 'neutral', 'negative'])->default('neutral')
                ->comment('Tone of the interpretation');
            $table->enum('category', ['strength', 'development_area', 'neutral'])->default('neutral')
                ->comment('Classification of interpretation');

            // Version control
            $table->string('version', 10)->default('1.0')
                ->comment('Template version for A/B testing');
            $table->boolean('is_active')->default(true)
                ->comment('Only active templates are used');

            $table->timestamps();

            // Indexes for performance
            $table->index(['interpretable_type', 'interpretable_id'], 'idx_interpretable');
            $table->index(['interpretable_type', 'interpretable_name', 'rating_value'], 'idx_type_name_rating');
            $table->index('rating_value', 'idx_rating');
            $table->index('is_active', 'idx_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interpretation_templates');
    }
};
