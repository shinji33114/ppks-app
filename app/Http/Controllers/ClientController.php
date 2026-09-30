<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Menampilkan daftar semua klien
     */
    public function index(Request $request)
{
    $query = Client::query();

    if (! $request->user()->hasRole('kepala-rps')) {
        $query->where('is_archived', false)
            ->whereNotIn('status', [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK]);
    }

    $clients = $query->when($request->search, function ($query, $search) {
            $query->where('nama_lengkap', 'like', "%{$search}%");
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('clients.index', compact('clients'));
}

    /**
     * Menampilkan form registrasi klien baru (Tahap 1)
     */
    public function create()
    {
        return view('clients.intake');
    }

    public function edit(Client $client)
{
    if (! $client->isStageOneEditable()) {
        abort(403, 'Data Tahap 1 sudah terkunci atau melewati batas waktu edit.');
    }

    return view('clients.edit', compact('client'));
}

    /**
     * Menyimpan data dari form ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
    'tanggal_masuk'  => 'required|date',
    'sumber_aduan'   => 'required|string|max:255',
    'tkp_kronologi'   => 'required|string',
    'kondisi_fisik'   => 'nullable|string',
    'problem_awal'    => 'nullable|string',
    'nama_lengkap'    => 'nullable|string|max:255',
    'nama_alias'      => 'nullable|string|max:255',
    'foto_klien'      => 'required|image|max:5120',
    'data_dukung'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
]);

        $validated['foto_klien'] = $request->file('foto_klien')->store('client-photos', 'local');

        if ($request->hasFile('data_dukung')) {
            $validated['data_dukung'] = $request->file('data_dukung')->store('client-documents', 'local');
        }

        if (empty($validated['nama_lengkap'])) {
            $validated['nama_lengkap'] = 'Tanpa Nama / X';
        }

        $client = Client::create($validated);

$peksosList = \App\Models\User::role('peksos')->get();
\Illuminate\Support\Facades\Notification::send($peksosList, new \App\Notifications\KlienBaruMasuk($client));

return redirect()->route('dashboard')
                  ->with('success', 'Klien berhasil diregistrasi.');
    }

    public function update(Request $request, Client $client)
{
    if (! $client->isStageOneEditable()) {
        abort(403, 'Data Tahap 1 sudah terkunci atau melewati batas waktu edit.');
    }

    $validated = $request->validate([
    'tanggal_masuk' => ['required', 'date'],
    'sumber_aduan' => ['required', 'string', 'max:255'],
    'tkp_kronologi' => ['required', 'string'],
    'kondisi_fisik' => ['nullable', 'string'],
    'nama_lengkap' => ['required', 'string', 'max:255'],
    'nama_alias' => ['nullable', 'string', 'max:255'],
    'problem_awal' => ['nullable', 'string', 'max:255'],
    'foto_klien' => ['nullable', 'image', 'max:5120'],
    'data_dukung' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
]);

if ($request->hasFile('foto_klien')) {
    $validated['foto_klien'] = $request->file('foto_klien')->store('client-photos', 'public');
}

if ($request->hasFile('data_dukung')) {
    $validated['data_dukung'] = $request->file('data_dukung')->store('client-documents', 'public');
}

        $client->update($validated);

        return redirect()->route('clients.show', $client)
            ->with('success', 'Data Tahap 1 berhasil diperbarui.');
    }

    /**
     * Menampilkan detail satu klien
     */
    public function show(Client $client)
    {
        // D4/D5: klien yang sudah diarsipkan (Selesai/Dirujuk) boleh dilihat READ-ONLY oleh
        // kepala-rps, admin-rps, dan peksos. Petugas Piket tetap tidak boleh, karena kasus itu
        // sudah di luar wewenangnya sesuai tabel role di dokumen.
        $isClosedCase = $client->is_archived
            || in_array($client->status, [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK], true);

        if ($isClosedCase) {
            abort_unless(auth()->user()->hasAnyRole(['kepala-rps', 'admin-rps', 'peksos']), 403);
        }

        return view('clients.show', compact('client', 'isClosedCase'));
    }
}