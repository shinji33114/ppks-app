<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAssessment extends Model
{
    protected $fillable = [
        'client_id',
        'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin',
        'alamat_domisili', 'status_perkawinan', 'pendidikan_terakhir',
        'pekerjaan', 'sumber_penghasilan', 'kepemilikan_bansos', 'kondisi_tempat_tinggal',
        'riwayat_masalah', 'relasi_keluarga', 'riwayat_trauma', 'dukungan_sosial',
        'kondisi_emosi', 'orientasi_realita', 'catatan_observasi',
        'simpulan_awal', 'rekomendasi_penanganan',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}