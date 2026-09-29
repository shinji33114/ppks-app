<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AssessmentController extends Controller
{
    /**
     * Klien hanya boleh di-assessment pertama kali jika belum diarsipkan dan masih berstatus draft.
     */
    private function canBeAssessed(Client $client): bool
    {
        return ! $client->is_archived && $client->status === Client::STATUS_DRAFT;
    }

    /**
     * C2: assessment yang sudah tersimpan masih boleh direvisi Peksos
     * selama klien belum masuk tahap penanganan dan belum diarsipkan.
     */
    private function canEditAssessment(Client $client): bool
    {
        return ! $client->is_archived && $client->status === Client::STATUS_ASSESSMENT;
    }

    /**
     * Aturan validasi dipakai bersama oleh store() dan update().
     * B3: hanya simpulan_awal yang wajib diisi.
     */
    private function rules(): array
    {
        return [
            // C1: Peksos boleh memperbarui identitas (nama, NIK, alamat KTP)
            'nama_lengkap' => 'nullable|string|max:255',
            'nik' => ['nullable', 'digits:16'],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'umur' => 'nullable|integer|min:0|max:150',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat_domisili' => 'nullable|string',
            'detail_alamat_pengakuan' => 'nullable|string',
            'status_perkawinan' => 'nullable|string|max:255',
            'status_pendidikan' => 'nullable|string|max:255',
            'pendidikan_terakhir' => 'nullable|string|max:255',
            'kategori_umur' => ['nullable', Rule::in(array_keys(ClientAssessment::KATEGORI_UMUR))],

            'kondisi_ekonomi' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'sumber_penghasilan' => 'nullable|string|max:255',
            'kepemilikan_bansos' => 'nullable|string|max:255',
            'kondisi_tempat_tinggal' => 'nullable|string',

            'riwayat_masalah' => 'nullable|string',
            'relasi_keluarga' => 'nullable|in:baik,kurang_baik,terputus',
            'riwayat_trauma' => 'nullable|string',
            'masalah_keluarga' => 'nullable|string',
            'masalah_sekolah' => 'nullable|string',
            'masalah_sosial' => 'nullable|string',
            'cara_pandang_klien' => 'nullable|string',
            'rencana_kedepan' => 'nullable|string',
            'dukungan_sosial' => 'nullable|string',

            'kondisi_emosi' => 'nullable|string|max:255',
            'cara_komunikasi' => 'nullable|string|max:255',
            'arus_percakapan' => 'nullable|string|max:255',
            'ingatan' => 'nullable|string|max:255',
            'arah_perilaku' => 'nullable|string|max:255',
            'orientasi_realita' => 'nullable|in:baik,terganggu',
            'catatan_observasi' => 'nullable|string',

            'simpulan_awal' => 'required|string',
            'rekomendasi_penanganan' => 'nullable|string',
        ];
    }

    private function messages(): array
    {
        return [
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'simpulan_awal.required' => 'Simpulan awal wajib diisi.',
        ];
    }

    /**
     * Menampilkan client yang menunggu assessment.
     */
    public function index()
    {
        $clients = Client::where('status', Client::STATUS_DRAFT)
            ->where('is_archived', false)
            ->latest()
            ->paginate(10);

        return view('assessments.index', compact('clients'));
    }

    /**
     * Menampilkan form assessment untuk client tertentu
     */
    public function create(Client $client)
    {
        if (! $this->canBeAssessed($client)) {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'Klien ini sudah melewati tahap assessment atau sudah diarsipkan.');
        }

        // Peksos mulai mengisi Tahap 2 -> data Tahap 1 terkunci
        if ($client->locked_at === null) {
            $client->update(['locked_at' => now()]);
        }

        return view('assessments.create', ['client' => $client, 'assessment' => null]);
    }

    /**
     * Menyimpan hasil assessment
     */
    public function store(Request $request, Client $client): RedirectResponse
    {
        if (! $this->canBeAssessed($client)) {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'Assessment tidak dapat disimpan: klien sudah melewati tahap ini atau sudah diarsipkan.');
        }

        $validated = $request->validate($this->rules(), $this->messages());

        $namaLengkap = $validated['nama_lengkap'] ?? null;
        unset($validated['nama_lengkap']); // kolom ini milik tabel clients, bukan client_assessments

        $validated['client_id'] = $client->id;

        ClientAssessment::updateOrCreate(['client_id' => $client->id], $validated);

        $clientData = [
            'status' => Client::STATUS_ASSESSMENT,
            'locked_at' => $client->locked_at ?? now(),
        ];
        if (filled($namaLengkap)) {
            $clientData['nama_lengkap'] = trim($namaLengkap);
        }
        $client->update($clientData);

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Assessment berhasil disimpan.');
    }

    /**
     * C2: form revisi assessment yang sudah tersimpan
     */
    public function edit(Client $client)
    {
        if (! $this->canEditAssessment($client) || ! $client->assessment) {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'Assessment tidak dapat diedit: klien sudah masuk penanganan, diarsipkan, atau belum di-assessment.');
        }

        return view('assessments.create', ['client' => $client, 'assessment' => $client->assessment]);
    }

    /**
     * C2: menyimpan revisi assessment
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        if (! $this->canEditAssessment($client) || ! $client->assessment) {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'Assessment tidak dapat diedit: klien sudah masuk penanganan, diarsipkan, atau belum di-assessment.');
        }

        $validated = $request->validate($this->rules(), $this->messages());

        $namaLengkap = $validated['nama_lengkap'] ?? null;
        unset($validated['nama_lengkap']);

        $client->assessment->update($validated);

        if (filled($namaLengkap)) {
            $client->update(['nama_lengkap' => trim($namaLengkap)]);
        }

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Assessment berhasil diperbarui.');
    }
}