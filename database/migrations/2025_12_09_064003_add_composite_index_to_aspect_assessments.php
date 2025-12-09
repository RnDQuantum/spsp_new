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
        Schema::table('aspect_assessments', function (Blueprint $table) {
            $table->index(
                ['event_id', 'position_formation_id', 'aspect_id', 'participant_id'],
                'idx_asp_event_pos_aspect_participant'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspect_assessments', function (Blueprint $table) {
            $table->dropIndex('idx_asp_event_pos_aspect_participant');
        });
    }
};
