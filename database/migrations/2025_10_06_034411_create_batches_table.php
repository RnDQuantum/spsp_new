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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('assessment_events')->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->string('location');
            $table->integer('batch_number');
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();

            $table->index('event_id');
            $table->unique(['event_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
