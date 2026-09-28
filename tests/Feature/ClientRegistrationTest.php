<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'petugas-piket']);
});

it('requires a client photo during stage one registration', function () {
    $user = User::factory()->create();
    $user->assignRole('petugas-piket');

    $response = $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
    ]);

    $response->assertSessionHasErrors('foto_klien');
});

it('stores the expanded stage one client data and uploads', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $user->assignRole('petugas-piket');

    $response = $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tempat_kejadian' => 'Terminal Kota',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'kondisi_fisik' => 'Kondisi sehat.',
        'problem_awal' => 'Membutuhkan perlindungan sementara.',
        'nama_lengkap' => '',
        'nama_alias' => 'Budi',
        'jenis_kelamin' => 'L',
        'umur' => 17,
        'alamat' => 'Kota Bandung',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
        'data_dukung' => UploadedFile::fake()->create('pengantar.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect(route('dashboard'));

    $client = \App\Models\Client::firstOrFail();

    expect($client->nama_lengkap)->toBe('Tanpa Nama / X')
        ->and($client->tempat_kejadian)->toBe('Terminal Kota')
        ->and($client->nama_alias)->toBe('Budi')
        ->and($client->umur)->toBe(17);

    Storage::disk('public')->assertExists($client->foto_klien);
    Storage::disk('public')->assertExists($client->data_dukung);
});
