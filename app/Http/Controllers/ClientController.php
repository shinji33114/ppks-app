<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Concerns\TerimaStrip;

class ClientController extends Controller
{
    use TerimaStrip;

    /** Field Tahap 1 yang bertipe pilihan: jika diisi "-" disimpan sebagai NULL. */
    private const FIELD_STRIP = ['kategori_ppks'];

    /**
     * Aturan Tahap 1: semua field wajib diisi (isi "-" jika datanya tidak ada).
     * Foto dan data dukung berupa file, jadi tidak bisa diisi "-":
     * foto wajib diunggah, data dukung boleh diganti centang "Tidak ada data dukung".
     * Saat edit, file yang sudah tersimpan tidak perlu diunggah ulang.
     */
    private function aturanTahapSatu(?Client $client): array
    {
        $adaFoto = filled($client?->foto_klien);
        $adaDokumen = filled($client?->data_dukung);

        return [
            'tanggal_masuk' => ['required', 'date', 'before_or_equal:today'],
            'sumber_aduan' => ['required', 'string', 'max:255'],
            'tkp_kronologi' => ['required', 'string'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nama_alias' => ['required', 'string', 'max:255'],
            'kondisi_fisik' => ['required', 'string'],
            'problem_awal' => ['required', 'string'],
            'kategori_ppks' => ['required', \Illuminate\Validation\Rule::in(Client::KATEGORI_PPKS)],
            'foto_klien' => [$adaFoto ? 'nullable' : 'required', 'image', 'max:5120'],
            'data_dukung' => [
                $adaDokumen ? 'nullable' : 'required_unless:tanpa_data_dukung,1',
                'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240',
            ],
        ];
    }

    private function pesanTahapSatu(): array
    {
        return [
            'required' => ':attribute wajib diisi. Isi dengan tanda "-" jika data tidak ada.',
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi.',
            'foto_klien.required' => 'Foto klien wajib diunggah.',
            'data_dukung.required_unless' => 'Data dukung wajib diunggah. Jika memang tidak ada, centang "Tidak ada data dukung".',
        ];
    }

    private function labelTahapSatu(): array
    {
        return [
            'tanggal_masuk' => 'Tanggal masuk',
            'sumber_aduan' => 'Sumber aduan',
            'tkp_kronologi' => 'Kronologi',
            'nama_lengkap' => 'Nama lengkap',
            'nama_alias' => 'Nama alias',
            'kondisi_fisik' => 'Kondisi fisik',
            'problem_awal' => 'Problem awal',
            'kategori_ppks' => 'Kategori PPKS',
            'foto_klien' => 'Foto klien',
            'data_dukung' => 'Data dukung',
        ];
    }

    /** Validasi + normalisasi "-" untuk store() dan update(). */
    private function validasiTahapSatu(Request $request, ?Client $client): array
    {
        $rules = $this->izinkanStrip($request, $this->aturanTahapSatu($client), self::FIELD_STRIP);

        $validated = $request->validate($rules, $this->pesanTahapSatu(), $this->labelTahapSatu());
        $validated = $this->terapkanStrip($request, $validated, self::FIELD_STRIP);

        // Nama "-" memakai penanda yang sudah dipakai aplikasi untuk klien tanpa identitas.
        if (trim($validated['nama_lengkap']) === '-') {
            $validated['nama_lengkap'] = 'Tanpa Nama / X';
        }

        return $validated;
    }

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
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nama_alias', 'like', "%{$search}%");
            });
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
        $validated = $this->validasiTahapSatu($request, null);

        $validated['foto_klien'] = $request->file('foto_klien')->store('client-photos', 'local');

        if ($request->hasFile('data_dukung')) {
            $validated['data_dukung'] = $request->file('data_dukung')->store('client-documents', 'local');
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

    $validated = $this->validasiTahapSatu($request, $client);

// Disk 'local' (privat) disamakan dengan store(), supaya foto/dokumen konsisten
// tidak bisa diakses langsung lewat URL publik -- lihat method photo()/document().
if ($request->hasFile('foto_klien')) {
    $validated['foto_klien'] = $request->file('foto_klien')->store('client-photos', 'local');
}

if ($request->hasFile('data_dukung')) {
    $validated['data_dukung'] = $request->file('data_dukung')->store('client-documents', 'local');
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
        // D4/D5 (revisi): klien yang sudah diarsipkan (Selesai/Dirujuk) HANYA boleh dilihat
        // oleh kepala-rps. Role lain (petugas-piket, peksos, admin-rps) diblokir sepenuhnya,
        // karena revisi data arsip juga jadi wewenang eksklusif kepala-rps (lihat E3).
        $isClosedCase = $client->is_archived
            || in_array($client->status, [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK], true);

        if ($isClosedCase) {
            abort_unless(auth()->user()->hasRole('kepala-rps'), 403);
        }

        return view('clients.show', compact('client', 'isClosedCase'));
    }

    /**
     * Menyajikan foto klien lewat rute yang mengecek login & role dulu (bukan
     * link publik). File fisik disimpan di disk 'local' (privat), bukan 'public'.
     */
    public function photo(Client $client)
    {
        $this->authorizeFileAccess($client);

        abort_unless($client->foto_klien && Storage::disk('local')->exists($client->foto_klien), 404);

        return Storage::disk('local')->response($client->foto_klien);
    }

    /** Sama seperti photo(), untuk berkas Data Dukung. */
    public function document(Client $client)
    {
        $this->authorizeFileAccess($client);

        abort_unless($client->data_dukung && Storage::disk('local')->exists($client->data_dukung), 404);

        return Storage::disk('local')->response($client->data_dukung);
    }

    /** Aturan akses yang sama seperti melihat detail klien (show()). */
    private function authorizeFileAccess(Client $client): void
    {
        $isClosedCase = $client->is_archived
            || in_array($client->status, [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK], true);

        if ($isClosedCase) {
            abort_unless(auth()->user()->hasAnyRole(['kepala-rps', 'admin-rps', 'peksos']), 403);
        }
    }
}