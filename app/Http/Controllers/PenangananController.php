<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PenangananController extends Controller
{
    /**
     * Menampilkan client yang siap atau sedang dalam penanganan.
     */
    public function dashboard()
    {
        $clients = Client::whereIn('status', ['assessment', 'penanganan'])
            ->latest()
            ->paginate(10);

        return view('penanganan.dashboard', compact('clients'));
    }

    /**
     * Menampilkan halaman penanganan: riwayat tindakan + form tambah + form tutup kasus
     */
    public function index(Client $client)
    {
        if ($client->is_archived || !in_array($client->status, ['assessment', 'penanganan'])) {
            return redirect()->route('clients.show', $client->id)
                              ->with('error', 'Penerima layanan ini belum siap didampingi atau layanannya sudah selesai.');
        }

        $followups = $client->followups;

        return view('penanganan.index', compact('client', 'followups'));
    }

    /**
     * Menyimpan satu catatan tindakan harian
     */
    public function store(Request $request, Client $client)
    {
        // A2: catatan tindakan hanya boleh ditambah saat klien sedang dalam penanganan dan belum diarsipkan.
        if ($client->is_archived || ! in_array($client->status, [Client::STATUS_ASSESSMENT, Client::STATUS_PENANGANAN], true)) {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'Catatan kegiatan tidak dapat ditambahkan karena pendampingan belum dimulai atau data sudah ditutup.');
        }

        $validated = $request->validate([
            'tanggal_tindakan' => 'required|date',
            'catatan_tindakan' => 'required|string',
            'foto_kegiatan' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('foto_kegiatan')) {
            $validated['foto_kegiatan'] = $request->file('foto_kegiatan')->store('client-followups', 'local');
        }

        $validated['client_id'] = $client->id;
        $validated['user_id'] = auth()->id();

        $followup = ClientFollowup::create($validated);

        // Begitu ada tindakan pertama, naikkan status ke 'penanganan'
        if ($client->status === 'assessment') {
            $client->update(['status' => 'penanganan']);
        }

        return redirect()->route('penanganan.index', $client->id)->withFragment('exit-document-form')
                          ->with('success', 'Catatan kegiatan berhasil disimpan.');
    }

    public function photo(Client $client, ClientFollowup $followup)
    {
        abort_unless((int) $followup->client_id === (int) $client->id, 404);
        abort_unless($followup->foto_kegiatan && Storage::disk('local')->exists($followup->foto_kegiatan), 404);

        return Storage::disk('local')->response($followup->foto_kegiatan);
    }

}