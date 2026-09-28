<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientExitDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function store(Request $request, Client $client): RedirectResponse
    {
        if (! in_array($client->status, [Client::STATUS_ASSESSMENT, Client::STATUS_PENANGANAN], true)) {
            return back()->with('error', 'Surat hanya dapat dibuat untuk klien yang sedang dalam penanganan.');
        }

        if ($client->exitDocuments()->where('status', 'menunggu_persetujuan')->exists()) {
            return back()->with('error', 'Klien ini sudah memiliki surat yang menunggu persetujuan.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:surat_keluar,rujukan'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'tanggal_keluar' => ['required', 'date', 'after_or_equal:' . $client->tanggal_masuk, 'before_or_equal:today'],
            'no_hp_penanggung_jawab' => ['required', 'string', 'max:20'],
        ]);

        $validated['client_id'] = $client->id;
        $validated['created_by'] = auth()->id();
        $validated['status'] = 'menunggu_persetujuan';

        ClientExitDocument::create($validated);

        return redirect()->route('penanganan.index', $client)
            ->with('success', 'Surat berhasil diajukan dan menunggu persetujuan Kepala RPS.');
    }

    public function edit(ClientExitDocument $document): View|RedirectResponse
    {
        if ($document->created_by !== auth()->id() || $document->status !== 'ditolak') {
            return redirect()->route('exit-documents.mine')
                ->with('error', 'Hanya surat Anda yang ditolak yang dapat diperbaiki.');
        }

        return view('exit-documents.edit', compact('document'));
    }

    public function resubmit(Request $request, ClientExitDocument $document): RedirectResponse
    {
        if ($document->created_by !== auth()->id() || $document->status !== 'ditolak') {
            return back()->with('error', 'Surat ini tidak dapat diajukan ulang.');
        }

        $validated = $request->validate([
            'type' => ['required', 'in:surat_keluar,rujukan'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'tanggal_surat' => ['required', 'date'],
            'tujuan' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string'],
            'tanggal_keluar' => ['required', 'date', 'after_or_equal:' . $document->client->tanggal_masuk, 'before_or_equal:today'],
            'no_hp_penanggung_jawab' => ['required', 'string', 'max:20'],
        ]);

        $document->update(array_merge($validated, [
            'status' => 'menunggu_persetujuan',
            'approved_by' => null,
            'approved_at' => null,
            'catatan_penolakan' => null,
        ]));

        return redirect()->route('exit-documents.mine')
            ->with('success', 'Surat berhasil diperbaiki dan diajukan ulang.');
    }

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

            $document->client->update([
                'rujukan' => $document->tujuan,
                'tanggal_keluar' => $document->tanggal_keluar,
                'no_hp_penanggung_jawab' => $document->no_hp_penanggung_jawab,
                'status_akhir' => $document->type === 'rujukan' ? 'dirujuk' : 'pulang',
                'status' => $document->type === 'rujukan'
                    ? Client::STATUS_DIRUJUK
                    : Client::STATUS_SELESAI,
                'is_archived' => true,
                'archived_at' => now(),
                'archived_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Surat disetujui dan status klien telah diperbarui.');
    }

    public function reject(Request $request, ClientExitDocument $document): RedirectResponse
    {
        if ($document->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Surat ini sudah diproses.');
        }

        $validated = $request->validate([
            'catatan_penolakan' => ['required', 'string', 'max:2000'],
        ]);

        $document->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'catatan_penolakan' => $validated['catatan_penolakan'],
        ]);

        return back()->with('success', 'Surat ditolak dan dikembalikan kepada Admin RPS.');
    }
}
