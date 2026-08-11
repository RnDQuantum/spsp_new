<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('psychological_tests') && ! Schema::hasTable('mmpi')) {
            Schema::rename('psychological_tests', 'mmpi');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('mmpi') && ! Schema::hasTable('psychological_tests')) {
            Schema::rename('mmpi', 'psychological_tests');
        }
    }
};
