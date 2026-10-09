<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('client_revisions') && Schema::hasColumn('client_revisions', 'perubahan')) {
            Schema::table('client_revisions', function (Blueprint $table) {
                $table->longText('perubahan')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('client_revisions') && Schema::hasColumn('client_revisions', 'perubahan')) {
            Schema::table('client_revisions', function (Blueprint $table) {
                $table->longText('perubahan')->nullable(false)->change();
            });
        }
    }
};