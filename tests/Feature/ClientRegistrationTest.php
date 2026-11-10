<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
});

it('requires a client photo during stage one registration', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $response = $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
    ]);

    $response->assertSessionHasErrors('foto_klien');
});

it('shows a predefined source of complaint menu during registration', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)
        ->get(route('clients.create'))
        ->assertOk()
        ->assertSee('<select name="sumber_aduan"', false)
        ->assertSee('Laporan warga')
        ->assertSee('Satpol PP')
        ->assertSee('Ormas')
        ->assertSee('Kelompok atau Lembaga Sosial')
        ->assertSee('Titik Lokasi Ditemukan')
        ->assertSee('Gunakan lokasi GPS')
        ->assertSee('data-location-map', false)
        ->assertSee('initializeOpenStreetMap', false)
        ->assertSee('name="latitude"', false)
        ->assertSee('name="longitude"', false)
        ->assertSee('Ambil foto sekarang')
        ->assertSee('Jepret foto')
        ->assertSee('navigator.mediaDevices.getUserMedia', false);
});

it('redirects direct visits to the client reuse URL to the registration form', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)
        ->get('/clients/reuse')
        ->assertRedirect(route('clients.create'));
});

it('allows admins to register clients but restricts editing existing data', function () {
    Storage::fake('local');
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $client = \App\Models\Client::create([
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Data Klien',
        'status' => \App\Models\Client::STATUS_DRAFT,
    ]);

    $this->actingAs($admin)
        ->get(route('clients.index'))
        ->assertOk()
        ->assertSee('Daftarkan Penerima');

    $this->actingAs($admin)->get(route('clients.create'))->assertOk();
    $this->actingAs($admin)->get('/clients/reuse')->assertRedirect(route('clients.create'));
    $this->actingAs($admin)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Baru',
        'nama_alias' => '-',
        'kondisi_fisik' => 'Kondisi sehat.',
        'problem_awal' => 'Membutuhkan pendampingan.',
        'kategori_ppks' => ['Anak Jalanan'],
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
        'tanpa_data_dukung' => '1',
    ])->assertRedirect();
    $this->actingAs($admin)->get(route('clients.edit', $client))->assertForbidden();
    $this->actingAs($admin)->patch(route('clients.update', $client), [])->assertForbidden();

    expect(\App\Models\Client::where('nama_lengkap', 'Klien Baru')->exists())->toBeTrue();
});

it('stores the expanded stage one client data and uploads', function () {
    Storage::fake('local'); // disk privat, lihat catatan opsi A soal foto/dokumen klien

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    // Catatan: sejak migration 2026_09_26_162644 (remove_redundant_fields_from_clients_table),
    // jenis_kelamin/umur/alamat adalah data Tahap 2 (client_assessments), bukan Tahap 1 (clients).
    // tempat_kejadian sudah digabung ke dalam tkp_kronologi. Field Tahap 1 yang sah hanya yang
    // divalidasi di ClientController@store.
    $response = $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di Terminal Kota, kondisi lemas.',
        'latitude' => '-6.8970000',
        'longitude' => '112.0640000',
        'kondisi_fisik' => 'Kondisi sehat.',
        'problem_awal' => 'Membutuhkan perlindungan sementara.',
        'nama_lengkap' => '-',
        'nama_alias' => 'Budi',
        'kategori_ppks' => ['Anak Jalanan'],
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
        'data_dukung' => UploadedFile::fake()->create('pengantar.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect(route('assessment.create', \App\Models\Client::firstOrFail()));

    $client = \App\Models\Client::firstOrFail();

    expect($client->nama_lengkap)->toBe('Tanpa Nama / X')
        ->and($client->tkp_kronologi)->toBe('Ditemukan di Terminal Kota, kondisi lemas.')
        ->and((float) $client->latitude)->toBe(-6.897)
        ->and((float) $client->longitude)->toBe(112.064)
        ->and($client->nama_alias)->toBe('Budi')
        ->and($client->kondisi_fisik)->toBe('Kondisi sehat.')
        ->and($client->problem_awal)->toBe('Membutuhkan perlindungan sementara.')
        ->and($client->kategori_ppks)->toBe('Anak Jalanan');

    Storage::disk('local')->assertExists($client->foto_klien);
    Storage::disk('local')->assertExists($client->data_dukung);
});

it('validates that location coordinates are a valid pair within geographic bounds', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $payload = [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Koordinat',
        'nama_alias' => '-',
        'kondisi_fisik' => '-',
        'problem_awal' => '-',
        'kategori_ppks' => ['-'],
        'tanpa_data_dukung' => '1',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
        'latitude' => '-6.897',
    ];

    $this->actingAs($user)->post(route('clients.store'), $payload)
        ->assertSessionHasErrors('longitude');

    $this->actingAs($user)->post(route('clients.store'), array_merge($payload, [
        'latitude' => '91',
        'longitude' => '112.064',
    ]))->assertSessionHasErrors('latitude');
});

it('menolak kategori_ppks di luar daftar pilihan', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'kategori_ppks' => ['Kategori Ngasal'],
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
    ])->assertSessionHasErrors('kategori_ppks.0');
});

