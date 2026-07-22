<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_poin_siswa', function (Blueprint $table) {
            $table->unsignedBigInteger('instansi_id')->nullable()->after('id_log_poin');
            $table->foreign('instansi_id')->references('id_instansi')->on('instansi')->nullOnDelete();
        });

        DB::statement('UPDATE log_poin_siswa lps JOIN master_poin mp ON lps.poin_id = mp.id_poin SET lps.instansi_id = mp.instansi_id');
    }

    public function down(): void
    {
        Schema::table('log_poin_siswa', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
            $table->dropColumn('instansi_id');
        });
    }
};
