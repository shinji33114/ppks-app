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
    Schema::create('client_assessments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('client_id')->constrained()->onDelete('cascade');

        // A. Identitas Lanjutan
        $table->string('nik', 16)->nullable();
        $table->string('tempat_lahir')->nullable();
        $table->date('tanggal_lahir')->nullable();
        $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
        $table->text('alamat_domisili')->nullable();
        $table->string('status_perkawinan')->nullable();
        $table->string('pendidikan_terakhir')->nullable();

        // B. Kondisi Ekonomi
        $table->string('pekerjaan')->nullable();
        $table->string('sumber_penghasilan')->nullable();
        $table->string('kepemilikan_bansos')->nullable();
        $table->text('kondisi_tempat_tinggal')->nullable();

        // C. Kondisi Psikososial
        $table->text('riwayat_masalah');
        $table->enum('relasi_keluarga', ['baik', 'kurang_baik', 'terputus'])->nullable();
        $table->text('riwayat_trauma')->nullable();
        $table->text('dukungan_sosial')->nullable();

        // D. Observasi Kondisi Mental
        $table->string('kondisi_emosi')->nullable();
        $table->enum('orientasi_realita', ['baik', 'terganggu'])->nullable();
        $table->text('catatan_observasi')->nullable();

        // E. Simpulan & Rekomendasi
        $table->text('simpulan_awal');
        $table->text('rekomendasi_penanganan');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_assessments');
    }
};
