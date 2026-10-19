<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientExitDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientExitDocumentController extends Controller
{
    public function show(ClientExitDocument $document): View
    {
        $document->load(['client.assessment', 'client.followups.user', 'creator']);

        return view('exit-documents.show', compact('document'));
    }

    public function print(ClientExitDocument $document): View|RedirectResponse
    {
        if (! in_array($document->status, ['selesai', 'disetujui'], true)) {
            return redirect()->route('exit-documents.show', $document)
                ->with('error', 'Surat ini belum dapat dicetak.');
        }

        $document->load(['client.assessment', 'creator']);

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
        abort_unless($user->hasAnyRole(['admin', 'super-admin']), 403);
        abort_unless(in_array($document->status, ['selesai', 'disetujui'], true), 403, 'PDF final tidak tersedia untuk surat ini.');
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
        $document->loadMissing(['client.assessment', 'creator']);
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

    public function store(Request $request, Client $client): RedirectResponse
    {
        if (! in_array($client->status, [Client::STATUS_ASSESSMENT, Client::STATUS_PENANGANAN], true)) {
            return back()->with('error', 'Surat hanya dapat dibuat untuk klien yang sedang dalam penanganan.');
        }

        if ($client->is_archived) {
            return back()->with('error', 'Klien ini sudah diarsipkan.');
        }

        if ($client->exitDocuments()->where('status', 'ditolak')->exists()) {
            return back()->with('error', 'Surat sebelumnya ditolak. Perbaiki dan ajukan ulang dari menu "Surat Saya".');
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
            $validated['status'] = 'selesai';

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
        });

        return redirect()->route('exit-documents.mine')
            ->with('success', 'Surat berhasil diterbitkan dan klien telah ditandai selesai.');
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
     * Allows correction of letters rejected before the approval workflow was removed.
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
                'status' => 'selesai',
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
        });

        return redirect()->route('exit-documents.mine')
            ->with('success', 'Surat lama berhasil diperbaiki dan diterbitkan kembali.');
    }
}