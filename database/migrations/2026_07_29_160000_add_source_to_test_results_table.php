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
        if (Schema::hasTable('test_results') && ! Schema::hasColumn('test_results', 'source')) {
            Schema::table('test_results', function (Blueprint $table) {
                $table->string('source', 50)->default('api')->after('status')->index()->comment('Asal sumber data mentah: api, lsp_db, file_import');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('test_results') && Schema::hasColumn('test_results', 'source')) {
            Schema::table('test_results', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }
};
