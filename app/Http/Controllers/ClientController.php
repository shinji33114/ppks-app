<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAssessment;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
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
            'kategori_ppks' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (in_array('-', $value, true) && count($value) > 1) {
                        $fail('Pilih kategori yang diketahui atau "Tidak diketahui", jangan keduanya.');
                    }
                },
            ],
            'kategori_ppks.*' => ['required', 'string', 'distinct', Rule::in([...Client::KATEGORI_PPKS, '-'])],
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
        $rules = $this->aturanTahapSatu($client);

        $validated = $request->validate($rules, $this->pesanTahapSatu(), $this->labelTahapSatu());
        $validated['kategori_ppks'] = $validated['kategori_ppks'] === ['-']
            ? null
            : implode(' | ', $validated['kategori_ppks']);

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
    $stageStatuses = [
        'draft' => [Client::STATUS_DRAFT],
        'assessment' => [Client::STATUS_ASSESSMENT],
        'penanganan' => [Client::STATUS_PENANGANAN],
        'final' => [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK],
    ];
    $validated = $request->validate([
        'stage' => ['nullable', Rule::in(array_keys($stageStatuses))],
        'tanggal_masuk' => ['nullable', 'date_format:Y-m-d'],
    ]);
    $stage = $validated['stage'] ?? null;
    $tanggalMasuk = $validated['tanggal_masuk'] ?? null;

    $statusCounts = Client::query()
        ->selectRaw('status, COUNT(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');
    $stageCounts = [
        'draft' => (int) ($statusCounts[Client::STATUS_DRAFT] ?? 0),
        'assessment' => (int) ($statusCounts[Client::STATUS_ASSESSMENT] ?? 0),
        'penanganan' => (int) ($statusCounts[Client::STATUS_PENANGANAN] ?? 0),
        'final' => (int) ($statusCounts[Client::STATUS_SELESAI] ?? 0)
            + (int) ($statusCounts[Client::STATUS_DIRUJUK] ?? 0),
    ];

    $clients = Client::query()
        ->with(['exitDocuments' => fn ($query) => $query
            ->whereIn('status', ['selesai', 'disetujui'])
            ->latest('id')
            ->limit(1)])
        ->when($stage, function ($query, $stage) use ($stageStatuses) {
            $query->whereIn('status', $stageStatuses[$stage]);
        })
        ->when($tanggalMasuk, function ($query, $tanggalMasuk) {
            $query->whereDate('tanggal_masuk', $tanggalMasuk);
        })
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nama_alias', 'like', "%{$search}%");
            });
        })
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('clients.index', compact('clients', 'stage', 'stageCounts', 'tanggalMasuk'));
}

    /**
     * Menampilkan form registrasi klien baru (Tahap 1)
     */
    public function create()
    {
        $previousClients = Client::with('assessment')
            ->whereHas('assessment', fn ($query) => $query->whereNotNull('nik')->where('nik', '!=', ''))
            ->latest('id')
            ->get();

        return view('clients.intake', compact('previousClients'));
    }

    public function edit(Client $client)
{
    if (! $client->isStageOneEditable()) {
        abort(403, 'Data pendaftaran tidak dapat diubah karena sudah dikunci atau batas waktu telah lewat.');
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

$admins = \App\Models\User::role(['admin', 'super-admin'])->get();
\Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\KlienBaruMasuk($client));

return redirect()->route('assessment.create', $client)
          ->with('success', 'Penerima layanan berhasil didaftarkan. Lanjutkan ke tahap penilaian.');
    }

    /**
     * Membuka episode layanan baru dengan memakai kembali data klien yang dipilih dari riwayat.
     */
    public function reuse(Request $request)
    {
$validated = $request->validate([
'previous_client_id' => [
    'required',
    Rule::exists('clients', 'id')->where(function ($query) {
        $query->whereExists(function ($subquery) {
            $subquery->selectRaw('1')
                ->from('client_assessments')
                ->whereColumn('client_assessments.client_id', 'clients.id')
                ->whereNotNull('client_assessments.nik')
                ->where('client_assessments.nik', '!=', '');
        });
    }),
],
    'tanggal_masuk' => ['required', 'date', 'before_or_equal:today'],
    'sumber_aduan' => ['required', Rule::in(Client::SUMBER_ADUAN)],
    'tkp_kronologi' => ['required', 'string'],
], [
    'previous_client_id.exists' => 'Data klien yang dipilih tidak memiliki riwayat NIK. Pilih data klien lain.',
]);

$previousClient = Client::with('assessment')->findOrFail($validated['previous_client_id']);
$assessment = $previousClient->assessment;

$client = DB::transaction(function () use ($validated, $previousClient, $assessment) {
    $client = Client::create([
        'tanggal_masuk' => $validated['tanggal_masuk'],
        'sumber_aduan' => $validated['sumber_aduan'],
        'tkp_kronologi' => $validated['tkp_kronologi'],
        'nama_lengkap' => $previousClient->nama_lengkap,
        'nama_alias' => $previousClient->nama_alias,
        'kondisi_fisik' => $previousClient->kondisi_fisik,
        'problem_awal' => $previousClient->problem_awal,
        'kategori_ppks' => $previousClient->kategori_ppks,
        'foto_klien' => $previousClient->foto_klien,
        'data_dukung' => $previousClient->data_dukung,
    ]);

    $assessmentData = array_intersect_key(
        $assessment->getAttributes(),
        array_flip($assessment->getFillable())
    );
    $assessmentData['client_id'] = $client->id;
    ClientAssessment::create($assessmentData);

    return $client;
});

$admins = \App\Models\User::role(['admin', 'super-admin'])->get();
\Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\KlienBaruMasuk($client));

return redirect()->route('assessment.create', $client)
    ->with('success', 'Data klien lama berhasil digunakan. Periksa dan perbarui penilaian untuk layanan baru.');
    }

    public function update(Request $request, Client $client)
{
    if (! $client->isStageOneEditable()) {
        abort(403, 'Data pendaftaran tidak dapat diubah karena sudah dikunci atau batas waktu telah lewat.');
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
            ->with('success', 'Data pendaftaran berhasil diperbarui.');
    }

    /**
     * Menampilkan detail satu klien
     */
    public function show(Client $client)
    {
        $isClosedCase = $client->is_archived
            || in_array($client->status, [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK], true);

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
            abort_unless(auth()->user()->hasAnyRole(['admin', 'super-admin']), 403);
        }
    }
}