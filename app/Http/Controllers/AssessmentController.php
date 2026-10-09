<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAssessment;
use App\Models\User;
use App\Notifications\AssessmentSelesai;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\TerimaStrip;

class AssessmentController extends Controller
{
    use TerimaStrip;

    /** Field bertipe (tanggal/angka/NIK/pilihan): jika diisi "-" disimpan sebagai NULL. */
    private const FIELD_STRIP = [
        'nik', 'tanggal_lahir', 'umur', 'jenis_kelamin', 'status_pendidikan', 'pendidikan_terakhir',
        'kategori_umur', 'relasi_keluarga', 'orientasi_realita',
    ];

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
     * Semua field wajib diisi; jika datanya tidak ada cukup diisi tanda "-".
     * Untuk field bertipe (FIELD_STRIP), "-" disimpan sebagai NULL (lihat trait TerimaStrip).
     */
    private function rules(Client $client): array
    {
        $nikUnique = Rule::unique('client_assessments', 'nik');
        if ($client->assessment) {
            $nikUnique->ignore($client->assessment->getKey());
        }

        return [
            // C1: Peksos boleh memperbarui identitas (nama, NIK, alamat KTP)
            'nama_lengkap' => 'required|string|max:255',
            'nik' => ['required', 'digits:16', $nikUnique],
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date|before_or_equal:today',
            'umur' => 'required|integer|min:0|max:150',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat_domisili' => 'required|string',
            'detail_alamat_pengakuan' => 'required|string',
            'status_perkawinan' => ['required', Rule::in(ClientAssessment::STATUS_PERKAWINAN)],
            'status_pendidikan' => 'required|string|max:255',
            'pendidikan_terakhir' => ['required', Rule::in($this->pilihanPendidikan($client))],
            'kategori_umur' => ['required', Rule::in(array_keys(ClientAssessment::KATEGORI_UMUR))],

            'kondisi_ekonomi' => 'required|string',
            'pekerjaan' => 'required|string|max:255',
            'sumber_penghasilan' => 'required|string|max:255',
            'kepemilikan_bansos' => 'required|string|max:255',
            'kondisi_tempat_tinggal' => 'required|string',

            'riwayat_masalah' => 'required|string',
            'relasi_keluarga' => 'required|in:baik,kurang_baik,terputus',
            'riwayat_trauma' => 'required|string',
            'masalah_keluarga' => 'required|string',
            'masalah_sekolah' => 'required|string',
            'masalah_sosial' => 'required|string',
            'cara_pandang_klien' => 'required|string',
            'rencana_kedepan' => 'required|string',
            'dukungan_sosial' => 'required|string',

            'kondisi_emosi' => 'required|string|max:255',
            'cara_komunikasi' => 'required|string|max:255',
            'arus_percakapan' => 'required|string|max:255',
            'ingatan' => 'required|string|max:255',
            'arah_perilaku' => 'required|string|max:255',
            'orientasi_realita' => 'required|in:baik,terganggu',
            'catatan_observasi' => 'required|string',

            'simpulan_awal' => 'required|string',
            'rekomendasi_penanganan' => 'required|string',
        ];
    }

    /**
     * Pilihan Pendidikan Terakhir yang diterima. Nilai lama yang sudah tersimpan (dulu berupa
     * teks bebas) ikut diterima agar data lama tetap bisa disimpan ulang tanpa dipaksa diganti.
     */
    private function pilihanPendidikan(Client $client): array
    {
        return array_values(array_filter(array_unique([
            ...ClientAssessment::PENDIDIKAN_TERAKHIR,
            $client->assessment?->pendidikan_terakhir,
        ])));
    }

    private function messages(): array
    {
        return [
            'pendidikan_terakhir.in' => 'Pendidikan terakhir harus dipilih dari daftar, atau pilih "- Tidak diketahui -".',
            'required' => ':attribute wajib diisi. Isi dengan tanda "-" jika data tidak ada.',
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka, atau isi tanda "-" jika tidak ada.',
            'nik.unique' => 'NIK sudah digunakan oleh data klien lain.',
        ];
    }

