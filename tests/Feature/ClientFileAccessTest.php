<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['petugas-piket', 'peksos', 'admin-rps', 'kepala-rps'] as $role) {
        Role::create(['name' => $role]);
    }
});

function fileTestUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function fileTestClient(array $attrs = []): Client
{
    Storage::fake('local');

    return Client::create(array_merge([
        'tanggal_masuk' => '2026-09-20',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Uji',
        'foto_klien' => UploadedFile::fake()->create('foto.jpg', 50, 'image/jpeg')->store('client-photos', 'local'),
        'status' => Client::STATUS_DRAFT,
    ], $attrs));
}

it('menolak akses foto klien tanpa login', function () {
    $client = fileTestClient();

    $this->get(route('clients.photo', $client))->assertRedirect(route('login'));
});

it('mengizinkan foto klien aktif diakses oleh role manapun yang login', function () {
    $client = fileTestClient();
    $user = fileTestUser('petugas-piket');

    $this->actingAs($user)->get(route('clients.photo', $client))->assertOk();
});

it('menolak Petugas Piket mengakses foto klien yang sudah diarsipkan', function () {
    $client = fileTestClient(['status' => Client::STATUS_SELESAI, 'is_archived' => true]);
    $user = fileTestUser('petugas-piket');

    $this->actingAs($user)->get(route('clients.photo', $client))->assertForbidden();
});

it('mengizinkan Peksos/Admin RPS/Kepala RPS mengakses foto klien arsip', function () {
    $client = fileTestClient(['status' => Client::STATUS_SELESAI, 'is_archived' => true]);

    foreach (['peksos', 'admin-rps', 'kepala-rps'] as $role) {
        $user = fileTestUser($role);
        $this->actingAs($user)->get(route('clients.photo', $client))->assertOk();
    }
});