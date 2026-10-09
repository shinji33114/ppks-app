<?php

use App\Models\Client;
use App\Models\ClientExitDocument;
use App\Models\User;
use App\Notifications\SuratKeluarNotification;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    foreach (['petugas-piket', 'peksos', 'admin-rps', 'kepala-rps'] as $role) {
        Role::create(['name' => $role]);
    }
});

function exitTestUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

function exitTestClient(array $attrs = []): Client
{
    return Client::create(array_merge([
        'tanggal_masuk' => '2026-09-01',
        'sumber_aduan' => 'Laporan warga',
        'tkp_kronologi' => 'Ditemukan di area publik.',
        'nama_lengkap' => 'Klien Uji',
        'status' => Client::STATUS_PENANGANAN,
    ], $attrs));
}

function exitDocumentPayload(array $override = []): array
{
    return array_merge([
        'type' => 'rujukan',
        'nomor_surat' => '001/RPS/IX/2026',
        'tanggal_surat' => '2026-09-24',
        'tujuan' => 'Panti Sosial Harapan',
        'tanggal_keluar' => '2026-09-24',
        'no_hp_penanggung_jawab' => '081234567890',
    ], $override);
}

// ---------------------------------------------------------------- C4
it('C4: status klien berubah otomatis & diarsipkan saat surat disubmit, tanpa menunggu approval', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $head = exitTestUser('kepala-rps');
    $client = exitTestClient();

    $this->actingAs($admin)
        ->post(route('exit-documents.store', $client), exitDocumentPayload())
        ->assertSessionHasNoErrors();

    $client->refresh();
    expect($client->status)->toBe(Client::STATUS_DIRUJUK)
        ->and($client->is_archived)->toBeTrue()
        ->and($client->rujukan)->toBe('Panti Sosial Harapan')
        ->and($client->tanggal_keluar->toDateString())->toBe('2026-09-24')
        ->and($client->no_hp_penanggung_jawab)->toBe('081234567890');

    $document = ClientExitDocument::firstOrFail();
    expect($document->status)->toBe('menunggu_persetujuan');

    $notification = $head->notifications()->firstOrFail();
    expect($notification->type)->toBe(SuratKeluarNotification::class)
        ->and($notification->data['event'])->toBe(SuratKeluarNotification::DIAJUKAN)
        ->and($notification->data['document_id'])->toBe($document->id)
        ->and($notification->data['url'])->toBe(route('exit-documents.show', $document));

    expect($document->pdf_path)->toBe('exit-documents/' . $document->id . '.pdf');
    Storage::disk('local')->assertExists($document->pdf_path);
    expect(Storage::disk('local')->get($document->pdf_path))->toStartWith('%PDF-');
});

it('C4: approve() hanya menandatangani surat, tidak mengubah status klien (sudah berubah sejak submit)', function () {
    $admin = exitTestUser('admin-rps');
    $head = exitTestUser('kepala-rps');
    $client = exitTestClient();

    $this->actingAs($admin)
        ->post(route('exit-documents.store', $client), exitDocumentPayload())
        ->assertRedirect(route('exit-documents.mine'));
    $document = ClientExitDocument::firstOrFail();
    $statusSebelumApprove = $client->refresh()->status;

    $this->actingAs($head)->patch(route('exit-documents.approve', $document))
        ->assertSessionHasNoErrors();

    expect($document->refresh()->status)->toBe('disetujui')
        ->and($client->refresh()->status)->toBe($statusSebelumApprove);

    $notification = $admin->notifications()->firstOrFail();
    expect($notification->type)->toBe(SuratKeluarNotification::class)
        ->and($notification->data['event'])->toBe(SuratKeluarNotification::DISETUJUI)
        ->and($notification->data['document_id'])->toBe($document->id)
        ->and($notification->data['url'])->toBe(route('exit-documents.mine'));
});

it('C4: surat yang ditolak Kepala RPS mengembalikan klien ke Dalam Penanganan (bukan arsip)', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $head = exitTestUser('kepala-rps');
    $client = exitTestClient();

    $this->actingAs($admin)->post(route('exit-documents.store', $client), exitDocumentPayload());
    $document = ClientExitDocument::firstOrFail();

    $this->actingAs($head)->patch(route('exit-documents.reject', $document), [
        'catatan_penolakan' => 'Tujuan rujukan belum jelas, mohon dilengkapi.',
    ])->assertSessionHasNoErrors();

    $client->refresh();
    expect($document->refresh()->status)->toBe('ditolak')
        ->and($client->status)->toBe(Client::STATUS_PENANGANAN)
        ->and($client->is_archived)->toBeFalse();

    $this->actingAs($admin)
        ->patch(route('exit-documents.resubmit', $document), exitDocumentPayload())
        ->assertSessionHasNoErrors();

    $client->refresh();
    expect($document->refresh()->status)->toBe('menunggu_persetujuan')
        ->and($client->status)->toBe(Client::STATUS_DIRUJUK)
        ->and($client->is_archived)->toBeTrue()
        ->and($client->archived_by)->toBe($admin->id)
        ->and($client->archived_at)->not->toBeNull();

    expect($head->notifications()->count())->toBe(2);
});

