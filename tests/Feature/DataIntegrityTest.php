<?php

use App\Models\Client;
use App\Models\ClientAssessment;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['petugas-piket', 'peksos', 'admin-rps', 'kepala-rps'] as $role) {
        Role::create(['name' => $role]);
    }
});

function makeUser(string $role, array $attrs = []): User
{
    $user = User::factory()->create(array_merge(['is_active' => true], $attrs));
    $user->assignRole($role);

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
    return array_merge([
        'riwayat_masalah' => 'Riwayat singkat.',
        'simpulan_awal' => 'Simpulan awal.',
        'rekomendasi_penanganan' => 'Pembinaan lanjutan.',
    ], $override);
}

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

it('A5: persetujuan surat menyimpan rujukan, tanggal keluar, dan no HP ke klien', function () {
    $admin = makeUser('admin-rps');
    $head = makeUser('kepala-rps');
    $client = makeClient(['status' => Client::STATUS_PENANGANAN]);

    $this->actingAs($admin)->post(route('exit-documents.store', $client), [
        'type' => 'rujukan',
        'tanggal_surat' => '2026-09-24',
        'tujuan' => 'Panti Sosial Harapan',
        'tanggal_keluar' => '2026-09-24',
        'no_hp_penanggung_jawab' => '081234567890',
    ]);

    $document = \App\Models\ClientExitDocument::firstOrFail();
    $this->actingAs($head)->patch(route('exit-documents.approve', $document));

    $client->refresh();
    expect($client->rujukan)->toBe('Panti Sosial Harapan')
        ->and($client->tanggal_keluar->toDateString())->toBe('2026-09-24')
        ->and($client->no_hp_penanggung_jawab)->toBe('081234567890')
        ->and($client->status_akhir)->toBe('dirujuk')
        ->and($client->is_archived)->toBeTrue();
});