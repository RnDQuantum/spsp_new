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
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('participant_id');
            $table->string('kode_proyek', 50)->nullable();
            $table->string('no_test', 30)->nullable();
            $table->string('username', 100)->nullable();
            $table->text('validitas')->nullable();
            $table->text('internal')->nullable();
            $table->text('interpersonal')->nullable();
            $table->text('kap_kerja')->nullable()->comment('Kapasitas Kerja');
            $table->text('klinik')->nullable();
            $table->text('kesimpulan')->nullable();
            $table->text('psikogram')->nullable();
            $table->decimal('nilai_pq', 10, 2)->nullable()->comment('Nilai Psychological Quotient');
            $table->string('tingkat_stres', 20)->nullable();
            $table->timestamps();

            $table->index(['event_id', 'participant_id', 'kode_proyek']);
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
