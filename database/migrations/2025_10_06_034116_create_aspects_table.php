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
        Schema::create('aspects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_templates')->cascadeOnDelete();
            $table->foreignId('category_type_id')->constrained('category_types')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('weight_percentage')->nullable();
            $table->decimal('standard_rating', 5, 2)->nullable();
            $table->integer('order');
            $table->timestamps();

            $table->index('template_id');
            $table->index('category_type_id');
            $table->index('code');
            $table->unique(['template_id', 'category_type_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aspects');
    }
};
