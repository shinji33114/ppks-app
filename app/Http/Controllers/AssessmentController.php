<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAssessment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Klien hanya boleh di-assessment jika belum diarsipkan dan masih berstatus draft.
     * (Membuka kembali assessment yang sudah tersimpan akan dikerjakan di item C2.)
     */
    private function canBeAssessed(Client $client): bool
    {
        return ! $client->is_archived && $client->status === Client::STATUS_DRAFT;
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

        // Peksos mulai mengisi Tahap 2 -> data Tahap 1 terkunci (butuh locked_at di $fillable, item A5)
        if ($client->locked_at === null) {
            $client->update(['locked_at' => now()]);
        }

        return view('assessments.create', compact('client'));
    }

    /**
     * Menyimpan hasil assessment
     */
    public function store(Request $request, Client $client): RedirectResponse
    {
        // A1: jalur POST langsung tidak boleh menimpa klien yang sudah lewat tahap ini / arsip.
        if (! $this->canBeAssessed($client)) {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'Assessment tidak dapat disimpan: klien sudah melewati tahap ini atau sudah diarsipkan.');
        }

        $validated = $request->validate([
            // A3: NIK harus tepat 16 digit angka
            'nik' => ['nullable', 'digits:16'],
            'tempat_lahir' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'alamat_domisili' => 'nullable|string',
            'status_perkawinan' => 'nullable|string|max:255',
            'pendidikan_terakhir' => 'nullable|string|max:255',

            'pekerjaan' => 'nullable|string|max:255',
            'sumber_penghasilan' => 'nullable|string|max:255',
            'kepemilikan_bansos' => 'nullable|string|max:255',
            'kondisi_tempat_tinggal' => 'nullable|string',

            'riwayat_masalah' => 'required|string',
            'relasi_keluarga' => 'nullable|in:baik,kurang_baik,terputus',
            'riwayat_trauma' => 'nullable|string',
            'dukungan_sosial' => 'nullable|string',

            'kondisi_emosi' => 'nullable|string|max:255',
            'orientasi_realita' => 'nullable|in:baik,terganggu',
            'catatan_observasi' => 'nullable|string',

            'simpulan_awal' => 'required|string',
            'rekomendasi_penanganan' => 'required|string',
        ], [
            'nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
        ]);

        $validated['client_id'] = $client->id;

        ClientAssessment::updateOrCreate(
            ['client_id' => $client->id],
            $validated
        );

        // Naikkan status client ke tahap berikutnya (sekaligus memastikan kunci Tahap 1 aktif)
        $client->update([
            'status' => Client::STATUS_ASSESSMENT,
            'locked_at' => $client->locked_at ?? now(),
        ]);

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Assessment berhasil disimpan.');
    }
}