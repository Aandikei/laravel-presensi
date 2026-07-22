<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('guru', 'foto')) {
            Schema::table('guru', fn (Blueprint $t) => $t->dropColumn('foto'));
        }
        if (Schema::hasColumn('siswa', 'foto')) {
            Schema::table('siswa', fn (Blueprint $t) => $t->dropColumn('foto'));
        }
        if (Schema::hasColumn('instansi', 'logo')) {
            Schema::table('instansi', fn (Blueprint $t) => $t->dropColumn('logo'));
        }
    }

    public function down(): void
    {
        Schema::table('guru', fn (Blueprint $t) => $t->string('foto')->nullable()->after('no_hp'));
        Schema::table('siswa', fn (Blueprint $t) => $t->string('foto')->nullable()->after('tanggal_lahir'));
        Schema::table('instansi', fn (Blueprint $t) => $t->string('logo')->nullable()->after('email'));
    }
};
