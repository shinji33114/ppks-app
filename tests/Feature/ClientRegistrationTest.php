<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

it('requires a client photo during stage one registration', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $response = $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
    ]);

    $response->assertSessionHasErrors('foto_klien');
});

it('stores the expanded stage one client data and uploads', function () {
    Storage::fake('local'); // disk privat, lihat catatan opsi A soal foto/dokumen klien

    $user = User::factory()->create();
    $user->assignRole('admin');

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
        'nama_lengkap' => '-',
        'nama_alias' => 'Budi',
        'kategori_ppks' => 'Anak Jalanan',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
        'data_dukung' => UploadedFile::fake()->create('pengantar.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect(route('dashboard'));

    $client = \App\Models\Client::firstOrFail();

    expect($client->nama_lengkap)->toBe('Tanpa Nama / X')
        ->and($client->tkp_kronologi)->toBe('Ditemukan di Terminal Kota, kondisi lemas.')
        ->and($client->nama_alias)->toBe('Budi')
        ->and($client->kondisi_fisik)->toBe('Kondisi sehat.')
        ->and($client->problem_awal)->toBe('Membutuhkan perlindungan sementara.')
        ->and($client->kategori_ppks)->toBe('Anak Jalanan');

    Storage::disk('local')->assertExists($client->foto_klien);
    Storage::disk('local')->assertExists($client->data_dukung);
});

it('menolak kategori_ppks di luar daftar pilihan', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'kategori_ppks' => 'Kategori Ngasal',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
    ])->assertSessionHasErrors('kategori_ppks');
});

it('menolak registrasi bila ada field yang dikosongkan', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
    ])->assertSessionHasErrors([
        'nama_lengkap', 'nama_alias', 'kondisi_fisik', 'problem_awal', 'kategori_ppks', 'data_dukung',
    ]);

    expect(\App\Models\Client::count())->toBe(0);
});

it('menerima tanda "-" untuk data yang tidak ada', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => '-',
        'nama_alias' => '-',
        'kondisi_fisik' => '-',
        'problem_awal' => '-',
        'kategori_ppks' => '-',
        'tanpa_data_dukung' => '1',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
    ])->assertSessionHasNoErrors()->assertRedirect(route('dashboard'));

    $client = \App\Models\Client::firstOrFail();

    expect($client->nama_lengkap)->toBe('Tanpa Nama / X')
        ->and($client->nama_alias)->toBe('-')
        ->and($client->kondisi_fisik)->toBe('-')
        ->and($client->problem_awal)->toBe('-')
        ->and($client->kategori_ppks)->toBeNull()
        ->and($client->data_dukung)->toBeNull();
});

it('edit Tahap 1: field tidak boleh dikosongkan, "-" diterima, file lama tetap', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('admin');

    $client = \App\Models\Client::create([
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Budi',
        'nama_alias' => 'Bud',
        'kondisi_fisik' => 'Sehat',
        'problem_awal' => 'Terlantar',
        'kategori_ppks' => 'Anak Jalanan',
        'foto_klien' => 'client-photos/lama.jpg',
        'status' => \App\Models\Client::STATUS_DRAFT,
    ]);

    $payload = [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Budi',
        'nama_alias' => '',
        'kondisi_fisik' => '-',
        'problem_awal' => '-',
        'kategori_ppks' => '-',
        'tanpa_data_dukung' => '1',
    ];

    $this->actingAs($user)->patch(route('clients.update', $client), $payload)
        ->assertSessionHasErrors('nama_alias');

    $this->actingAs($user)->patch(route('clients.update', $client), array_merge($payload, ['nama_alias' => '-']))
        ->assertSessionHasNoErrors();

    $client->refresh();
    expect($client->nama_alias)->toBe('-')
        ->and($client->kategori_ppks)->toBeNull()
        ->and($client->foto_klien)->toBe('client-photos/lama.jpg');
});
