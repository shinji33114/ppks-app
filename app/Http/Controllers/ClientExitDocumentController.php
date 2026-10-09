<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientExitDocument;
use App\Models\User;
use App\Notifications\SuratKeluarNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientExitDocumentController extends Controller
{
    public function index(): View
    {
        $documents = ClientExitDocument::with(['client', 'creator'])
            ->where('status', 'menunggu_persetujuan')
            ->latest()
            ->paginate(15);

        return view('exit-documents.index', compact('documents'));
    }

    public function show(ClientExitDocument $document): View
    {
        if ($document->client->is_archived && ! auth()->user()->hasRole('kepala-rps')) {
            abort(403);
        }

        $document->load(['client.assessment', 'client.followups.user', 'creator']);

        return view('exit-documents.show', compact('document'));
    }

    public function print(ClientExitDocument $document): View|RedirectResponse
    {
        if (! auth()->user()->hasRole('kepala-rps')) {
            abort(403);
        }

        if ($document->status !== 'disetujui') {
            return redirect()->route('exit-documents.show', $document)
                ->with('error', 'Surat hanya dapat dicetak setelah disetujui Kepala RPS.');
        }

        $document->load(['client.assessment', 'approver']);

        return view('exit-documents.print', compact('document'));
    }

    public function mine(): View
    {
        $documents = ClientExitDocument::with('client')
            ->where('created_by', auth()->id())
            ->latest()
            ->paginate(15);

        return view('exit-documents.mine', compact('documents'));
    }

    public function downloadPdf(ClientExitDocument $document)
    {
        $user = auth()->user();
        $isCreator = $user->hasRole('admin-rps') && (int) $document->created_by === (int) $user->id;

        abort_unless($user->hasRole('kepala-rps') || $isCreator, 403);
        abort_unless($document->status === 'disetujui', 403, 'PDF final hanya tersedia setelah surat disetujui Kepala RPS.');
        abort_unless($document->pdf_path && Storage::disk('local')->exists($document->pdf_path), 404);

        $this->generatePdf($document);
        $document->refresh();

        $filename = ($document->type === 'rujukan' ? 'Surat-Rujukan-' : 'Surat-Keluar-') . $document->id . '.pdf';

        return response()->download(
            Storage::disk('local')->path($document->pdf_path),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    private function generatePdf(ClientExitDocument $document): void
    {
        $document->loadMissing(['client.assessment', 'creator', 'approver']);
        $path = 'exit-documents/' . $document->id . '.pdf';
        $photoFileUri = null;
        $photoPath = $document->client->foto_klien;

        if ($photoPath && Storage::disk('local')->exists($photoPath)) {
            $photoBytes = Storage::disk('local')->get($photoPath);
            $imageInfo = @getimagesizefromstring($photoBytes);
            $mimeType = $imageInfo['mime'] ?? null;
            $canRenderImage = $mimeType === 'image/jpeg'
                || (extension_loaded('gd') && in_array($mimeType, ['image/png', 'image/gif'], true));

            if ($canRenderImage) {
                $absolutePhotoPath = Storage::disk('local')->path($photoPath);
                $photoFileUri = 'file://' . str_replace('\\', '/', $absolutePhotoPath);
            }
        }

        $pdf = Pdf::loadView('exit-documents.pdf', compact('document', 'photoFileUri'))
            ->setOptions(['chroot' => [base_path(), Storage::disk('local')->path('')]])
            ->setPaper('a4');

        Storage::disk('local')->put($path, $pdf->output());
        $document->update(['pdf_path' => $path]);
    }

    /**
     * C4: sesuai dokumen ("Mengisi Tanggal Keluar & Rujukan otomatis mengubah status
     * akhir klien menjadi Selesai/Alumni/Dirujuk"), status klien berubah dan diarsipkan
     * DI SINI, saat Admin Penanganan submit surat -- bukan menunggu approve() Kepala RPS.
     * Persetujuan Kepala RPS tetap berjalan, tapi hanya menjadi status pada surat itu
     * sendiri (dokumen resmi), tidak lagi jadi syarat status klien berubah.
     */
    public function store(Request $request, Client $client): RedirectResponse
    {
        if (! in_array($client->status, [Client::STATUS_ASSESSMENT, Client::STATUS_PENANGANAN], true)) {
            return back()->with('error', 'Surat hanya dapat dibuat untuk klien yang sedang dalam penanganan.');
        }

        if ($client->is_archived) {
            return back()->with('error', 'Klien ini sudah diarsipkan.');
        }

        if ($client->exitDocuments()->where('status', 'ditolak')->exists()) {
            return back()->with('error', 'Surat sebelumnya ditolak Kepala RPS. Perbaiki dan ajukan ulang dari menu "Surat Saya".');
        }

        if ($client->exitDocuments()->where('status', 'menunggu_persetujuan')->exists()) {
            return back()->with('error', 'Klien ini sudah memiliki surat yang menunggu persetujuan.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:surat_keluar,rujukan'],
            'nomor_surat' => ['nullable', 'string', 'max:100', Rule::unique('client_exit_documents', 'nomor_surat')],
            'tanggal_surat' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'tanggal_keluar' => ['required', 'date', 'after_or_equal:' . $client->tanggal_masuk, 'before_or_equal:today'],
            'no_hp_penanggung_jawab' => [
                'required', 'string', 'max:20',
                Rule::unique('clients', 'no_hp_penanggung_jawab')->ignore($client->id),
                Rule::unique('client_exit_documents', 'no_hp_penanggung_jawab')
                    ->where(fn ($query) => $query->where('client_id', '!=', $client->id)),
            ],
        ]);

        DB::transaction(function () use ($validated, $client) {
            $validated['client_id'] = $client->id;
            $validated['created_by'] = auth()->id();
            $validated['status'] = 'menunggu_persetujuan';

            $document = ClientExitDocument::create($validated);

            // Perubahan status & arsip terjadi sekarang, otomatis, sesuai dokumen.
            $client->update([
                'rujukan' => $validated['tujuan'],
                'tanggal_keluar' => $validated['tanggal_keluar'],
                'no_hp_penanggung_jawab' => $validated['no_hp_penanggung_jawab'],
                'status_akhir' => $validated['type'] === 'rujukan' ? 'dirujuk' : 'pulang',
                'status' => $validated['type'] === 'rujukan'
                    ? Client::STATUS_DIRUJUK
                    : Client::STATUS_SELESAI,
                'is_archived' => true,
                'archived_at' => now(),
                'archived_by' => auth()->id(),
            ]);

            $this->generatePdf($document);

            User::role('kepala-rps')->each(fn (User $head) => $head->notify(
                new SuratKeluarNotification($document, SuratKeluarNotification::DIAJUKAN)
            ));
        });

        return redirect()->route('exit-documents.mine')
            ->with('success', 'Klien telah ditandai selesai dan surat menunggu persetujuan Kepala RPS.');
    }

    public function edit(ClientExitDocument $document): View|RedirectResponse
    {
        if ($document->created_by !== auth()->id() || $document->status !== 'ditolak') {
            return redirect()->route('exit-documents.mine')
                ->with('error', 'Hanya surat Anda yang ditolak yang dapat diperbaiki.');
        }

        return view('exit-documents.edit', compact('document'));
    }

    /**
     * C4: surat yang ditolak Kepala RPS tidak lagi otomatis mengubah status/arsip klien
     * (itu sudah terjadi saat store()). resubmit() di sini hanya memperbaiki isi surat
     * dan mengajukan ulang persetujuannya.
     */
    public function resubmit(Request $request, ClientExitDocument $document): RedirectResponse
    {
        if ($document->created_by !== auth()->id() || $document->status !== 'ditolak') {
            return back()->with('error', 'Surat ini tidak dapat diajukan ulang.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:surat_keluar,rujukan'],
            'nomor_surat' => [
                'nullable', 'string', 'max:100',
                Rule::unique('client_exit_documents', 'nomor_surat')->ignore($document->id),
            ],
            'tanggal_surat' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'tanggal_keluar' => ['required', 'date', 'after_or_equal:' . $document->client->tanggal_masuk, 'before_or_equal:today'],
            'no_hp_penanggung_jawab' => [
                'required', 'string', 'max:20',
                Rule::unique('clients', 'no_hp_penanggung_jawab')->ignore($document->client_id),
                Rule::unique('client_exit_documents', 'no_hp_penanggung_jawab')
                    ->where(fn ($query) => $query->where('client_id', '!=', $document->client_id)),
            ],
        ]);

        DB::transaction(function () use ($validated, $document) {
            $document->update(array_merge($validated, [
                'status' => 'menunggu_persetujuan',
                'approved_by' => null,
                'approved_at' => null,
                'catatan_penolakan' => null,
            ]));

            // Data surat berubah -> sinkronkan lagi ke klien (rujukan/tanggal keluar bisa saja direvisi).
            $document->client->update([
                'rujukan' => $validated['tujuan'],
                'tanggal_keluar' => $validated['tanggal_keluar'],
                'no_hp_penanggung_jawab' => $validated['no_hp_penanggung_jawab'],
                'status_akhir' => $validated['type'] === 'rujukan' ? 'dirujuk' : 'pulang',
                'status' => $validated['type'] === 'rujukan'
                    ? Client::STATUS_DIRUJUK
                    : Client::STATUS_SELESAI,
                'is_archived' => true,
                'archived_at' => now(),
                'archived_by' => auth()->id(),
            ]);

            $document->refresh();
            $this->generatePdf($document);

            User::role('kepala-rps')->each(fn (User $head) => $head->notify(
                new SuratKeluarNotification($document, SuratKeluarNotification::DIAJUKAN)
            ));
        });

        return redirect()->route('exit-documents.mine')
            ->with('success', 'Surat berhasil diperbaiki dan diajukan ulang.');
    }

    /**
     * C4: approve() sekarang HANYA menandatangani surat secara formal.
     * Status & arsip klien sudah berubah sejak store().
     */
    public function approve(ClientExitDocument $document): RedirectResponse
    {
        if ($document->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Surat ini sudah diproses.');
        }

        DB::transaction(function () use ($document) {
            $document->update([
                'status' => 'disetujui',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $document->refresh();
            $this->generatePdf($document);

            $document->creator?->notify(
                new SuratKeluarNotification($document, SuratKeluarNotification::DISETUJUI)
            );
        });

        return back()->with('success', 'Surat telah disetujui secara resmi.');
    }

    /**
     * C4: karena status klien sudah berubah otomatis sejak store(), surat yang
     * DITOLAK Kepala RPS berarti kasus itu SEHARUSNYA belum benar-benar selesai.
     * Maka klien dikembalikan aktif (kembali ke 'penanganan', tidak lagi diarsipkan)
     * sampai Admin RPS memperbaiki dan mengajukan ulang suratnya.
     */
    public function reject(Request $request, ClientExitDocument $document): RedirectResponse
    {
        if ($document->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Surat ini sudah diproses.');
        }

        $validated = $request->validate([
            'catatan_penolakan' => ['required', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($document, $validated) {
            $document->update([
                'status' => 'ditolak',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'catatan_penolakan' => $validated['catatan_penolakan'],
            ]);

            $document->client->update([
                'status' => Client::STATUS_PENANGANAN,
                'status_akhir' => null,
                'is_archived' => false,
                'archived_at' => null,
                'archived_by' => null,
            ]);

            // Beri tahu Admin RPS pembuat surat lewat lonceng notifikasi.
            $document->refresh();
            $document->creator?->notify(
                new SuratKeluarNotification($document, SuratKeluarNotification::DITOLAK)
            );
        });

        return back()->with('success', 'Surat ditolak dan klien dikembalikan ke status Dalam Penanganan. Admin RPS dapat memperbaiki dan mengajukan ulang.');
    }
}