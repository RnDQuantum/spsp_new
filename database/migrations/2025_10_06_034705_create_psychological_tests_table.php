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
        Schema::create('psychological_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->unique()->constrained('participants')->cascadeOnDelete();
            $table->decimal('raw_score', 5, 2);
            $table->integer('iq_score')->nullable();
            $table->string('validity_status');
            $table->string('internal_status');
            $table->string('interpersonal_status');
            $table->string('work_capacity_status');
            $table->string('clinical_status');
            $table->string('conclusion_code');
            $table->string('conclusion_text');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('conclusion_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('psychological_tests');
    }
};
