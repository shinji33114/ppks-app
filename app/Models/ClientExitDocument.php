<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientExitDocument extends Model
{
    protected $fillable = [
        'client_id',
        'created_by',
        'approved_by',
        'type',
        'status',
        'nomor_surat',
        'tanggal_surat',
        'tujuan',
        'keterangan',
        'tanggal_keluar',
        'no_hp_penanggung_jawab',
        'catatan_penolakan',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_keluar' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
