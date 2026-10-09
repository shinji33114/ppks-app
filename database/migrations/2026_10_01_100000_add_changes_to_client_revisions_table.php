<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_revisions')) {
            return;
        }

        if (! Schema::hasColumn('client_revisions', 'changes')) {
            Schema::table('client_revisions', function (Blueprint $table) {
                $table->json('changes')->nullable();
            });
        }

        if (! Schema::hasColumn('client_revisions', 'perubahan')) {
            return;
        }

        DB::table('client_revisions')
            ->whereNull('changes')
            ->whereNotNull('perubahan')
            ->orderBy('id')
            ->chunkById(100, function ($revisions) {
                foreach ($revisions as $revision) {
                    $changes = json_decode($revision->perubahan, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($changes)) {
                        DB::table('client_revisions')
                            ->where('id', $revision->id)
                            ->update(['changes' => json_encode($changes)]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('client_revisions') && Schema::hasColumn('client_revisions', 'changes')) {
            Schema::table('client_revisions', function (Blueprint $table) {
                $table->dropColumn('changes');
            });
        }
    }
};