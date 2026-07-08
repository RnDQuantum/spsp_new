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
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('event_id')->constrained('assessment_events')->cascadeOnDelete();

            // Identitas alat tes (dari API Quantum)
            $table->string('test_code', 50)->comment('Kode alat tes dari API, e.g. A.1, B.2, D.2');
            $table->string('test_name', 255)->comment('Nama alat tes, e.g. Typical CFIT3A');
            $table->string('test_category', 100)->comment('Kategori tes, e.g. Kecerdasan / IQ');

            // Metadata tes
            $table->string('status', 20)->default('completed');
            $table->timestamp('test_started_at')->nullable()->comment('Waktu mulai tes (mulai_tes dari API)');

            // Data tes — dipecah untuk efisiensi query & storage
            $table->json('summary_data')->comment('Skor ringkasan: IQ, kategori, hasil aspek, kesimpulan');
            $table->json('interpretation_data')->nullable()->comment('Narasi interpretasi & saran pengembangan');
            $table->json('raw_response')->comment('Response utuh dari API (backup, tanpa detail Kraeplin)');

            // Status konversi ke rating SPSP (untuk tahap 2)
            $table->enum('conversion_status', ['pending', 'converted', 'skipped', 'not_applicable'])
                  ->default('pending')
                  ->comment('Status konversi ke rating 1-5 SPSP');
            $table->timestamp('converted_at')->nullable();

            $table->timestamps();

            // 1 peserta + 1 event + 1 alat tes = 1 record (idempotent)
            $table->unique(['participant_id', 'event_id', 'test_code'], 'test_results_unique');

            // Index untuk query umum
            $table->index(['event_id', 'test_code'], 'test_results_event_code');
            $table->index('conversion_status', 'test_results_conversion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