it('allows selecting and saving multiple PPKS service categories', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)
        ->get(route('clients.create'))
        ->assertOk()
        ->assertSee('Pilih satu atau beberapa kategori yang sesuai.')
        ->assertSee('name="kategori_ppks[]"', false);

    $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Multi Kategori',
        'nama_alias' => '-',
        'kondisi_fisik' => '-',
        'problem_awal' => 'Membutuhkan beberapa jenis bantuan.',
        'kategori_ppks' => ['Anak Terlantar', 'Korban Kekerasan'],
        'tanpa_data_dukung' => '1',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
    ])->assertSessionHasNoErrors();

    $client = \App\Models\Client::firstOrFail();
    expect($client->kategori_ppks)->toBe('Anak Terlantar | Korban Kekerasan')
        ->and($client->kategoriPpksList())->toBe(['Anak Terlantar', 'Korban Kekerasan']);

    $this->actingAs($user)
        ->get(route('clients.show', $client))
        ->assertOk()
        ->assertSee('Anak Terlantar, Korban Kekerasan');
});

it('does not allow choosing unknown together with a known PPKS category', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Uji',
        'nama_alias' => '-',
        'kondisi_fisik' => '-',
        'problem_awal' => '-',
        'kategori_ppks' => ['-', 'Anak Terlantar'],
        'tanpa_data_dukung' => '1',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
    ])->assertSessionHasErrors('kategori_ppks');

    expect(\App\Models\Client::count())->toBe(0);
});

it('menolak registrasi bila ada field yang dikosongkan', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('super-admin');

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
    $user->assignRole('super-admin');

    $this->actingAs($user)->post(route('clients.store'), [
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => '-',
        'nama_alias' => '-',
        'kondisi_fisik' => '-',
        'problem_awal' => '-',
        'kategori_ppks' => ['-'],
        'tanpa_data_dukung' => '1',
        'foto_klien' => UploadedFile::fake()->create('client.jpg', 100, 'image/jpeg'),
    ])->assertSessionHasNoErrors()->assertRedirect(route('assessment.create', \App\Models\Client::firstOrFail()));

    $client = \App\Models\Client::firstOrFail();

    expect($client->nama_lengkap)->toBe('Tanpa Nama / X')
        ->and($client->nama_alias)->toBe('-')
        ->and($client->kondisi_fisik)->toBe('-')
        ->and($client->problem_awal)->toBe('-')
        ->and($client->kategori_ppks)->toBeNull()
        ->and($client->data_dukung)->toBeNull();
});

it('starts a new service episode from an existing NIK and copies the client profile', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $previousClient = \App\Models\Client::create([
        'tanggal_masuk' => '2026-09-24',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Kasus sebelumnya.',
        'nama_lengkap' => 'Budi Santoso',
        'nama_alias' => 'Budi',
        'kondisi_fisik' => 'Sehat',
        'problem_awal' => 'Butuh pendampingan.',
        'kategori_ppks' => 'Anak Jalanan',
        'foto_klien' => 'client-photos/budi.jpg',
        'data_dukung' => 'client-documents/budi.pdf',
        'status' => \App\Models\Client::STATUS_SELESAI,
        'is_archived' => true,
    ]);
    $previousAssessment = \App\Models\ClientAssessment::create(array_merge(
        payloadAssessmentLengkap(['nik' => '3578012345678901', 'alamat_domisili' => 'Alamat lama']),
        ['client_id' => $previousClient->id],
    ));

    $this->actingAs($user)->get(route('clients.create'))
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertSee($previousAssessment->nik);

    $response = $this->actingAs($user)->post(route('clients.reuse'), [
        'previous_client_id' => $previousClient->id,
        'tanggal_masuk' => '2026-10-25',
        'sumber_aduan' => 'Keluarga/kerabat',
        'tkp_kronologi' => 'Membutuhkan layanan lanjutan.',
        'latitude' => '-6.9000',
        'longitude' => '112.0700',
    ]);

    $newClient = \App\Models\Client::where('id', '!=', $previousClient->id)->firstOrFail();
    $response->assertRedirect(route('assessment.create', $newClient));

    expect($newClient->status)->toBe(\App\Models\Client::STATUS_DRAFT)
        ->and($newClient->tanggal_masuk->toDateString())->toBe('2026-10-25')
        ->and($newClient->tkp_kronologi)->toBe('Membutuhkan layanan lanjutan.')
        ->and((float) $newClient->latitude)->toBe(-6.9)
        ->and((float) $newClient->longitude)->toBe(112.07)
        ->and($newClient->nama_lengkap)->toBe('Budi Santoso')
        ->and($newClient->foto_klien)->toBe('client-photos/budi.jpg')
        ->and($newClient->assessment->nik)->toBe($previousAssessment->nik)
        ->and($newClient->assessment->alamat_domisili)->toBe('Alamat lama')
        ->and(\App\Models\ClientAssessment::where('nik', $previousAssessment->nik)->count())->toBe(2);

    $this->actingAs($user)
        ->get(route('assessment.create', $newClient))
        ->assertOk()
        ->assertSee('Alamat lama')
        ->assertSee(route('assessment.store', $newClient));
});

it('shows an error when a selected client has no service history with an NIK', function () {
    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)->post(route('clients.reuse'), [
        'previous_client_id' => $previousClient = \App\Models\Client::create([
            'tanggal_masuk' => '2026-09-24',
            'sumber_aduan' => 'Laporan warga',
            'tkp_kronologi' => 'Klien tanpa NIK.',
            'nama_lengkap' => 'Klien Tanpa NIK',
        ])->id,
        'tanggal_masuk' => '2026-10-25',
        'sumber_aduan' => 'Keluarga/kerabat',
        'tkp_kronologi' => 'Kebutuhan layanan.',
    ])->assertSessionHasErrors('previous_client_id');

    expect(\App\Models\Client::count())->toBe(1);
});

it('edit Tahap 1: field tidak boleh dikosongkan, "-" diterima, file lama tetap', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $user->assignRole('super-admin');

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
        'kategori_ppks' => ['-'],
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
