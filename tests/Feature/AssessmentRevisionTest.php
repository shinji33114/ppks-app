<?php

use App\Models\Client;
use App\Models\ClientAssessment;
use App\Models\User;
use App\Notifications\AssessmentSelesai;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

// Nama helper diberi awalan "rev" supaya tidak bentrok dengan helper di file tes lain.
function revUser(string $role): User
{
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('admin');

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
it('B3: assessment ditolak bila ada field yang kosong', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), ['simpulan_awal' => 'Simpulan awal.'])
        ->assertSessionHasErrors(['nik', 'tanggal_lahir', 'umur', 'jenis_kelamin', 'riwayat_masalah', 'rekomendasi_penanganan']);

    expect(ClientAssessment::count())->toBe(0)
        ->and($client->refresh()->status)->toBe(Client::STATUS_DRAFT);
});

it('B3: assessment berhasil bila data yang tidak ada diisi tanda "-"', function () {
    $peksos = revUser('peksos');
    $admin = revUser('admin-rps');
    $client = revClient();

    $semuaStrip = array_merge(
        array_map(fn () => '-', payloadAssessmentLengkap()),
        ['status_perkawinan' => 'Belum menikah'],
    );

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), $semuaStrip)
        ->assertSessionHasNoErrors();

    $assessment = ClientAssessment::firstOrFail();

    // Field bertipe (NIK, tanggal, angka, pilihan) disimpan NULL; field teks disimpan "-".
    expect($client->refresh()->status)->toBe(Client::STATUS_ASSESSMENT)
        ->and($client->nama_lengkap)->toBe('Klien Uji') // nama "-" tidak menimpa nama yang sudah ada
        ->and($assessment->nik)->toBeNull()
        ->and($assessment->tanggal_lahir)->toBeNull()
        ->and($assessment->umur)->toBeNull()
        ->and($assessment->jenis_kelamin)->toBeNull()
        ->and($assessment->kategori_umur)->toBeNull()
        ->and($assessment->riwayat_masalah)->toBe('-')
        ->and($assessment->simpulan_awal)->toBe('-');

    $notification = $admin->notifications()->firstOrFail();
    expect($notification->type)->toBe(AssessmentSelesai::class)
        ->and($notification->data['client_id'])->toBe($client->id)
        ->and($notification->data['url'])->toBe(route('clients.show', $client));
});

it('B3: simpulan awal tetap wajib', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['simpulan_awal' => '']))
        ->assertSessionHasErrors('simpulan_awal');

    expect(ClientAssessment::count())->toBe(0)
        ->and($client->refresh()->status)->toBe(Client::STATUS_DRAFT);
});

it('rejects an NIK already used by another assessment but allows keeping its own NIK on edit', function () {
    $peksos = revUser('peksos');
    $firstClient = revClient();
    $secondClient = revClient(['nama_lengkap' => 'Klien Kedua']);
    $nik = '3578012345678901';

    $this->actingAs($peksos)
        ->post(route('assessment.store', $firstClient), payloadAssessmentLengkap(['simpulan_awal' => 'Simpulan pertama.', 'nik' => $nik]))
        ->assertSessionHasNoErrors();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $secondClient), payloadAssessmentLengkap(['simpulan_awal' => 'Simpulan kedua.', 'nik' => $nik]))
        ->assertSessionHasErrors('nik');

    $this->actingAs($peksos)
        ->put(route('assessment.update', $firstClient), payloadAssessmentLengkap(['simpulan_awal' => 'Simpulan diperbarui.', 'nik' => $nik]))
        ->assertSessionHasNoErrors();

    expect(ClientAssessment::count())->toBe(1)
        ->and($secondClient->refresh()->status)->toBe(Client::STATUS_DRAFT);
});

it('mengizinkan NIK "-" pada banyak klien karena disimpan NULL', function () {
    $peksos = revUser('peksos');
    $firstClient = revClient();
    $secondClient = revClient(['nama_lengkap' => 'Klien Kedua']);

    foreach ([$firstClient, $secondClient] as $client) {
        $this->actingAs($peksos)
            ->post(route('assessment.store', $client), payloadAssessmentLengkap(['nik' => '-']))
            ->assertSessionHasNoErrors();
    }

    expect(ClientAssessment::count())->toBe(2)
        ->and(ClientAssessment::whereNull('nik')->count())->toBe(2);
});

// ---------------------------------------------------------------- B2
it('B2: umur, kondisi ekonomi, dan masalah keluarga tersimpan', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)->post(route('assessment.store', $client), payloadAssessmentLengkap([
        'umur' => 15,
        'kondisi_ekonomi' => 'Penghasilan keluarga tidak tetap.',
        'masalah_keluarga' => 'Sering terjadi pertengkaran.',
    ]))->assertSessionHasNoErrors();

    $assessment = ClientAssessment::firstOrFail();
    expect((int) $assessment->umur)->toBe(15)
        ->and($assessment->kondisi_ekonomi)->toBe('Penghasilan keluarga tidak tetap.')
        ->and($assessment->masalah_keluarga)->toBe('Sering terjadi pertengkaran.');
});

it('B2: umur di luar rentang ditolak', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['umur' => 200]))
        ->assertSessionHasErrors('umur');
});

