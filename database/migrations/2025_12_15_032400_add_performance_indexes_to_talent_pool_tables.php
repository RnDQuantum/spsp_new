<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 🚀 PERFORMANCE: Add critical indexes for Talent Pool performance optimization
     * These indexes will significantly improve query performance for large datasets (5000+ participants)
     */
    public function up(): void
    {
        Schema::table('aspect_assessments', function (Blueprint $table) {
            // Critical composite index for the main query in TalentPoolService
            $table->index(['event_id', 'position_formation_id', 'participant_id'], 'idx_talent_pool_main_query');

            // Additional index for category-based filtering
            $table->index(['event_id', 'position_formation_id', 'aspect_id'], 'idx_talent_pool_category_filter');
        });

        Schema::table('aspects', function (Blueprint $table) {
            // Index for category and template lookups
            $table->index(['category_type_id', 'template_id'], 'idx_aspects_category_template');

            // Index for code-based lookups
            $table->index(['template_id', 'code'], 'idx_aspects_template_code');
        });

        Schema::table('category_types', function (Blueprint $table) {
            // Index for template-based category filtering
            $table->index(['template_id', 'code'], 'idx_category_types_template_code');
        });

        Schema::table('participants', function (Blueprint $table) {
            // Index for participant lookups in assessments
            $table->index(['id', 'test_number'], 'idx_participants_lookup');
        });

        Schema::table('position_formations', function (Blueprint $table) {
            // Index for template-based position lookups
            $table->index(['template_id', 'id'], 'idx_position_formations_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aspect_assessments', function (Blueprint $table) {
            $table->dropIndex('idx_talent_pool_main_query');
            $table->dropIndex('idx_talent_pool_category_filter');
        });

        Schema::table('aspects', function (Blueprint $table) {
            $table->dropIndex('idx_aspects_category_template');
            $table->dropIndex('idx_aspects_template_code');
        });

        Schema::table('category_types', function (Blueprint $table) {
            $table->dropIndex('idx_category_types_template_code');
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropIndex('idx_participants_lookup');
        });

        Schema::table('position_formations', function (Blueprint $table) {
            $table->dropIndex('idx_position_formations_template_id');
        });
    }
};
