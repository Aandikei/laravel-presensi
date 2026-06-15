<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrasi_akademik', function (Blueprint $table) {
            $table->dateTime('tanggal_mutasi')->nullable()->change();
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dateTime('transfer_token_expires_at')->nullable()->after('transfer_token');
        });
    }

    public function down(): void
    {
        Schema::table('registrasi_akademik', function (Blueprint $table) {
            $table->date('tanggal_mutasi')->nullable()->change();
        });

        Schema::table('siswa', function (Blueprint $table) {
            $table->dropColumn('transfer_token_expires_at');
        });
    }
};
