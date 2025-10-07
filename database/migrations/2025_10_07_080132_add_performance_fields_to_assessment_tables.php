<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERFORMANCE OPTIMIZATION for 2000+ participants per event
     * Adding denormalized fields to prevent expensive JOINs in analytics queries
     */
    public function up(): void
    {
        // 1. category_assessments: Add event_id, batch_id, position_formation_id
        Schema::table('category_assessments', function (Blueprint $table) {
            $table->foreignId('event_id')->after('participant_id')
                ->constrained('assessment_events')->cascadeOnDelete();
            $table->foreignId('batch_id')->after('event_id')->nullable()
                ->constrained('batches')->nullOnDelete();
            $table->foreignId('position_formation_id')->after('batch_id')->nullable()
                ->constrained('position_formations')->nullOnDelete();

            // Composite indexes for analytics
            $table->index(['event_id', 'category_type_id'], 'idx_cat_event_type');
            $table->index(['batch_id', 'category_type_id'], 'idx_cat_batch_type');
            $table->index(['position_formation_id', 'category_type_id'], 'idx_cat_position_type');
        });

        // 2. aspect_assessments: Add event_id, batch_id, position_formation_id, participant_id
        Schema::table('aspect_assessments', function (Blueprint $table) {
            $table->foreignId('participant_id')->after('category_assessment_id')
                ->constrained('participants')->cascadeOnDelete();
            $table->foreignId('event_id')->after('participant_id')
                ->constrained('assessment_events')->cascadeOnDelete();
            $table->foreignId('batch_id')->after('event_id')->nullable()
                ->constrained('batches')->nullOnDelete();
            $table->foreignId('position_formation_id')->after('batch_id')->nullable()
                ->constrained('position_formations')->nullOnDelete();

            // Composite indexes for analytics
            $table->index(['event_id', 'aspect_id'], 'idx_asp_event_aspect');
            $table->index(['batch_id', 'aspect_id'], 'idx_asp_batch_aspect');
            $table->index(['position_formation_id', 'aspect_id'], 'idx_asp_position_aspect');
            $table->index(['participant_id', 'aspect_id'], 'idx_asp_participant_aspect');
        });

        // 3. sub_aspect_assessments: Add participant_id, event_id
        Schema::table('sub_aspect_assessments', function (Blueprint $table) {
            $table->foreignId('participant_id')->after('aspect_assessment_id')
                ->constrained('participants')->cascadeOnDelete();
            $table->foreignId('event_id')->after('participant_id')
                ->constrained('assessment_events')->cascadeOnDelete();

            // Indexes for filtering
            $table->index(['event_id', 'sub_aspect_id'], 'idx_sub_event_subaspect');
            $table->index(['participant_id', 'sub_aspect_id'], 'idx_sub_participant_subaspect');
        });

        // 4. final_assessments: Add event_id, batch_id, position_formation_id
        Schema::table('final_assessments', function (Blueprint $table) {
            $table->foreignId('event_id')->after('participant_id')
                ->constrained('assessment_events')->cascadeOnDelete();
            $table->foreignId('batch_id')->after('event_id')->nullable()
                ->constrained('batches')->nullOnDelete();
            $table->foreignId('position_formation_id')->after('batch_id')->nullable()
                ->constrained('position_formations')->nullOnDelete();

            // Indexes for ranking/leaderboard
            $table->index(['event_id', 'achievement_percentage'], 'idx_final_event_achievement');
            $table->index(['batch_id', 'achievement_percentage'], 'idx_final_batch_achievement');
            $table->index(['position_formation_id', 'achievement_percentage'], 'idx_final_position_achievement');
        });

        // 5. psychological_tests: Add event_id
        Schema::table('psychological_tests', function (Blueprint $table) {
            $table->foreignId('event_id')->after('participant_id')
                ->constrained('assessment_events')->cascadeOnDelete();

            // Index for filtering
            $table->index(['event_id', 'conclusion_code'], 'idx_psych_event_conclusion');
        });

        // 6. interpretations: Add event_id
        Schema::table('interpretations', function (Blueprint $table) {
            $table->foreignId('event_id')->after('participant_id')
                ->constrained('assessment_events')->cascadeOnDelete();

            // Index for filtering
            $table->index(['event_id', 'category_type_id'], 'idx_interp_event_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to handle foreign key constraints

        Schema::table('interpretations', function (Blueprint $table) {
            $table->dropIndex('idx_interp_event_category');
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });

        Schema::table('psychological_tests', function (Blueprint $table) {
            $table->dropIndex('idx_psych_event_conclusion');
            $table->dropForeign(['event_id']);
            $table->dropColumn('event_id');
        });

        Schema::table('final_assessments', function (Blueprint $table) {
            $table->dropIndex('idx_final_position_achievement');
            $table->dropIndex('idx_final_batch_achievement');
            $table->dropIndex('idx_final_event_achievement');
            $table->dropForeign(['position_formation_id']);
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['event_id']);
            $table->dropColumn(['event_id', 'batch_id', 'position_formation_id']);
        });

        Schema::table('sub_aspect_assessments', function (Blueprint $table) {
            $table->dropIndex('idx_sub_participant_subaspect');
            $table->dropIndex('idx_sub_event_subaspect');
            $table->dropForeign(['event_id']);
            $table->dropForeign(['participant_id']);
            $table->dropColumn(['participant_id', 'event_id']);
        });

        Schema::table('aspect_assessments', function (Blueprint $table) {
            $table->dropIndex('idx_asp_participant_aspect');
            $table->dropIndex('idx_asp_position_aspect');
            $table->dropIndex('idx_asp_batch_aspect');
            $table->dropIndex('idx_asp_event_aspect');
            $table->dropForeign(['position_formation_id']);
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['event_id']);
            $table->dropForeign(['participant_id']);
            $table->dropColumn(['participant_id', 'event_id', 'batch_id', 'position_formation_id']);
        });

        Schema::table('category_assessments', function (Blueprint $table) {
            $table->dropIndex('idx_cat_position_type');
            $table->dropIndex('idx_cat_batch_type');
            $table->dropIndex('idx_cat_event_type');
            $table->dropForeign(['position_formation_id']);
            $table->dropForeign(['batch_id']);
            $table->dropForeign(['event_id']);
            $table->dropColumn(['event_id', 'batch_id', 'position_formation_id']);
        });
    }
};
