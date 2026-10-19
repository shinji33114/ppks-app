<?php

use App\Models\Client;
use App\Models\ClientRevision;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

function arsipRevUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

    return $user;
}

function arsipRevClient(array $attrs = []): Client
{
    return Client::create(array_merge([
        'tanggal_masuk' => '2026-09-10',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Uji',
        'foto_klien' => 'client-photos/test.jpg',
        'status' => Client::STATUS_SELESAI,
        'status_akhir' => 'pulang',
        'tanggal_keluar' => '2026-09-15',
        'no_hp_penanggung_jawab' => '081200000000',
        'is_archived' => true,
        'archived_at' => now(),
    ], $attrs));
}

function arsipRevPayload(array $over = []): array
{
    return array_merge([
        'alasan' => 'Koreksi nama sesuai KTP asli.',
        'nama_lengkap' => 'Klien Uji',
        'nama_alias' => null,
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'problem_awal' => null,
        'kondisi_fisik' => null,
        'rujukan' => null,
        'tanggal_keluar' => '2026-09-15',
        'no_hp_penanggung_jawab' => '081200000000',
    ], $over);
}

it('lets kepala-rps revise an archived client and logs the change', function () {
    $head = arsipRevUser('kepala-rps');
    $client = arsipRevClient();

    $this->actingAs($head)
        ->put(route('clients.revisi.update', $client), arsipRevPayload(['nama_lengkap' => 'Budi Santoso']))
        ->assertRedirect(route('clients.show', $client));

    expect($client->refresh()->nama_lengkap)->toBe('Budi Santoso');

    $rev = ClientRevision::firstOrFail();
    expect($rev->user_id)->toBe($head->id)
        ->and($rev->alasan)->toBe('Koreksi nama sesuai KTP asli.')
        ->and($rev->changes['nama_lengkap'])->toBe([
            'label' => 'Nama Lengkap',
            'from' => 'Klien Uji',
            'to' => 'Budi Santoso',
        ]);
});

it('requires a reason for every revision', function () {
    $client = arsipRevClient();

    $this->actingAs(arsipRevUser('kepala-rps'))
        ->put(route('clients.revisi.update', $client), arsipRevPayload(['alasan' => '', 'nama_lengkap' => 'Lain']))
        ->assertSessionHasErrors('alasan');

    expect($client->refresh()->nama_lengkap)->toBe('Klien Uji')
        ->and(ClientRevision::count())->toBe(0);
});

it('allows an admin to revise archived data', function () {
    $client = arsipRevClient();

    $this->actingAs(arsipRevUser('admin'))
        ->get(route('clients.revisi.edit', $client))
        ->assertOk();

    $this->actingAs(arsipRevUser('admin'))
        ->put(route('clients.revisi.update', $client), arsipRevPayload(['nama_lengkap' => 'Diubah']))
        ->assertRedirect(route('clients.show', $client));

    expect($client->refresh()->nama_lengkap)->toBe('Diubah');
});

it('does not allow revision of a client that is not archived', function () {
    $client = arsipRevClient(['status' => Client::STATUS_PENANGANAN, 'is_archived' => false]);

    $this->actingAs(arsipRevUser('kepala-rps'))
        ->get(route('clients.revisi.edit', $client))
        ->assertNotFound();

    $this->actingAs(arsipRevUser('kepala-rps'))
        ->put(route('clients.revisi.update', $client), arsipRevPayload(['nama_lengkap' => 'Diubah']))
        ->assertNotFound();
});

it('shows the official revision action for a closed client and opens its form', function () {
    $client = arsipRevClient([
        'status' => Client::STATUS_SELESAI,
        'is_archived' => false,
        'archived_at' => null,
    ]);
    $head = arsipRevUser('kepala-rps');

    $this->actingAs($head)
        ->get(route('clients.show', $client))
        ->assertOk()
        ->assertSee('Revisi Data Arsip');

    $this->actingAs($head)
        ->get(route('clients.revisi.edit', $client))
        ->assertOk();

    $this->actingAs($head)
        ->put(route('clients.revisi.update', $client), arsipRevPayload(['nama_lengkap' => 'Klien Diperbarui']))
        ->assertRedirect(route('clients.show', $client));

    expect($client->refresh()->is_archived)->toBeTrue()
        ->and($client->archived_at)->not->toBeNull()
        ->and($client->archived_by)->toBe($head->id);
});

it('rejects an exit date earlier than the entry date', function () {
    $client = arsipRevClient();

    $this->actingAs(arsipRevUser('kepala-rps'))
        ->put(route('clients.revisi.update', $client), arsipRevPayload(['tanggal_keluar' => '2026-09-01']))
        ->assertSessionHasErrors('tanggal_keluar');
});

it('rejects a responsible-party phone number already used by another client during archive revision', function () {
    $head = arsipRevUser('kepala-rps');
    arsipRevClient(['no_hp_penanggung_jawab' => '081211111111']);
    $client = arsipRevClient(['no_hp_penanggung_jawab' => '081222222222']);

    $this->actingAs($head)
        ->put(route('clients.revisi.update', $client), arsipRevPayload([
            'alasan' => 'Koreksi nomor penanggung jawab.',
            'no_hp_penanggung_jawab' => '081211111111',
        ]))
        ->assertSessionHasErrors('no_hp_penanggung_jawab');

    expect($client->refresh()->no_hp_penanggung_jawab)->toBe('081222222222');
});

it('shows the client detail page after assessment with a birth date filled in', function () {
    $client = arsipRevClient(['status' => Client::STATUS_ASSESSMENT, 'is_archived' => false, 'archived_at' => null]);
    $client->assessment()->create(['tempat_lahir' => 'Surabaya', 'tanggal_lahir' => '2000-05-17', 'simpulan_awal' => 'Uji']);

    $this->actingAs(arsipRevUser('kepala-rps'))
        ->get(route('clients.show', $client))
        ->assertOk()
        ->assertSee('Surabaya, 17'); // nama bulan mengikuti APP_LOCALE, jadi tidak diuji di sini
});

it('shows the progress stepper on the client detail page', function () {
    $draft = arsipRevClient(['status' => Client::STATUS_DRAFT, 'is_archived' => false, 'archived_at' => null]);

    $this->actingAs(arsipRevUser('kepala-rps'))
        ->get(route('clients.show', $draft))
        ->assertOk()
        ->assertSee('stepper-ppks')
        ->assertSee('Tahap 3');

    $final = arsipRevClient(['status' => Client::STATUS_DIRUJUK]);

    $this->actingAs(arsipRevUser('kepala-rps'))
        ->get(route('clients.show', $final))
        ->assertOk()
        ->assertSee('Hasil akhir');
});

it('shows the admin role and the Tuban footer on the dashboard', function () {
    $this->actingAs(arsipRevUser('admin'))
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard Statistik')
        ->assertSee('Total Penerima Manfaat')
        ->assertSee('Dinas Sosial Kabupaten Tuban');
});
