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
        Schema::table('participants', function (Blueprint $table) {
            // Add indexes for search performance
            $table->index('name', 'idx_participants_name');
            $table->index('test_number', 'idx_participants_test_number');

            // Composite index for common query patterns
            $table->index(['event_id', 'position_formation_id', 'name'], 'idx_participants_event_position_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('idx_participants_name');
            $table->dropIndex('idx_participants_test_number');
            $table->dropIndex('idx_participants_event_position_name');
        });
    }
};
