<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_exit_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['surat_keluar', 'rujukan']);
            $table->enum('status', ['menunggu_persetujuan', 'disetujui', 'ditolak'])
                ->default('menunggu_persetujuan');
            $table->string('nomor_surat')->nullable();
            $table->date('tanggal_surat');
            $table->string('tujuan');
            $table->text('keterangan')->nullable();
            $table->date('tanggal_keluar');
            $table->string('no_hp_penanggung_jawab', 20);
            $table->text('catatan_penolakan')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_exit_documents');
    }
};
