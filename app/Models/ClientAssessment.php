<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientAssessment extends Model
{
    /** Pilihan Kategori Umur (field 34). Rentang usia tiap kategori ditentukan oleh instansi. */
    public const KATEGORI_UMUR = [
        'balita' => 'Balita',
        'anak' => 'Anak',
        'remaja' => 'Remaja',
        'dewasa' => 'Dewasa',
        'lansia' => 'Lansia',
    ];

   protected $fillable = [
        'client_id',
        'nik', 'tempat_lahir', 'tanggal_lahir', 'umur', 'jenis_kelamin',
        'alamat_domisili', 'detail_alamat_pengakuan',
        'status_perkawinan', 'status_pendidikan', 'pendidikan_terakhir', 'kategori_umur',
        'kondisi_ekonomi', 'pekerjaan', 'sumber_penghasilan', 'kepemilikan_bansos', 'kondisi_tempat_tinggal',
        'riwayat_masalah', 'relasi_keluarga', 'riwayat_trauma',
        'masalah_keluarga', 'masalah_sekolah', 'masalah_sosial', 'cara_pandang_klien', 'rencana_kedepan',
        'dukungan_sosial',
        'kondisi_emosi', 'cara_komunikasi', 'arus_percakapan', 'ingatan', 'arah_perilaku',
        'orientasi_realita', 'catatan_observasi',
        'simpulan_awal', 'rekomendasi_penanganan',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}