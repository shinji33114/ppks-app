<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Dokumen field 6 ("Problem Awal / Bentuk Pelanggaran") adalah teks bebas.
 * Daftar kategori yang sebelumnya dipakai sebagai isi problem_awal (dan dipakai
 * untuk grafik breakdown di halaman Statistik) dipindah ke kolom terpisah ini,
 * supaya problem_awal bisa dikembalikan jadi teks bebas tanpa merusak grafik.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Guard: nama file migrasi ini diperbaiki (sebelumnya ada spasi), jadi aman dijalankan ulang.
        if (! Schema::hasColumn('clients', 'kategori_ppks')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('kategori_ppks')->nullable()->after('problem_awal');
            });
        }

        // Migrasikan data lama: nilai problem_awal yang cocok dengan daftar kategori
        // dipindah ke kategori_ppks, sisanya (kalau ada uraian bebas) dibiarkan di problem_awal.
        $kategoriList = [
            'Lansia Terlantar', 'Anak Jalanan', 'Anak Terlantar', 'Disabilitas Terlantar',
            'Korban Kekerasan', 'Gelandangan / Pengemis', 'ODGJ Terlantar', 'Lainnya',
        ];

        DB::table('clients')
            ->whereIn('problem_awal', $kategoriList)
            ->update(['kategori_ppks' => DB::raw('problem_awal')]);
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('kategori_ppks');
        });
    }
};