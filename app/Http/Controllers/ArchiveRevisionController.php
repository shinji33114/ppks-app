<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientRevision;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArchiveRevisionController extends Controller
{
    /**
     * Label field untuk ditampilkan di log revisi, supaya catatan mudah dibaca
     * ("Nama Lengkap" bukan "nama_lengkap").
     */
    private const LABELS = [
        'nama_lengkap' => 'Nama Lengkap',
        'nama_alias' => 'Nama Alias',
        'sumber_aduan' => 'Sumber Aduan',
        'tkp_kronologi' => 'TKP / Kronologi',
        'problem_awal' => 'Kategori PPKS',
        'kondisi_fisik' => 'Kondisi Fisik',
        'rujukan' => 'Rujukan / Tujuan',
        'tanggal_keluar' => 'Tanggal Keluar',
        'no_hp_penanggung_jawab' => 'No. HP Penanggung Jawab',
    ];

    private function rules(Client $client): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'nama_alias' => 'nullable|string|max:255',
            'sumber_aduan' => 'required|string|max:255',
            'tkp_kronologi' => 'required|string',
            'problem_awal' => 'nullable|string|max:255',
            'kondisi_fisik' => 'nullable|string',
            'rujukan' => 'nullable|string|max:255',
            'tanggal_keluar' => ['nullable', 'date', 'after_or_equal:' . $client->tanggal_masuk->format('Y-m-d')],
            'no_hp_penanggung_jawab' => [
                'nullable', 'string', 'max:20',
                Rule::unique('clients', 'no_hp_penanggung_jawab')->ignore($client->id),
                Rule::unique('client_exit_documents', 'no_hp_penanggung_jawab')
                    ->where(fn ($query) => $query->where('client_id', '!=', $client->id)),
            ],
            'alasan' => 'required|string',
        ];
    }

    private function isClosedCase(Client $client): bool
    {
        return $client->is_archived
            || in_array($client->status, [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK], true);
    }

    /**
     * Menampilkan form revisi. Hanya untuk Kepala RPS, hanya untuk data yang
     * sudah diarsipkan (Selesai/Dirujuk) — data yang masih berjalan direvisi
     * lewat form Tahap 1/2/3 masing-masing, bukan lewat sini.
     */
    public function edit(Client $client): View
    {
        abort_unless(auth()->user()->hasRole('kepala-rps'), 403);
        abort_unless($this->isClosedCase($client), 404, 'Fitur ini hanya untuk data yang sudah diarsipkan.');

        return view('clients.revisi', compact('client'));
    }

    /**
     * E3 + E4: menyimpan revisi sekaligus mencatat log perubahannya.
     */
    public function update(Request $request, Client $client): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('kepala-rps'), 403);
        abort_unless($this->isClosedCase($client), 404);

        $validated = $request->validate($this->rules($client));
        $alasan = $validated['alasan'];
        unset($validated['alasan']);

        // Bandingkan nilai lama vs baru SEBELUM disimpan, supaya log-nya akurat.
        $changes = [];
        foreach ($validated as $field => $newValue) {
            $oldValue = $client->getOriginal($field);
            if ((string) $oldValue !== (string) $newValue) {
                $changes[$field] = [
                    'label' => self::LABELS[$field] ?? $field,
                    'from' => $oldValue,
                    'to' => $newValue,
                ];
            }
        }

        if (empty($changes)) {
            return redirect()->route('clients.show', $client->id)
                ->with('error', 'Tidak ada perubahan yang disimpan — data yang dikirim sama dengan data sebelumnya.');
        }

        DB::transaction(function () use ($client, $validated, $changes, $alasan) {
            $client->update(array_merge($validated, [
                'is_archived' => true,
                'archived_at' => $client->archived_at ?? now(),
                'archived_by' => $client->archived_by ?? auth()->id(),
            ]));

            ClientRevision::create([
                'client_id' => $client->id,
                'user_id' => auth()->id(),
                'changes' => $changes,
                'alasan' => $alasan,
            ]);
        });

        return redirect()->route('clients.show', $client->id)
            ->with('success', 'Revisi data arsip berhasil disimpan dan tercatat di log.');
    }
}