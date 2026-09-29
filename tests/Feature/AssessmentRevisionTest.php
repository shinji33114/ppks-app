<?php

use App\Models\Client;
use App\Models\ClientAssessment;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['petugas-piket', 'peksos', 'admin-rps', 'kepala-rps'] as $role) {
        Role::create(['name' => $role]);
    }
});

// Nama helper diberi awalan "rev" supaya tidak bentrok dengan helper di file tes lain.
function revUser(string $role): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole($role);

    return $user;
}

function revClient(array $attrs = []): Client
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

// ---------------------------------------------------------------- B3
it('B3: assessment berhasil hanya dengan simpulan awal', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), ['simpulan_awal' => 'Simpulan awal.'])
        ->assertSessionHasNoErrors();

    expect($client->refresh()->status)->toBe(Client::STATUS_ASSESSMENT)
        ->and(ClientAssessment::count())->toBe(1)
        ->and(ClientAssessment::first()->riwayat_masalah)->toBeNull();
});

it('B3: simpulan awal tetap wajib', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), ['riwayat_masalah' => 'Ada, tapi tanpa simpulan.'])
        ->assertSessionHasErrors('simpulan_awal');

    expect(ClientAssessment::count())->toBe(0)
        ->and($client->refresh()->status)->toBe(Client::STATUS_DRAFT);
});

// ---------------------------------------------------------------- B2
it('B2: umur, kondisi ekonomi, dan masalah keluarga tersimpan', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)->post(route('assessment.store', $client), [
        'simpulan_awal' => 'Simpulan awal.',
        'umur' => 15,
        'kondisi_ekonomi' => 'Penghasilan keluarga tidak tetap.',
        'masalah_keluarga' => 'Sering terjadi pertengkaran.',
    ])->assertSessionHasNoErrors();

    $assessment = ClientAssessment::firstOrFail();
    expect((int) $assessment->umur)->toBe(15)
        ->and($assessment->kondisi_ekonomi)->toBe('Penghasilan keluarga tidak tetap.')
        ->and($assessment->masalah_keluarga)->toBe('Sering terjadi pertengkaran.');
});

it('B2: umur di luar rentang ditolak', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), ['simpulan_awal' => 'Ok.', 'umur' => 200])
        ->assertSessionHasErrors('umur');
});

// ---------------------------------------------------------------- C1
it('C1: Peksos dapat memperbarui nama lengkap klien', function () {
    $peksos = revUser('peksos');
    $client = revClient(['nama_lengkap' => 'Tanpa Nama / X']);

    $this->actingAs($peksos)->post(route('assessment.store', $client), [
        'simpulan_awal' => 'Simpulan awal.',
        'nama_lengkap' => 'Budi Santoso',
    ])->assertSessionHasNoErrors();

    expect($client->refresh()->nama_lengkap)->toBe('Budi Santoso');
});

it('C1: nama dikosongkan tidak menghapus nama yang sudah ada', function () {
    $peksos = revUser('peksos');
    $client = revClient(['nama_lengkap' => 'Klien Uji']);

    $this->actingAs($peksos)->post(route('assessment.store', $client), [
        'simpulan_awal' => 'Simpulan awal.',
        'nama_lengkap' => '',
    ]);

    expect($client->refresh()->nama_lengkap)->toBe('Klien Uji');
});

// ---------------------------------------------------------------- C2
it('C2: form edit terbuka dan menampilkan data yang tersimpan', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), ['simpulan_awal' => 'Simpulan lama.']);

    $this->actingAs($peksos)
        ->get(route('assessment.edit', $client))
        ->assertOk()
        ->assertSee('Simpulan lama.')
        ->assertSee('Klien Uji');
});

it('C2: revisi assessment memperbarui data tanpa membuat baris baru', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), ['simpulan_awal' => 'Simpulan lama.']);

    $this->actingAs($peksos)
        ->put(route('assessment.update', $client), ['simpulan_awal' => 'Simpulan baru.'])
        ->assertRedirect(route('clients.show', $client->id));

    expect(ClientAssessment::count())->toBe(1)
        ->and(ClientAssessment::first()->simpulan_awal)->toBe('Simpulan baru.')
        ->and($client->refresh()->status)->toBe(Client::STATUS_ASSESSMENT);
});

it('C2: revisi ditolak setelah klien masuk penanganan', function () {
    $peksos = revUser('peksos');
    $client = revClient(['status' => Client::STATUS_PENANGANAN]);
    ClientAssessment::create(['client_id' => $client->id, 'simpulan_awal' => 'Simpulan lama.']);

    $this->actingAs($peksos)
        ->get(route('assessment.edit', $client))
        ->assertRedirect(route('clients.show', $client->id));

    $this->actingAs($peksos)
        ->put(route('assessment.update', $client), ['simpulan_awal' => 'Diubah paksa.'])
        ->assertRedirect(route('clients.show', $client->id));

    expect(ClientAssessment::first()->simpulan_awal)->toBe('Simpulan lama.');
});

it('C2: revisi ditolak untuk klien yang sudah diarsipkan', function () {
    $peksos = revUser('peksos');
    $client = revClient(['status' => Client::STATUS_SELESAI, 'is_archived' => true]);
    ClientAssessment::create(['client_id' => $client->id, 'simpulan_awal' => 'Simpulan lama.']);

    $this->actingAs($peksos)
        ->put(route('assessment.update', $client), ['simpulan_awal' => 'Diubah paksa.'])
        ->assertRedirect(route('clients.show', $client->id));

    expect(ClientAssessment::first()->simpulan_awal)->toBe('Simpulan lama.');
});

it('C2: role selain Peksos tidak dapat membuka form edit', function () {
    $petugas = revUser('petugas-piket');
    $client = revClient(['status' => Client::STATUS_ASSESSMENT]);
    ClientAssessment::create(['client_id' => $client->id, 'simpulan_awal' => 'Simpulan lama.']);

    $this->actingAs($petugas)
        ->get(route('assessment.edit', $client))
        ->assertForbidden();
});

// ---------------------------------------------------------------- F3
it('F3: rute penutupan kasus tanpa persetujuan sudah dihapus', function () {
    expect(Route::has('penanganan.tutup'))->toBeFalse();
});