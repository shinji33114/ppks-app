<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('client_exit_documents', 'pdf_path')) {
            Schema::table('client_exit_documents', function (Blueprint $table) {
                $table->string('pdf_path')->nullable()->after('no_hp_penanggung_jawab');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('client_exit_documents', 'pdf_path')) {
            Schema::table('client_exit_documents', function (Blueprint $table) {
                $table->dropColumn('pdf_path');
            });
        }
    }
};