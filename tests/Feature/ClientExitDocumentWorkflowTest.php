<?php

use App\Models\Client;
use App\Models\ClientExitDocument;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
});

function exitTestUser(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole('admin');

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
it('publishes the letter and archives the client immediately', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    exitTestUser('admin-rps');
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
    expect($document->status)->toBe('selesai')
        ->and($document->approved_by)->toBeNull()
        ->and($document->approved_at)->toBeNull()
        ->and($admin->notifications()->count())->toBe(0);

    expect($document->pdf_path)->toBe('exit-documents/' . $document->id . '.pdf');
    Storage::disk('local')->assertExists($document->pdf_path);
    expect(Storage::disk('local')->get($document->pdf_path))->toStartWith('%PDF-');
});

it('does not expose approval or rejection pages and actions', function () {
    $admin = exitTestUser('admin-rps');

    $this->actingAs($admin)->get('/exit-documents')->assertNotFound();
    $this->actingAs($admin)->patch('/exit-documents/1/approve')->assertNotFound();
    $this->actingAs($admin)->patch('/exit-documents/1/reject')->assertNotFound();
});

it('converts pending legacy letters into published letters', function () {
    $admin = exitTestUser('admin-rps');
    $client = exitTestClient();
    $document = ClientExitDocument::create(array_merge(exitDocumentPayload(), [
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'status' => 'menunggu_persetujuan',
    ]));

    $migration = require base_path('database/migrations/2026_10_14_000002_remove_exit_document_approval_workflow.php');
    $migration->up();

    expect($document->fresh()->status)->toBe('selesai');
});

it('allows an old rejected letter to be corrected and directly published', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $client = exitTestClient();

    $document = ClientExitDocument::create(array_merge(exitDocumentPayload(), [
        'client_id' => $client->id,
        'created_by' => $admin->id,
        'status' => 'ditolak',
        'catatan_penolakan' => 'Tujuan rujukan belum jelas.',
    ]));

    $this->actingAs($admin)
        ->patch(route('exit-documents.resubmit', $document), exitDocumentPayload())
        ->assertSessionHasNoErrors();

    $client->refresh();
    expect($document->refresh()->status)->toBe('selesai')
        ->and($client->status)->toBe(Client::STATUS_DIRUJUK)
        ->and($client->is_archived)->toBeTrue()
        ->and($client->archived_by)->toBe($admin->id)
        ->and($client->archived_at)->not->toBeNull();

});

it('allows admins to print and download the PDF immediately after publication', function () {
    Storage::fake('local');
    $admin = exitTestUser('admin-rps');
    $otherAdmin = exitTestUser('admin-rps');
    $head = exitTestUser('kepala-rps');
    $client = exitTestClient();

    $this->actingAs($admin)->post(route('exit-documents.store', $client), exitDocumentPayload());
    $document = ClientExitDocument::firstOrFail();

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

    $this->actingAs($admin)
        ->get(route('exit-documents.print', $document))
        ->assertOk()
        ->assertSee('telah diproses untuk')
        ->assertDontSee('telah disetujui');

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
    $this->actingAs($otherAdmin)->get(route('exit-documents.pdf', $document))->assertOk();
});

it('shows the protected client photo on the published letter detail page', function () {
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

it('embeds the client photo in the generated final PDF', function () {
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
