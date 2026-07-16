<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add nullable columns first
        Schema::table('batches', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('event_id')->constrained('institutions')->cascadeOnDelete();
        });

        Schema::table('position_formations', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('event_id')->constrained('institutions')->cascadeOnDelete();
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('event_id')->constrained('institutions')->cascadeOnDelete();
        });

        // 2. Populate the data from parent events in a database-agnostic way (using subqueries)
        DB::table('batches')->update([
            'institution_id' => DB::table('assessment_events')
                ->whereColumn('assessment_events.id', 'batches.event_id')
                ->select('institution_id')
        ]);

        DB::table('position_formations')->update([
            'institution_id' => DB::table('assessment_events')
                ->whereColumn('assessment_events.id', 'position_formations.event_id')
                ->select('institution_id')
        ]);

        DB::table('participants')->update([
            'institution_id' => DB::table('assessment_events')
                ->whereColumn('assessment_events.id', 'participants.event_id')
                ->select('institution_id')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });

        Schema::table('position_formations', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });

        Schema::table('batches', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn('institution_id');
        });
    }
};
