<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_assessments', function (Blueprint $table) {
            $table->unsignedSmallInteger('umur')->nullable()->after('tanggal_lahir');           // field 13
            $table->text('kondisi_ekonomi')->nullable()->after('kepemilikan_bansos');           // field 18
            $table->text('masalah_keluarga')->nullable()->after('riwayat_trauma');              // field 19
        });
    }

    public function down(): void
    {
        Schema::table('client_assessments', function (Blueprint $table) {
            $table->dropColumn(['umur', 'kondisi_ekonomi', 'masalah_keluarga']);
        });
    }
};