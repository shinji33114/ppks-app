<?php

use App\Models\Client;
use App\Models\ClientAssessment;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

function makeUser(string $role, array $attrs = []): User
{
    // is_active diisi eksplisit: default true hanya ada di database, tidak di objek model yang baru dibuat
    $user = User::factory()->create(array_merge(['is_active' => true], $attrs));
    $user->assignRole('admin');

    return $user;
}

function makeClient(array $attrs = []): Client
{
    return Client::create(array_merge([
        'tanggal_masuk' => '2026-09-20',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Uji',
        'foto_klien' => 'client-photos/test.jpg',
        'status' => Client::STATUS_DRAFT,
    ], $attrs));
}

function assessmentPayload(array $override = []): array
{
    // Semua field assessment wajib diisi; payload lengkap ada di tests/Pest.php.
    return payloadAssessmentLengkap($override);
}

it('renders the refreshed assessment and handling pages', function () {
    $admin = makeUser('admin');
    $waitingClient = makeClient(['status' => Client::STATUS_DRAFT]);
    $handlingClient = makeClient([
        'status' => Client::STATUS_ASSESSMENT,
        'nama_lengkap' => 'Klien Penanganan',
    ]);

    $this->actingAs($admin)
        ->get(route('assessment.index'))
        ->assertOk()
        ->assertSee('Daftar yang menunggu penilaian')
        ->assertSee($waitingClient->nama_lengkap);

    $this->actingAs($admin)
        ->get(route('penanganan.dashboard'))
        ->assertOk()
        ->assertSee('Siap didampingi')
        ->assertSee($handlingClient->nama_lengkap);

    $this->actingAs($admin)
        ->get(route('penanganan.index', $handlingClient))
        ->assertOk()
        ->assertSee('Tambah catatan kegiatan')
        ->assertSee('Riwayat kegiatan')
        ->assertSee('Surat keluar atau rujukan');
});

// ---------------------------------------------------------------- A1
it('A1: menolak assessment untuk klien yang sudah diarsipkan (POST langsung)', function () {
    $peksos = makeUser('peksos');
    $client = makeClient(['status' => Client::STATUS_SELESAI, 'is_archived' => true]);

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), assessmentPayload())
        ->assertRedirect(route('clients.show', $client->id));

    expect($client->refresh()->status)->toBe(Client::STATUS_SELESAI)
        ->and(ClientAssessment::count())->toBe(0);
});

it('A1: assessment normal untuk klien draft tetap berhasil', function () {
    $peksos = makeUser('peksos');
    $client = makeClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), assessmentPayload())
        ->assertSessionHasNoErrors();

    expect($client->refresh()->status)->toBe(Client::STATUS_ASSESSMENT)
        ->and(ClientAssessment::count())->toBe(1);
});

// ---------------------------------------------------------------- A2
it('A2: menolak catatan tindakan untuk klien yang sudah diarsipkan', function () {
    $admin = makeUser('admin-rps');
    $client = makeClient(['status' => Client::STATUS_DIRUJUK, 'is_archived' => true]);

    $this->actingAs($admin)
        ->post(route('penanganan.store', $client), [
            'tanggal_tindakan' => '2026-09-25',
            'catatan_tindakan' => 'Tidak boleh tersimpan.',
        ])
        ->assertRedirect(route('clients.show', $client->id));

    expect($client->followups()->count())->toBe(0);
});

it('A2: catatan tindakan untuk klien siap ditangani tetap berhasil', function () {
    $admin = makeUser('admin-rps');
    $client = makeClient(['status' => Client::STATUS_ASSESSMENT]);

    $this->actingAs($admin)
        ->post(route('penanganan.store', $client), [
            'tanggal_tindakan' => '2026-09-25',
            'catatan_tindakan' => 'Bimbingan hari pertama.',
        ]);

    expect($client->followups()->count())->toBe(1)
        ->and($client->refresh()->status)->toBe(Client::STATUS_PENANGANAN);
});

