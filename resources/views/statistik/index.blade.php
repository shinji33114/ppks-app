@extends('layouts.spark')

@section('page-title', 'Ringkasan Layanan')
@section('hide-page-header', true)

@push('styles')
    <style>
        .statistics-dashboard {
            --stats-ink: #172b25;
            --stats-muted: #71827a;
            --stats-green: #198754;
            --stats-border: #e8efeb;
            color: var(--stats-ink);
        }

        .statistics-hero {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.5rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            padding: clamp(1.5rem, 3vw, 2.25rem);
            border: 1px solid #dcebe2;
            border-radius: 1.25rem;
            background:
                radial-gradient(circle at 88% 15%, rgb(255 255 255 / 72%) 0, transparent 27%),
                linear-gradient(120deg, #e8f5ec 0%, #f5faf6 55%, #edf7f1 100%);
            box-shadow: 0 12px 30px rgb(29 70 47 / 6%);
        }

        .statistics-hero::after {
            position: absolute;
            right: 7%;
            bottom: -6.5rem;
            width: 13rem;
            height: 13rem;
            border: 1px solid rgb(25 135 84 / 10%);
            border-radius: 50%;
            content: "";
            pointer-events: none;
        }

        .statistics-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            margin-bottom: .65rem;
            color: #16804c;
            font-size: .75rem;
            font-weight: 800;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .statistics-hero h2 {
            margin: 0 0 .45rem;
            color: var(--stats-ink);
            font-size: clamp(1.45rem, 2.4vw, 2rem);
            font-weight: 800;
            letter-spacing: -.035em;
        }

        .statistics-hero p {
            max-width: 38rem;
            margin: 0;
            color: #60746a;
            line-height: 1.6;
        }

        .statistics-export-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            flex: 0 0 auto;
            padding: .8rem;
            border: 1px solid rgb(255 255 255 / 75%);
            border-radius: 1rem;
            background: rgb(255 255 255 / 78%);
            box-shadow: 0 8px 20px rgb(27 68 44 / 6%);
        }

        .statistics-export-icon {
            display: inline-flex;
            width: 3rem;
            height: 3rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: .9rem;
            background: #dff2e5;
            color: #168044;
            font-size: 1.4rem;
        }

        .statistics-export-button {
            display: inline-flex;
            min-height: 2.9rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            gap: .55rem;
            padding: .7rem 1.1rem;
            border: 1px solid #168044;
            border-radius: .75rem;
            background: linear-gradient(135deg, #198754 0%, #11683e 100%);
            box-shadow: 0 5px 12px rgb(25 135 84 / 20%);
            color: #fff;
            font-size: .9rem;
            font-weight: 700;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }

        .statistics-export-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgb(25 135 84 / 27%);
            color: #fff;
            filter: brightness(1.04);
        }

        .statistics-export-button:focus-visible {
            outline: 3px solid rgb(25 135 84 / 28%);
            outline-offset: 3px;
        }

        .statistics-kpi {
            position: relative;
            height: 100%;
            overflow: hidden;
            padding: 1.35rem;
            border: 1px solid var(--stats-border);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgb(27 54 42 / 4%);
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .statistics-kpi:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgb(27 54 42 / 9%);
        }

        .statistics-kpi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: .75rem;
            margin-bottom: 1.15rem;
        }

        .statistics-kpi-icon {
            display: inline-flex;
            width: 2.8rem;
            height: 2.8rem;
            align-items: center;
            justify-content: center;
            border-radius: .9rem;
            font-size: 1.25rem;
        }

        .statistics-kpi-icon--green { background: #e6f5eb; color: #16804c; }
        .statistics-kpi-icon--blue { background: #eaf1ff; color: #3569c8; }
        .statistics-kpi-icon--amber { background: #fff4df; color: #b77912; }

        .statistics-kpi-label {
            color: var(--stats-muted);
            font-size: .85rem;
            font-weight: 600;
        }

        .statistics-kpi-value {
            color: var(--stats-ink);
            font-size: clamp(1.8rem, 3vw, 2.25rem);
            font-weight: 800;
            letter-spacing: -.045em;
            line-height: 1;
        }

        .statistics-kpi-note {
            margin-top: .65rem;
            color: var(--stats-muted);
            font-size: .75rem;
        }

        .statistics-section {
            height: 100%;
            padding: 1.35rem;
            border: 1px solid var(--stats-border);
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 8px 24px rgb(27 54 42 / 4%);
        }

        .statistics-section-heading {
            display: flex;
            align-items: flex-start;
            gap: .75rem;
            margin-bottom: .6rem;
        }

        .statistics-section-icon {
            display: inline-flex;
            width: 2.45rem;
            height: 2.45rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: .75rem;
            background: #f0f6f2;
            color: var(--stats-green);
            font-size: 1rem;
        }

        .statistics-section-heading h3 {
            margin: .1rem 0 .2rem;
            color: var(--stats-ink);
            font-size: 1rem;
            font-weight: 750;
        }

        .statistics-section-heading p {
            margin: 0;
            color: var(--stats-muted);
            font-size: .76rem;
        }

        .statistics-number-item {
            display: flex;
            min-height: 3.25rem;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            padding: .65rem .8rem;
            border: 1px solid var(--stats-border);
            border-radius: .75rem;
            background: #fbfdfb;
        }

        .statistics-number-label {
            color: var(--stats-ink);
            font-size: .85rem;
            font-weight: 600;
        }

        .statistics-number-value {
            color: var(--stats-green);
            font-size: 1.15rem;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
        }

        @media (max-width: 575.98px) {
            .statistics-hero {
                align-items: stretch;
                flex-direction: column;
            }

            .statistics-export-card {
                justify-content: space-between;
                flex-wrap: wrap;
            }

            .statistics-export-button {
                flex: 1 0 100%;
                padding-inline: .85rem;
            }
        }
    </style>
@endpush

@section('content')

<div class="statistics-dashboard">
<section class="statistics-hero">
    <div>
        <div class="statistics-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Ringkasan Layanan</div>
        <p>Lihat jumlah penerima layanan dan perkembangan pelayanan sosial.</p>
</div>
    <div class="statistics-export-card">
        <span class="statistics-export-icon" aria-hidden="true">
            <i class="bi bi-file-earmark-spreadsheet"></i>
        </span>
        <div>
            <div class="fw-bold text-dark small">Unduh laporan</div>
            <div class="small text-muted">Simpan ringkasan dalam berkas Excel</div>
        </div>
        <a href="{{ route('statistik.export') }}" class="statistics-export-button">
            <i class="bi bi-download" aria-hidden="true"></i>
            <span>Unduh laporan Excel</span>
        </a>
    </div>
</section>

<div class="row g-4 mb-2">
    <div class="col-md-4">
        <div class="statistics-kpi">
            <div class="statistics-kpi-top">
                <div class="statistics-kpi-label">Total Penerima Manfaat</div>
                <span class="statistics-kpi-icon statistics-kpi-icon--green" aria-hidden="true"><i class="bi bi-people"></i></span>
            </div>
            <div class="statistics-kpi-value">{{ number_format($totalKlien, 0, ',', '.') }}</div>
            <div class="statistics-kpi-note">Semua penerima layanan yang sudah didaftarkan</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="statistics-kpi">
            <div class="statistics-kpi-top">
                <div class="statistics-kpi-label">Rata-rata Lama Pendampingan</div>
                <span class="statistics-kpi-icon statistics-kpi-icon--blue" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
            </div>
            <div class="statistics-kpi-value">
                {{ $rataRataLamaHari !== null ? $rataRataLamaHari . ' hari' : '—' }}
            </div>
            <div class="statistics-kpi-note">{{ $rataRataLamaHari === null ? 'Belum ada layanan yang selesai' : 'Waktu dari pendaftaran sampai layanan selesai atau rujukan' }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="statistics-kpi">
            <div class="statistics-kpi-top">
                <div class="statistics-kpi-label">Jenis kebutuhan belum diisi</div>
                <span class="statistics-kpi-icon statistics-kpi-icon--amber" aria-hidden="true"><i class="bi bi-tags"></i></span>
            </div>
            <div class="statistics-kpi-value">{{ number_format($totalTanpaKategori, 0, ',', '.') }}</div>
            <div class="statistics-kpi-note">Data ini perlu dilengkapi oleh petugas</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-2">
    <div class="col-lg-6">
        <div class="statistics-section">
            <div class="statistics-section-heading">
                <span class="statistics-section-icon" aria-hidden="true"><i class="bi bi-bar-chart-line"></i></span>
                <div><h3>Jumlah Penerima Layanan per Tahap</h3><p>Jumlah penerima layanan pada setiap tahap</p></div>
            </div>
            <div class="row row-cols-2 row-cols-lg-3 g-2">
                @foreach ($statusBreakdown as $item)
                    <div class="col">
                        <div class="statistics-number-item">
                            <span class="statistics-number-label">{{ $item['label'] }}</span>
                            <span class="statistics-number-value">{{ number_format($item['total'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="statistics-section">
            <div class="statistics-section-heading">
                <span class="statistics-section-icon" aria-hidden="true"><i class="bi bi-graph-up-arrow"></i></span>
                <div><h3>Pendaftaran Baru</h3><p>Jumlah penerima layanan yang didaftarkan dalam 12 bulan terakhir</p></div>
            </div>
            <div id="chart-tren"></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-2">
    <div class="col-12 col-md-6">
        <div class="statistics-section">
            <div class="statistics-section-heading">
                <span class="statistics-section-icon" aria-hidden="true"><i class="bi bi-gender-ambiguous"></i></span>
                <div><h3>Jenis Kelamin</h3><p>Jumlah penerima layanan berdasarkan jenis kelamin</p></div>
            </div>
            <div class="row row-cols-2 g-2">
                @foreach ($genderBreakdown as $label => $total)
                    <div class="col">
                        <div class="statistics-number-item">
                            <span class="statistics-number-label">{{ $label }}</span>
                            <span class="statistics-number-value">{{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-muted small mt-2 mb-0">*Dihitung dari penerima layanan yang sudah dinilai</p>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="statistics-section">
            <div class="statistics-section-heading">
                <span class="statistics-section-icon" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                <div><h3>Kelompok Usia</h3><p>Jumlah penerima layanan berdasarkan usia</p></div>
            </div>
            <div class="row row-cols-2 row-cols-lg-3 g-2">
                @foreach ($ageBuckets as $label => $total)
                    <div class="col">
                        <div class="statistics-number-item">
                            <span class="statistics-number-label">{{ $label }}</span>
                            <span class="statistics-number-value">{{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="text-muted small mt-2 mb-0">*Dihitung dari penerima layanan yang sudah dinilai</p>
        </div>
    </div>
 </div>

<div class="row g-4 mb-2">
    <div class="col-12">
        <div class="statistics-section">
            <div class="statistics-section-heading">
                <span class="statistics-section-icon" aria-hidden="true"><i class="bi bi-collection"></i></span>
                <div><h3>Jenis Kebutuhan Layanan</h3><p>Jumlah penerima layanan untuk setiap jenis kebutuhan</p></div>
            </div>
            @if ($kategoriBreakdown->isEmpty())
                <p class="text-muted">Belum ada data kategori.</p>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                    @foreach ($kategoriBreakdown as $item)
                        <div class="col">
                            <div class="statistics-number-item">
                                <span class="statistics-number-label">{{ $item->kategori_ppks }}</span>
                                <span class="statistics-number-value">{{ number_format($item->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

    <div class="row g-4">
        <div class="col-12">
            <div class="statistics-section">
                <div class="statistics-section-heading">
                    <span class="statistics-section-icon" aria-hidden="true"><i class="bi bi-megaphone"></i></span>
                    <div><h3>5 Sumber Aduan Terbanyak</h3><p>Jumlah penerima layanan dari setiap sumber laporan</p></div>
                </div>
                @if ($sumberAduanBreakdown->isEmpty())
                    <p class="text-muted mb-0">Belum ada data sumber aduan.</p>
                @else
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-2">
                        @foreach ($sumberAduanBreakdown as $item)
                            <div class="col">
                                <div class="statistics-number-item">
                                    <span class="statistics-number-label">{{ $item->sumber_aduan }}</span>
                                    <span class="statistics-number-value">{{ number_format($item->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartText = '#71827a';
    const chartGrid = '#edf2ef';
    const chartBase = {
        chart: {
            fontFamily: 'inherit',
            foreColor: chartText,
            parentHeightOffset: 0,
            animations: { speed: 450 },
        },
        grid: { borderColor: chartGrid, strokeDashArray: 4 },
        tooltip: { theme: 'light' },
    };

    // Tren bulanan (line chart)
    new ApexCharts(document.querySelector("#chart-tren"), {
        ...chartBase,
        chart: { ...chartBase.chart, type: 'area', height: 270, toolbar: { show: false } },
        series: [{ name: 'Pendaftaran Baru', data: @json($trenBulanan->pluck('total')) }],
        xaxis: {
            categories: @json($trenBulanan->pluck('label')),
            labels: { style: { colors: chartText } },
            axisBorder: { show: false },
            axisTicks: { show: false },
        },
        yaxis: { min: 0, forceNiceScale: true, labels: { style: { colors: chartText } } },
        colors: ['#3974d5'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { opacityFrom: .28, opacityTo: .02, stops: [0, 90, 100] } },
        markers: { size: 4, strokeWidth: 2, hover: { size: 6 } },
        dataLabels: { enabled: false },
        grid: { ...chartBase.grid, padding: { left: 8, right: 8 } },
    }).render();

});
</script>
@endpush