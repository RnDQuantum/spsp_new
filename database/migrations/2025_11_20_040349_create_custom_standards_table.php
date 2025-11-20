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
        Schema::create('custom_standards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('template_id')->constrained('assessment_templates')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();

            // JSON storage for all configurations
            $table->json('category_weights');
            $table->json('aspect_configs');
            $table->json('sub_aspect_configs');

            // Metadata
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->unique(['institution_id', 'code']);
            $table->index('template_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_standards');
    }
};