    private function labels(): array
    {
        return [
            'nama_lengkap' => 'Nama lengkap', 'nik' => 'NIK', 'tempat_lahir' => 'Tempat lahir',
            'tanggal_lahir' => 'Tanggal lahir', 'umur' => 'Umur', 'jenis_kelamin' => 'Jenis kelamin',
            'alamat_domisili' => 'Alamat (sesuai KTP)', 'detail_alamat_pengakuan' => 'Detail alamat pengakuan',
            'status_perkawinan' => 'Status perkawinan', 'status_pendidikan' => 'Status pendidikan',
            'pendidikan_terakhir' => 'Pendidikan terakhir', 'kategori_umur' => 'Kategori umur',
            'kondisi_ekonomi' => 'Kondisi ekonomi', 'pekerjaan' => 'Pekerjaan',
            'sumber_penghasilan' => 'Sumber penghasilan', 'kepemilikan_bansos' => 'Kepemilikan bansos',
            'kondisi_tempat_tinggal' => 'Kondisi tempat tinggal', 'riwayat_masalah' => 'Riwayat masalah',
            'relasi_keluarga' => 'Relasi keluarga', 'riwayat_trauma' => 'Riwayat kekerasan / trauma',
            'masalah_keluarga' => 'Masalah lingkup keluarga', 'masalah_sekolah' => 'Masalah lingkup sekolah',
            'masalah_sosial' => 'Masalah lingkup sosial', 'cara_pandang_klien' => 'Cara pandang klien',
            'rencana_kedepan' => 'Rencana ke depan', 'dukungan_sosial' => 'Dukungan sosial',
            'kondisi_emosi' => 'Alam perasaan', 'cara_komunikasi' => 'Cara komunikasi',
            'arus_percakapan' => 'Arus percakapan', 'ingatan' => 'Ingatan', 'arah_perilaku' => 'Arah perilaku',
            'orientasi_realita' => 'Orientasi realita', 'catatan_observasi' => 'Catatan observasi',
            'simpulan_awal' => 'Simpulan awal', 'rekomendasi_penanganan' => 'Rekomendasi penanganan',
        ];
    }

    /** Validasi + normalisasi "-" untuk store() dan update(). */
    private function validasi(Request $request, Client $client): array
    {
        $rules = $this->izinkanStrip($request, $this->rules($client), self::FIELD_STRIP);

        $validated = $request->validate($rules, $this->messages(), $this->labels());

        return $this->terapkanStrip($request, $validated, self::FIELD_STRIP);
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

        $validated = $this->validasi($request, $client);

        $namaLengkap = $validated['nama_lengkap'] ?? null;
        unset($validated['nama_lengkap']); // kolom ini milik tabel clients, bukan client_assessments

        $validated['client_id'] = $client->id;

        ClientAssessment::updateOrCreate(['client_id' => $client->id], $validated);

        $clientData = [
            'status' => Client::STATUS_ASSESSMENT,
            'locked_at' => $client->locked_at ?? now(),
        ];
        if (filled($namaLengkap) && trim($namaLengkap) !== '-') {
            $clientData['nama_lengkap'] = trim($namaLengkap);
        }
        $client->update($clientData);

        User::role('admin-rps')->each(fn (User $admin) => $admin->notify(new AssessmentSelesai($client)));

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

        $validated = $this->validasi($request, $client);

        $namaLengkap = $validated['nama_lengkap'] ?? null;
        unset($validated['nama_lengkap']);

        $client->assessment->update($validated);

        if (filled($namaLengkap) && trim($namaLengkap) !== '-') {
            $client->update(['nama_lengkap' => trim($namaLengkap)]);
        }

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Assessment berhasil diperbarui.');
    }
}