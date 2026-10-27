<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_exit_documents', function (Blueprint $table) {
            $table->enum('status', ['menunggu_persetujuan', 'disetujui', 'ditolak', 'selesai'])
                ->default('menunggu_persetujuan')
                ->change();
        });

        DB::table('client_exit_documents')
            ->where('status', 'menunggu_persetujuan')
            ->update(['status' => 'selesai']);
    }

    public function down(): void
    {
        DB::table('client_exit_documents')
            ->where('status', 'selesai')
            ->update(['status' => 'disetujui']);

        Schema::table('client_exit_documents', function (Blueprint $table) {
            $table->enum('status', ['menunggu_persetujuan', 'disetujui', 'ditolak'])
                ->default('menunggu_persetujuan')
                ->change();
        });
    }
};
