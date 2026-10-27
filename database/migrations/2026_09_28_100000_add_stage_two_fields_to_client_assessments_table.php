<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * B1 - Melengkapi 11 field Tahap 2 yang ada di dokumen spesifikasi
 * tetapi belum ada di database. Semua kolom nullable dan hanya MENAMBAH,
 * sehingga data assessment yang sudah ada tidak berubah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_assessments', function (Blueprint $table) {
            // Identitas
            $table->text('detail_alamat_pengakuan')->nullable()->after('alamat_domisili');   // field 15
            $table->string('status_pendidikan')->nullable()->after('detail_alamat_pengakuan'); // field 16
            $table->string('kategori_umur')->nullable()->after('status_pendidikan');          // field 34

            // Psikososial
            $table->text('masalah_sekolah')->nullable()->after('riwayat_trauma');             // field 20
            $table->text('masalah_sosial')->nullable()->after('masalah_sekolah');             // field 21
            $table->text('cara_pandang_klien')->nullable()->after('masalah_sosial');          // field 22
            $table->text('rencana_kedepan')->nullable()->after('cara_pandang_klien');         // field 23

            // Observasi status mental
            $table->string('cara_komunikasi')->nullable()->after('kondisi_emosi');            // field 24
            $table->string('arus_percakapan')->nullable()->after('cara_komunikasi');          // field 25
            $table->string('ingatan')->nullable()->after('arus_percakapan');                  // field 26
            $table->string('arah_perilaku')->nullable()->after('ingatan');                    // field 28
        });
    }

    public function down(): void
    {
        Schema::table('client_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'detail_alamat_pengakuan', 'status_pendidikan', 'kategori_umur',
                'masalah_sekolah', 'masalah_sosial', 'cara_pandang_klien', 'rencana_kedepan',
                'cara_komunikasi', 'arus_percakapan', 'ingatan', 'arah_perilaku',
            ]);
        });
    }
};