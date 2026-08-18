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
        Schema::create('participant_performance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->decimal('kpi_score', 6, 2);
            $table->decimal('target_score', 6, 2)->default(100.00);
            $table->decimal('benchmark_score', 6, 2)->default(90.00);
            $table->string('performance_rating', 50)->default('Sangat Baik');
            $table->json('kpi_breakdown')->nullable();
            $table->json('achievements')->nullable();
            $table->timestamps();

            $table->index(['participant_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_performance_records');
    }
};
