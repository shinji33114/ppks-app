<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClientAssessment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatistikController extends Controller
{
    public function index(): View
    {
        return view('statistik.index', $this->kumpulkanStatistik());
    }

    /**
     * Unduh seluruh angka yang tampil di dashboard statistik sebagai file Excel (.xlsx).
     * Data diambil dari kumpulkanStatistik() yang sama dengan halaman dashboard,
     * sehingga isi Excel selalu identik dengan yang dilihat Admin di layar.
     */
    public function export(): StreamedResponse
    {
        $s = $this->kumpulkanStatistik();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Statistik PPKS');

        $sheet->setCellValueExplicit('A1', 'Statistik Penerima Manfaat PPKS', DataType::TYPE_STRING);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValueExplicit(
            'A2',
            'Diunduh: ' . Carbon::now()->translatedFormat('d F Y H:i'),
            DataType::TYPE_STRING
        );

        $row = 4;

        $row = $this->tulisTabel($sheet, $row, 'Ringkasan', ['Indikator', 'Nilai'], [
            ['Total Penerima Manfaat', $s['totalKlien']],
            ['Rata-rata Lama Penanganan (hari)', $s['rataRataLamaHari'] ?? '-'],
            ['Klien Belum Dikategorikan', $s['totalTanpaKategori']],
        ]);

        $row = $this->tulisTabel($sheet, $row, 'Breakdown Status Klien', ['Status', 'Jumlah Klien'],
            array_map(fn ($r) => [$r['label'], $r['total']], $s['statusBreakdown'])
        );

        $row = $this->tulisTabel($sheet, $row, 'Tren Klien Masuk (6 Bulan Terakhir)', ['Bulan', 'Klien Masuk'],
            $s['trenBulanan']->map(fn ($r) => [$r['label'], $r['total']])->all()
        );

        $row = $this->tulisTabel($sheet, $row, 'Pilah Jenis Kelamin (klien yang sudah Assessment)', ['Jenis Kelamin', 'Jumlah'],
            collect($s['genderBreakdown'])->map(fn ($total, $label) => [$label, $total])->values()->all()
        );

        $row = $this->tulisTabel($sheet, $row, 'Pilah Kelompok Usia (klien yang sudah Assessment)', ['Kelompok Usia', 'Jumlah'],
            collect($s['ageBuckets'])->map(fn ($total, $label) => [$label, $total])->values()->all()
        );

        $row = $this->tulisTabel($sheet, $row, 'Kategori PPKS', ['Kategori', 'Jumlah Klien'],
            $s['kategoriBreakdown']->map(fn ($r) => [$r->kategori_ppks, $r->total])->all()
        );

        $this->tulisTabel($sheet, $row, '5 Sumber Aduan Terbanyak', ['Sumber Aduan', 'Jumlah Klien'],
            $s['sumberAduanBreakdown']->map(fn ($r) => [$r->sumber_aduan, $r->total])->all()
        );

        $sheet->getColumnDimension('A')->setWidth(48);
        $sheet->getColumnDimension('B')->setWidth(18);

        $namaFile = 'statistik-ppks-' . Carbon::now()->format('Y-m-d') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $namaFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Menulis satu blok (judul + header + baris data) mulai dari $row,
     * mengembalikan nomor baris kosong berikutnya (ada jeda satu baris antar blok).
     *
     * Teks ditulis sebagai string eksplisit, bukan lewat setCellValue biasa, karena
     * kolom seperti sumber_aduan / kategori_ppks diisi bebas oleh pengguna: tanpa ini
     * teks yang diawali "=" akan dibaca Excel sebagai rumus.
     */
    private function tulisTabel(Worksheet $sheet, int $row, string $judul, array $header, array $baris): int
    {
        $sheet->setCellValueExplicit("A{$row}", $judul, DataType::TYPE_STRING);
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
        $row++;

        $sheet->setCellValueExplicit("A{$row}", $header[0], DataType::TYPE_STRING);
        $sheet->setCellValueExplicit("B{$row}", $header[1], DataType::TYPE_STRING);
        $sheet->getStyle("A{$row}:B{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:B{$row}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E8F5E9');
        $row++;

        if (empty($baris)) {
            $sheet->setCellValueExplicit("A{$row}", 'Belum ada data.', DataType::TYPE_STRING);
            $row++;
        }

        foreach ($baris as [$label, $nilai]) {
            $sheet->setCellValueExplicit("A{$row}", (string) $label, DataType::TYPE_STRING);

            if (is_int($nilai) || is_float($nilai)) {
                $sheet->setCellValueExplicit("B{$row}", $nilai, DataType::TYPE_NUMERIC);
            } else {
                $sheet->setCellValueExplicit("B{$row}", (string) $nilai, DataType::TYPE_STRING);
            }
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $row++;
        }

        return $row + 1;
    }

    /**
     * Seluruh perhitungan statistik. Dipakai bersama oleh dashboard (index) dan unduhan Excel (export).
     */
    private function kumpulkanStatistik(): array
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

        // 3. Pilah jenis kelamin (dari data assessment yang sudah diisi Admin)
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

        // 4. Kategori PPKS
        // Dipindah dari problem_awal ke kategori_ppks: problem_awal sekarang teks bebas
        // (sesuai field 6 dokumen), tidak lagi cocok dikelompokkan untuk grafik ini.
        $kategoriBreakdown = Client::select('kategori_ppks', DB::raw('count(*) as total'))
            ->whereNotNull('kategori_ppks')
            ->where('kategori_ppks', '!=', '')
            ->groupBy('kategori_ppks')
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
        // Dihitung di PHP (bukan DATE_FORMAT) supaya jalan di MySQL maupun SQLite,
        // dan bulan tanpa klien tetap tampil dengan nilai 0.
        $awal = Carbon::now()->subMonths(5)->startOfMonth();
        $perBulan = Client::where('tanggal_masuk', '>=', $awal)
            ->pluck('tanggal_masuk')
            ->countBy(fn ($tgl) => Carbon::parse($tgl)->format('Y-m'));

        $trenBulanan = collect(range(0, 5))->map(function ($i) use ($awal, $perBulan) {
            $bulan = $awal->copy()->addMonths($i);

            return [
                'label' => $bulan->translatedFormat('M Y'),
                'total' => $perBulan[$bulan->format('Y-m')] ?? 0,
            ];
        });

        // Bonus: rekap sumber aduan (jalur rujukan masuk)
        $sumberAduanBreakdown = Client::select('sumber_aduan', DB::raw('count(*) as total'))
            ->groupBy('sumber_aduan')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return compact(
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
        );
    }
}