// ---------------------------------------------------------------- A3
it('A3: NIK harus tepat 16 digit', function () {
    $peksos = makeUser('peksos');
    $client = makeClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), assessmentPayload(['nik' => '12345']))
        ->assertSessionHasErrors('nik');

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), assessmentPayload(['nik' => '12345678901234AB']))
        ->assertSessionHasErrors('nik');

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), assessmentPayload(['nik' => '3578012345678901']))
        ->assertSessionHasNoErrors();
});

// ---------------------------------------------------------------- A4
it('A4: sesi milik akun yang dinonaktifkan langsung dikeluarkan', function () {
    $user = makeUser('petugas-piket', ['is_active' => false]);

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

it('A4: akun aktif tidak terganggu', function () {
    $user = makeUser('petugas-piket');

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertOk();
});

// ---------------------------------------------------------------- A5
it('A5: membuka form assessment benar-benar menyimpan locked_at', function () {
    $peksos = makeUser('peksos');
    $client = makeClient();

    expect($client->locked_at)->toBeNull();

    $this->actingAs($peksos)->get(route('assessment.create', $client))->assertOk();

    expect($client->refresh()->locked_at)->not->toBeNull()
        ->and($client->isStageOneEditable())->toBeFalse();
});

it('A5: penerbitan surat langsung menyimpan rujukan, tanggal keluar, dan no HP ke klien', function () {
    $admin = makeUser('admin-rps');
    $client = makeClient(['status' => Client::STATUS_PENANGANAN]);

    $this->actingAs($admin)->post(route('exit-documents.store', $client), [
        'type' => 'rujukan',
        'tanggal_surat' => '2026-09-24',
        'tujuan' => 'Panti Sosial Harapan',
        'tanggal_keluar' => '2026-09-24',
        'no_hp_penanggung_jawab' => '081234567890',
    ]);

    $client->refresh();
    expect($client->rujukan)->toBe('Panti Sosial Harapan')
        ->and($client->tanggal_keluar->toDateString())->toBe('2026-09-24')
        ->and($client->no_hp_penanggung_jawab)->toBe('081234567890')
        ->and($client->status_akhir)->toBe('dirujuk')
        ->and($client->is_archived)->toBeTrue()
        ->and(\App\Models\ClientExitDocument::firstOrFail()->status)->toBe('selesai');
});

// ---------------------------------------------------------------- B1
function b1Fields(): array
{
    return [
        'detail_alamat_pengakuan' => 'Jl. Mawar RT 02/RW 05, dekat pasar',
        'status_pendidikan' => 'Putus Sekolah',
        'kategori_umur' => 'remaja',
        'masalah_sekolah' => 'Sering membolos.',
        'masalah_sosial' => 'Dijauhi teman sebaya.',
        'cara_pandang_klien' => 'Merasa tidak ada jalan keluar.',
        'rencana_kedepan' => 'Ingin kembali bersekolah.',
        'cara_komunikasi' => 'Kooperatif',
        'arus_percakapan' => 'Lancar',
        'ingatan' => 'Baik',
        'arah_perilaku' => 'Terarah',
    ];
}

it('B1: form assessment menampilkan 11 field baru', function () {
    $peksos = makeUser('peksos');
    $client = makeClient();

    $response = $this->actingAs($peksos)->get(route('assessment.create', $client))->assertOk();

    foreach (array_keys(b1Fields()) as $name) {
        $response->assertSee('name="'.$name.'"', false);
    }
});

it('B1: 11 field baru tersimpan ke client_assessments', function () {
    $peksos = makeUser('peksos');
    $client = makeClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), assessmentPayload(b1Fields()))
        ->assertSessionHasNoErrors();

    $assessment = ClientAssessment::firstOrFail();
    foreach (b1Fields() as $column => $value) {
        expect($assessment->{$column})->toBe($value);
    }
});

it('B1: kategori umur di luar pilihan ditolak', function () {
    $peksos = makeUser('peksos');
    $client = makeClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), assessmentPayload(['kategori_umur' => 'raksasa']))
        ->assertSessionHasErrors('kategori_umur');
});