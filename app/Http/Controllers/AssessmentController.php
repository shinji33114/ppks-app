<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAssessment;
use Illuminate\Http\Request;

class AssessmentController extends Controller
{
    /**
     * Menampilkan client yang menunggu assessment.
     */
    public function index()
    {
        $clients = Client::where('status', 'draft')
            ->latest()
            ->paginate(10);

        return view('assessments.index', compact('clients'));
    }

    /**
     * Menampilkan form assessment untuk client tertentu
     */
    public function create(Client $client)
    {
        // Cegah akses kalau client belum di tahap draft
        if ($client->status !== 'draft') {
            return redirect()->route('clients.show', $client->id)
                              ->with('error', 'Client ini sudah melewati tahap assessment.');
        }

        if ($client->locked_at === null) {
            $client->update(['locked_at' => now()]);
        }

        return view('assessments.create', compact('client'));
    }

    /**
     * Menyimpan hasil assessment
     */
    public function store(Request $request, Client $client)
    {
        $validated = $request->validate([
            'nik' => 'nullable|string|max:16',
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
        ]);

        $validated['client_id'] = $client->id;

        ClientAssessment::updateOrCreate(
            ['client_id' => $client->id],
            $validated
        );

        // Naikkan status client ke tahap berikutnya
        $client->update(['status' => 'assessment']);

        return redirect()->route('clients.show', $client->id)
                          ->with('success', 'Assessment berhasil disimpan.');
    }
}