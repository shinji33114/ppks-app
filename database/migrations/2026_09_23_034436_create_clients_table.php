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
    Schema::create('clients', function (Blueprint $table) {
        $table->id();

        // Status alur — ini yang mengontrol posisi klien di 5 tahap flowchart
        $table->enum('status', ['draft', 'assessment', 'penanganan', 'selesai'])
              ->default('draft');

        // 5 field wajib Tahap 1 (Petugas Piket)
        $table->date('tanggal_masuk');
        $table->string('sumber_aduan');
        $table->text('tkp_kronologi');
        $table->string('nama_lengkap')->default('Tanpa Nama / X');
        $table->string('foto_klien')->nullable();

        // Untuk aturan kunci data 1x24 jam
        $table->timestamp('locked_at')->nullable();

        $table->timestamps(); // otomatis bikin kolom created_at & updated_at
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
