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
        Schema::table('participant_personal_profiles', function (Blueprint $table) {
            $table->string('succession_target_role')->nullable()->after('motto_or_values');
            $table->string('readiness_horizon', 50)->nullable()->after('succession_target_role'); // 'ready_now', '1_year', '2_year'
            $table->unsignedTinyInteger('readiness_percentage')->nullable()->after('readiness_horizon');
            $table->text('succession_notes')->nullable()->after('readiness_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participant_personal_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'succession_target_role',
                'readiness_horizon',
                'readiness_percentage',
                'succession_notes',
            ]);
        });
    }
};
