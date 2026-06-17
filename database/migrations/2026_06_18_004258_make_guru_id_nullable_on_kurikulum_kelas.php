<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kurikulum_kelas', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
            $table->unsignedBigInteger('guru_id')->nullable()->change();
            $table->foreign('guru_id')->references('id_guru')->on('guru')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kurikulum_kelas', function (Blueprint $table) {
            $table->dropForeign(['guru_id']);
        });

        // Hapus data yang guru_id-nya null sebelum balikin NOT NULL
        DB::table('kurikulum_kelas')->whereNull('guru_id')->delete();

        Schema::table('kurikulum_kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('guru_id')->nullable(false)->change();
            $table->foreign('guru_id')->references('id_guru')->on('guru')->cascadeOnDelete();
        });
    }
};
