<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->unsignedBigInteger('instansi_tujuan_id')->nullable()->after('foto');
            $table->string('transfer_token', 6)->nullable()->after('instansi_tujuan_id');
            $table->dateTime('transfer_token_expires_at')->nullable()->after('transfer_token');
            $table->index('transfer_token');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropIndex(['transfer_token']);
            $table->dropColumn('transfer_token_expires_at');
            $table->dropColumn('transfer_token');
            $table->dropColumn('instansi_tujuan_id');
        });
    }
};
