<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Payload form assessment (Tahap 2) dengan SEMUA field terisi.
 * Sejak semua field wajib diisi, test yang menyimpan assessment memakai payload ini
 * lalu menimpa field yang ingin diuji lewat $override.
 */
function payloadAssessmentLengkap(array $override = []): array
{
    return array_merge([
        'nama_lengkap' => 'Klien Uji',
        'nik' => '3578012345678901',
        'tempat_lahir' => 'Tuban',
        'tanggal_lahir' => '2000-01-01',
        'umur' => '26',
        'jenis_kelamin' => 'L',
        'alamat_domisili' => 'Jl. Contoh No. 1, Tuban',
        'detail_alamat_pengakuan' => 'Dekat pasar, RT 01/RW 02',
        'status_perkawinan' => 'Belum menikah',
        'status_pendidikan' => 'Putus Sekolah',
        'pendidikan_terakhir' => 'SMP / Sederajat',
        'kategori_umur' => 'dewasa',
        'kondisi_ekonomi' => 'Penghasilan tidak tetap.',
        'pekerjaan' => 'Buruh harian',
        'sumber_penghasilan' => 'Upah harian',
        'kepemilikan_bansos' => 'PKH',
        'kondisi_tempat_tinggal' => 'Menumpang',
        'riwayat_masalah' => 'Riwayat singkat.',
        'relasi_keluarga' => 'baik',
        'riwayat_trauma' => '-',
        'masalah_keluarga' => 'Sering bertengkar.',
        'masalah_sekolah' => '-',
        'masalah_sosial' => 'Dijauhi lingkungan.',
        'cara_pandang_klien' => 'Merasa tidak ada jalan keluar.',
        'rencana_kedepan' => 'Ingin bekerja.',
        'dukungan_sosial' => 'Tetangga',
        'kondisi_emosi' => 'Tenang',
        'cara_komunikasi' => 'Kooperatif',
        'arus_percakapan' => 'Lancar',
        'ingatan' => 'Baik',
        'arah_perilaku' => 'Terarah',
        'orientasi_realita' => 'baik',
        'catatan_observasi' => '-',
        'simpulan_awal' => 'Simpulan awal.',
        'rekomendasi_penanganan' => 'Pembinaan lanjutan.',
    ], $override);
}
