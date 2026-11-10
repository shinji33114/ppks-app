<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('clients')
            ->where('status', 'assessment')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('client_assessments')
                    ->whereColumn('client_assessments.client_id', 'clients.id');
            })
            ->update(['status' => 'penanganan']);
    }

    public function down(): void
    {
        DB::table('clients')
            ->where('status', 'penanganan')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('client_followups')
                    ->whereColumn('client_followups.client_id', 'clients.id');
            })
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('client_assessments')
                    ->whereColumn('client_assessments.client_id', 'clients.id');
            })
            ->update(['status' => 'assessment']);
    }
};
