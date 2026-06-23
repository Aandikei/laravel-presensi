<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('export_jobs', function (Blueprint $table) {
            $table->id('id_export');
            $table->foreignId('user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->string('type'); // absensi-excel, absensi-pdf, poin-excel, poin-pdf
            $table->json('filters')->nullable();
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('filename')->nullable();
            $table->string('filepath')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('export_jobs');
    }
};
