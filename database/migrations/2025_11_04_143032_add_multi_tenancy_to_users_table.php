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
        Schema::table('users', function (Blueprint $table) {
            // Multi-tenancy
            $table->foreignId('institution_id')
                ->nullable()
                ->after('id')
                ->constrained('institutions')
                ->nullOnDelete();

            // User management
            $table->boolean('is_active')->default(true)->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            // Indexes
            $table->index('institution_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropIndex(['institution_id']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['institution_id', 'is_active', 'last_login_at']);
        });
    }
};