// ---------------------------------------------------------------- C1
it('C1: Peksos dapat memperbarui nama lengkap klien', function () {
    $peksos = revUser('peksos');
    $client = revClient(['nama_lengkap' => 'Tanpa Nama / X']);

    $this->actingAs($peksos)->post(route('assessment.store', $client), payloadAssessmentLengkap([
        'nama_lengkap' => 'Budi Santoso',
    ]))->assertSessionHasNoErrors();

    expect($client->refresh()->nama_lengkap)->toBe('Budi Santoso');
});

it('C1: nama "-" tidak menimpa nama yang sudah ada, nama kosong ditolak', function () {
    $peksos = revUser('peksos');
    $client = revClient(['nama_lengkap' => 'Klien Uji']);

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['nama_lengkap' => '']))
        ->assertSessionHasErrors('nama_lengkap');

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['nama_lengkap' => '-']))
        ->assertSessionHasNoErrors();

    expect($client->refresh()->nama_lengkap)->toBe('Klien Uji');
});

// ---------------------------------------------------------------- C2
it('C2: form edit terbuka dan menampilkan data yang tersimpan', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['simpulan_awal' => 'Simpulan lama.']));

    $this->actingAs($peksos)
        ->get(route('assessment.edit', $client))
        ->assertOk()
        ->assertSee('Simpulan lama.')
        ->assertSee('Klien Uji');
});

it('C2: revisi assessment memperbarui data tanpa membuat baris baru', function () {
    $peksos = revUser('peksos');
    $admin = revUser('admin-rps');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['simpulan_awal' => 'Simpulan lama.']));

    $this->actingAs($peksos)
        ->put(route('assessment.update', $client), payloadAssessmentLengkap(['simpulan_awal' => 'Simpulan baru.']))
        ->assertRedirect(route('clients.show', $client->id));

    expect(ClientAssessment::count())->toBe(1)
        ->and(ClientAssessment::first()->simpulan_awal)->toBe('Simpulan baru.')
        ->and($client->refresh()->status)->toBe(Client::STATUS_ASSESSMENT)
        ->and($admin->notifications()->count())->toBe(1);
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

it('C2: Admin dapat membuka form edit assessment', function () {
    $admin = revUser('admin');
    $client = revClient(['status' => Client::STATUS_ASSESSMENT]);
    ClientAssessment::create(['client_id' => $client->id, 'simpulan_awal' => 'Simpulan lama.']);

    $this->actingAs($admin)
        ->get(route('assessment.edit', $client))
        ->assertOk();
});

// ---------------------------------------------------------------- F3
it('F3: rute penutupan kasus tanpa persetujuan sudah dihapus', function () {
    expect(Route::has('penanganan.tutup'))->toBeFalse();
});

it('pendidikan terakhir hanya menerima pilihan yang tersedia', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['pendidikan_terakhir' => 'Sekolah Rakyat']))
        ->assertSessionHasErrors('pendidikan_terakhir');

    expect(ClientAssessment::count())->toBe(0);
});

it('status perkawinan hanya menerima pilihan yang tersedia', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $response = $this->actingAs($peksos)->get(route('assessment.create', $client))->assertOk();
    foreach (ClientAssessment::STATUS_PERKAWINAN as $status) {
        $response->assertSee('<option value="'.$status.'"', false);
    }

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['status_perkawinan' => 'Cerai hidup']))
        ->assertSessionHasNoErrors();

    expect(ClientAssessment::firstOrFail()->status_perkawinan)->toBe('Cerai hidup');

    $client = revClient(['nama_lengkap' => 'Klien Kedua']);
    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['status_perkawinan' => 'Cerai']))
        ->assertSessionHasErrors('status_perkawinan');
});

it('pendidikan terakhir tersimpan dari pilihan dan "-" disimpan NULL', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)
        ->post(route('assessment.store', $client), payloadAssessmentLengkap(['pendidikan_terakhir' => 'Sarjana (S1 / D4)']))
        ->assertSessionHasNoErrors();

    expect(ClientAssessment::firstOrFail()->pendidikan_terakhir)->toBe('Sarjana (S1 / D4)');

    $this->actingAs($peksos)
        ->put(route('assessment.update', $client), payloadAssessmentLengkap(['pendidikan_terakhir' => '-']))
        ->assertSessionHasNoErrors();

    expect(ClientAssessment::firstOrFail()->pendidikan_terakhir)->toBeNull();
});

it('data pendidikan lama (teks bebas) tetap tampil dan bisa disimpan ulang saat edit', function () {
    $peksos = revUser('peksos');
    $client = revClient();

    $this->actingAs($peksos)->post(route('assessment.store', $client), payloadAssessmentLengkap());
    ClientAssessment::firstOrFail()->update(['pendidikan_terakhir' => 'SMA']); // nilai dari masa teks bebas

    $this->actingAs($peksos)
        ->get(route('assessment.edit', $client))
        ->assertOk()
        ->assertSee('SMA (data lama)');

    $this->actingAs($peksos)
        ->put(route('assessment.update', $client), payloadAssessmentLengkap(['pendidikan_terakhir' => 'SMA']))
        ->assertSessionHasNoErrors();
});