it('allows the document creator and kepala-rps to download the PDF only after approval', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $otherAdmin = exitTestUser('admin-rps');
    $head = exitTestUser('kepala-rps');
    $client = exitTestClient();

    $this->actingAs($admin)->post(route('exit-documents.store', $client), exitDocumentPayload());
    $document = ClientExitDocument::firstOrFail();

    $this->actingAs($admin)->get(route('exit-documents.pdf', $document))->assertForbidden();
    $this->actingAs($head)->get(route('exit-documents.pdf', $document))->assertForbidden();

    $this->actingAs($head)->patch(route('exit-documents.approve', $document))->assertSessionHasNoErrors();

    $document->refresh();
    $this->actingAs($admin)
        ->get(route('exit-documents.mine'))
        ->assertOk()
        ->assertSee(route('exit-documents.pdf', [
            'document' => $document->id,
            'v' => $document->updated_at->timestamp,
        ]));

    $this->actingAs($admin)
        ->get(route('exit-documents.pdf', $document))
        ->assertOk()
        ->assertDownload('Surat-Rujukan-' . $document->id . '.pdf')
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Length', (string) Storage::disk('local')->size($document->pdf_path));

    Storage::disk('local')->put($document->pdf_path, '%PDF-OLD-FORMAT');
    $this->actingAs($admin)->get(route('exit-documents.pdf', $document))->assertOk();
    expect(Storage::disk('local')->get($document->pdf_path))
        ->toStartWith('%PDF-1.7')
        ->not->toBe('%PDF-OLD-FORMAT');

    $this->actingAs($admin)
        ->get(route('exit-documents.pdf', $document), ['Range' => 'bytes=0-4'])
        ->assertStatus(206)
        ->assertHeader('Content-Range', 'bytes 0-4/' . Storage::disk('local')->size($document->pdf_path));

    $this->actingAs($head)->get(route('exit-documents.pdf', $document))->assertOk();
    $this->actingAs($otherAdmin)->get(route('exit-documents.pdf', $document))->assertForbidden();
});

it('shows the protected client photo to kepala-rps on the letter review page', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $head = exitTestUser('kepala-rps');
    $client = exitTestClient(['foto_klien' => 'client-photos/client.jpg']);
    Storage::disk('local')->put($client->foto_klien, file_get_contents(public_path('spark-admin/assets/images/user_2.jpg')));

    $this->actingAs($admin)->post(route('exit-documents.store', $client), exitDocumentPayload());
    $document = ClientExitDocument::firstOrFail();

    $this->actingAs($head)
        ->get(route('exit-documents.show', $document))
        ->assertOk()
        ->assertSee(route('clients.photo', $client));

    $this->actingAs($head)->get(route('clients.photo', $client))->assertOk();
});

it('embeds the client photo in the generated approval PDF', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $client = exitTestClient(['foto_klien' => 'client-photos/client.jpg']);
    Storage::disk('local')->put($client->foto_klien, file_get_contents(public_path('spark-admin/assets/images/user_2.jpg')));

    $this->actingAs($admin)
        ->post(route('exit-documents.store', $client), exitDocumentPayload())
        ->assertRedirect(route('exit-documents.mine'));
    $document = ClientExitDocument::firstOrFail();
    $pdfContents = Storage::disk('local')->get($document->pdf_path);
    preg_match('/\/Type \/Pages.*?\/Count (\d+)/s', $pdfContents, $pageMatch);
    expect($pdfContents)->toStartWith('%PDF-')
        ->and(str_contains($pdfContents, '/Subtype /Image'))->toBeTrue()
        ->and((int) ($pageMatch[1] ?? 0))->toBe(1);
});

it('C4: Admin tidak bisa submit surat untuk klien yang sudah diarsipkan', function () {
    $admin = exitTestUser('admin-rps');
    $client = exitTestClient(['status' => Client::STATUS_DIRUJUK, 'is_archived' => true]);

    $this->actingAs($admin)
        ->post(route('exit-documents.store', $client), exitDocumentPayload())
        ->assertSessionHasNoErrors(); // bukan error validasi, tapi redirect back dengan pesan 'error'

    expect(ClientExitDocument::count())->toBe(0);
});

it('rejects duplicate letter numbers and responsible-party phone numbers', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $firstClient = exitTestClient();
    $secondClient = exitTestClient(['nama_lengkap' => 'Klien Kedua']);

    $this->actingAs($admin)
        ->post(route('exit-documents.store', $firstClient), exitDocumentPayload())
        ->assertSessionHasNoErrors();

    $this->actingAs($admin)
        ->post(route('exit-documents.store', $secondClient), exitDocumentPayload([
            'no_hp_penanggung_jawab' => '081298765432',
        ]))
        ->assertSessionHasErrors('nomor_surat');

    $this->actingAs($admin)
        ->post(route('exit-documents.store', $secondClient), exitDocumentPayload([
            'nomor_surat' => '002/RPS/IX/2026',
        ]))
        ->assertSessionHasErrors('no_hp_penanggung_jawab');

    expect(ClientExitDocument::count())->toBe(1);
});

it('notifies the admin who created the letter when kepala-rps rejects it', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $otherAdmin = exitTestUser('admin-rps');
    $head = exitTestUser('kepala-rps');
    $client = exitTestClient();

    $this->actingAs($admin)->post(route('exit-documents.store', $client), exitDocumentPayload());
    $document = ClientExitDocument::firstOrFail();

    $this->actingAs($head)->patch(route('exit-documents.reject', $document), [
        'catatan_penolakan' => 'Tujuan rujukan belum jelas.',
    ])->assertSessionHasNoErrors();

    expect($admin->notifications()->count())->toBe(1)
        ->and($admin->notifications()->first()->data['event'])->toBe(SuratKeluarNotification::DITOLAK)
        ->and($admin->notifications()->first()->data['message'])->toContain('Tujuan rujukan belum jelas.')
        ->and($admin->notifications()->first()->data['url'])->toBe(route('exit-documents.mine'))
        ->and($otherAdmin->notifications()->count())->toBe(0)
        ->and($head->notifications()->count())->toBe(1); // hanya notifikasi "diajukan" sebelumnya
});
