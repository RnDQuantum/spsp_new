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
            $table->string('tempat_lahir', 100)->nullable()->after('name');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('gelar_depan', 50)->nullable()->after('tanggal_lahir');
            $table->string('gelar_belakang', 50)->nullable()->after('gelar_depan');
            $table->string('pendidikan', 50)->nullable()->after('gelar_belakang');
            $table->string('agama', 50)->nullable()->after('pendidikan');
            $table->string('status_perkawinan', 50)->nullable()->after('agama');
            $table->string('nik', 20)->nullable()->after('skb_number');
            $table->string('no_kjg', 30)->nullable()->after('nik');
            $table->string('jabatan_pelaksana', 100)->nullable()->after('no_kjg');
            $table->string('jbt_fungsional', 100)->nullable()->after('jabatan_pelaksana');
            $table->string('jbt_struktural', 100)->nullable()->after('jbt_fungsional');
            $table->string('pangkat', 50)->nullable()->after('jbt_struktural');
            $table->string('golongan', 20)->nullable()->after('pangkat');
            $table->string('status_kepegawaian', 50)->nullable()->after('golongan');
            $table->string('unit_kerja', 255)->nullable()->after('status_kepegawaian');
            $table->string('minat_penempatan', 255)->nullable()->after('unit_kerja');
            $table->string('pengalaman_kerja', 255)->nullable()->after('minat_penempatan');
        });

        Schema::table('position_formations', function (Blueprint $table) {
            $table->string('level_jabatan', 50)->nullable()->after('name');
            $table->text('description')->nullable()->after('level_jabatan');
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->string('address', 255)->nullable()->after('name');
            $table->string('phone', 50)->nullable()->after('address');
            $table->string('pic_name', 100)->nullable()->after('phone');
            $table->string('pic_phone', 50)->nullable()->after('pic_name');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('pic_name', 100)->nullable()->after('contract_number');
            $table->string('pic_phone', 50)->nullable()->after('pic_name');
            $table->string('project_type', 50)->nullable()->after('pic_phone');
        });

        Schema::table('assessment_events', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->after('description');
            $table->integer('target_participants')->nullable()->after('location');
            $table->string('assessment_type', 50)->nullable()->after('target_participants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_lahir',
                'tanggal_lahir',
                'gelar_depan',
                'gelar_belakang',
                'pendidikan',
                'agama',
                'status_perkawinan',
                'nik',
                'no_kjg',
                'jabatan_pelaksana',
                'jbt_fungsional',
                'jbt_struktural',
                'pangkat',
                'golongan',
                'status_kepegawaian',
                'unit_kerja',
                'minat_penempatan',
                'pengalaman_kerja',
            ]);
        });

        Schema::table('position_formations', function (Blueprint $table) {
            $table->dropColumn(['level_jabatan', 'description']);
        });

        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['address', 'phone', 'pic_name', 'pic_phone']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['pic_name', 'pic_phone', 'project_type']);
        });

        Schema::table('assessment_events', function (Blueprint $table) {
            $table->dropColumn(['location', 'target_participants', 'assessment_type']);
        });
    }
};
