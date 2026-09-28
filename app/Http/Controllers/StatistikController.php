<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAssessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatistikController extends Controller
{
    public function index(): View
    {
        // 1. Total klien keseluruhan
        $totalKlien = Client::count();

        // 2. Breakdown per status
        $statusCounts = Client::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusLabels = [
            Client::STATUS_DRAFT => 'Draft',
            Client::STATUS_ASSESSMENT => 'Assessment',
            Client::STATUS_PENANGANAN => 'Penanganan',
            Client::STATUS_SELESAI => 'Selesai',
            Client::STATUS_DIRUJUK => 'Dirujuk',
        ];

        $statusBreakdown = [];
        foreach ($statusLabels as $key => $label) {
            $statusBreakdown[] = [
                'label' => $label,
                'total' => $statusCounts[$key] ?? 0,
            ];
        }

        // 3. Pilah jenis kelamin (dari data assessment yang sudah diisi Peksos)
        $genderCounts = ClientAssessment::select('jenis_kelamin', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_kelamin')
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');

        $genderBreakdown = [
            'Laki-laki' => $genderCounts['L'] ?? 0,
            'Perempuan' => $genderCounts['P'] ?? 0,
        ];

        // Pilah kelompok usia (dihitung dari tanggal_lahir)
        $ageBuckets = ['Anak (<18)' => 0, 'Produktif (18-59)' => 0, 'Lansia (60+)' => 0];
        ClientAssessment::whereNotNull('tanggal_lahir')->pluck('tanggal_lahir')->each(function ($tgl) use (&$ageBuckets) {
            $age = Carbon::parse($tgl)->age;
            if ($age < 18) {
                $ageBuckets['Anak (<18)']++;
            } elseif ($age < 60) {
                $ageBuckets['Produktif (18-59)']++;
            } else {
                $ageBuckets['Lansia (60+)']++;
            }
        });

        // 4. Kategori PPKS (problem_awal)
        $kategoriBreakdown = Client::select('problem_awal', DB::raw('count(*) as total'))
            ->whereNotNull('problem_awal')
            ->where('problem_awal', '!=', '')
            ->groupBy('problem_awal')
            ->orderByDesc('total')
            ->get();

        $totalDenganKategori = $kategoriBreakdown->sum('total');
        $totalTanpaKategori = $totalKlien - $totalDenganKategori;

        // 5. Rata-rata lama penanganan (untuk klien yang sudah Selesai/Dirujuk)
        $selesaiClients = Client::whereIn('status', [Client::STATUS_SELESAI, Client::STATUS_DIRUJUK])
            ->whereNotNull('tanggal_keluar')
            ->get(['tanggal_masuk', 'tanggal_keluar']);

        $rataRataLamaHari = $selesaiClients->isNotEmpty()
            ? round($selesaiClients->avg(fn ($c) => Carbon::parse($c->tanggal_masuk)->diffInDays(Carbon::parse($c->tanggal_keluar))), 1)
            : null;

        // 6. Tren klien masuk per bulan (6 bulan terakhir)
        $trenBulanan = Client::select(
                DB::raw("DATE_FORMAT(tanggal_masuk, '%Y-%m') as bulan"),
                DB::raw('count(*) as total')
            )
            ->where('tanggal_masuk', '>=', Carbon::now()->subMonths(5)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->map(fn ($row) => [
                'label' => Carbon::createFromFormat('Y-m', $row->bulan)->translatedFormat('M Y'),
                'total' => $row->total,
            ]);

        // Bonus: rekap sumber aduan (jalur rujukan masuk)
        $sumberAduanBreakdown = Client::select('sumber_aduan', DB::raw('count(*) as total'))
            ->groupBy('sumber_aduan')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('statistik.index', compact(
            'totalKlien',
            'statusBreakdown',
            'genderBreakdown',
            'ageBuckets',
            'kategoriBreakdown',
            'totalDenganKategori',
            'totalTanpaKategori',
            'rataRataLamaHari',
            'trenBulanan',
            'sumberAduanBreakdown'
        ));
    }
}