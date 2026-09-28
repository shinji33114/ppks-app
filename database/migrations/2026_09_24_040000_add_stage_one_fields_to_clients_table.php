<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('tempat_kejadian')->nullable()->after('sumber_aduan');
            $table->text('kondisi_fisik')->nullable()->after('tkp_kronologi');
            $table->text('problem_awal')->nullable()->after('kondisi_fisik');
            $table->string('data_dukung')->nullable()->after('foto_klien');
            $table->string('nama_alias')->nullable()->after('nama_lengkap');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nama_alias');
            $table->unsignedSmallInteger('umur')->nullable()->after('jenis_kelamin');
            $table->text('alamat')->nullable()->after('umur');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'tempat_kejadian',
                'kondisi_fisik',
                'problem_awal',
                'data_dukung',
                'nama_alias',
                'jenis_kelamin',
                'umur',
                'alamat',
            ]);
        });
    }
};
