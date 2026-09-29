<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // 'peksos' wajib ada: ClientController@store mengirim notifikasi ke role ini saat registrasi disimpan.
    Role::create(['name' => 'petugas-piket']);
    Role::create(['name' => 'peksos']);
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

    // Catatan: sejak migration 2026_09_26_162644 (remove_redundant_fields_from_clients_table),
    // jenis_kelamin/umur/alamat adalah data Tahap 2 (client_assessments), bukan Tahap 1 (clients).
    // tempat_kejadian sudah digabung ke dalam tkp_kronologi. Field Tahap 1 yang sah hanya yang
    // divalidasi di ClientController@store.
    $response = $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di Terminal Kota, kondisi lemas.',
        'kondisi_fisik' => 'Kondisi sehat.',
        'problem_awal' => 'Membutuhkan perlindungan sementara.',
        'nama_lengkap' => '',
        'nama_alias' => 'Budi',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
        'data_dukung' => UploadedFile::fake()->create('pengantar.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect(route('dashboard'));

    $client = \App\Models\Client::firstOrFail();

    expect($client->nama_lengkap)->toBe('Tanpa Nama / X')
        ->and($client->tkp_kronologi)->toBe('Ditemukan di Terminal Kota, kondisi lemas.')
        ->and($client->nama_alias)->toBe('Budi')
        ->and($client->kondisi_fisik)->toBe('Kondisi sehat.')
        ->and($client->problem_awal)->toBe('Membutuhkan perlindungan sementara.');

    Storage::disk('public')->assertExists($client->foto_klien);
    Storage::disk('public')->assertExists($client->data_dukung);
});