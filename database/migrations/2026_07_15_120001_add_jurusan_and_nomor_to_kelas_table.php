<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('jurusan_id')->nullable()->after('guru_wali_id');
            $table->string('nomor_kelas', 10)->nullable()->after('jurusan_id');

            $table->foreign('jurusan_id')->references('id_jurusan')->on('jurusan')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['jurusan_id']);
            $table->dropColumn(['jurusan_id', 'nomor_kelas']);
        });
    }
};
