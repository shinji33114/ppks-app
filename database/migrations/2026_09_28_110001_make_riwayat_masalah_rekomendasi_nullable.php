<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // B3: hanya simpulan_awal yang wajib, jadi dua kolom ini harus boleh kosong.
        Schema::table('client_assessments', function (Blueprint $table) {
            $table->text('riwayat_masalah')->nullable()->change();
            $table->text('rekomendasi_penanganan')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Catatan: rollback ini akan gagal kalau sudah ada baris yang kosong.
        Schema::table('client_assessments', function (Blueprint $table) {
            $table->text('riwayat_masalah')->nullable(false)->change();
            $table->text('rekomendasi_penanganan')->nullable(false)->change();
        });
    }
};