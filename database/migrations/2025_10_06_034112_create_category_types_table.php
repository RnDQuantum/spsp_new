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
        Schema::create('category_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('assessment_templates')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->integer('weight_percentage');
            $table->integer('order');
            $table->timestamps();

            $table->index('template_id');
            $table->unique(['template_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_types');
    }
};
