<?php

use App\Models\Client;
use App\Models\ClientExitDocument;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'admin-rps']);
    Role::create(['name' => 'kepala-rps']);
});

it('closes the case at submit (C4) and only signs the letter on head approval', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin-rps');
    $head = User::factory()->create();
    $head->assignRole('kepala-rps');

    $client = Client::create([
        'tanggal_masuk' => '2026-09-20',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Uji',
        'foto_klien' => 'client-photos/test.jpg',
        'status' => Client::STATUS_PENANGANAN,
    ]);

    $response = $this->actingAs($admin)->post(route('exit-documents.store', $client), [
        'type' => 'rujukan',
        'nomor_surat' => '001/RPS/IX/2026',
        'tanggal_surat' => '2026-09-24',
        'tujuan' => 'Panti Sosial Harapan',
        'keterangan' => 'Rujukan layanan lanjutan.',
        'tanggal_keluar' => '2026-09-24',
        'no_hp_penanggung_jawab' => '081234567890',
    ]);

    $response->assertRedirect(route('exit-documents.mine'));

    $this->actingAs($admin)->get(route('exit-documents.mine'))->assertOk();

    // C4 / dokumen: mengisi Tanggal Keluar & Rujukan langsung mengubah status akhir klien.
    expect($client->refresh()->status)->toBe(Client::STATUS_DIRUJUK)
        ->and($client->is_archived)->toBeTrue();

    $document = ClientExitDocument::firstOrFail();
    $this->actingAs($head)
        ->patch(route('exit-documents.approve', $document))
        ->assertRedirect();

    expect($client->refresh()->status)->toBe(Client::STATUS_DIRUJUK)
        ->and($client->status_label)->toBe('Dirujuk')
        ->and($document->refresh()->status)->toBe('disetujui');
});
