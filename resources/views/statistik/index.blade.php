@extends('layouts.spark')

@section('page-title', 'Dashboard Statistik')
@section('page-subtitle', 'Rekap data Penerima Manfaat PPKS')

@section('content')

<div class="d-flex justify-content-end mb-3">
    <a href="{{ route('statistik.export') }}" class="btn-custom btn-custom-primary">
        <i class="bi bi-file-earmark-excel"></i> Unduh Excel
    </a>
</div>

<div class="row g-4 mb-2">
    <div class="col-md-4">
        <div class="card border-light shadow-sm p-4 text-center">
            <div class="text-muted small mb-1">Total Penerima Manfaat</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ $totalKlien }}</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-light shadow-sm p-4 text-center">
            <div class="text-muted small mb-1">Rata-rata Lama Penanganan</div>
            <div style="font-size: 2rem; font-weight: 700;">
                {{ $rataRataLamaHari !== null ? $rataRataLamaHari . ' hari' : '—' }}
            </div>
            @if ($rataRataLamaHari === null)
                <div class="text-muted small">Belum ada klien selesai</div>
            @endif
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-light shadow-sm p-4 text-center">
            <div class="text-muted small mb-1">Klien Belum Dikategorikan</div>
            <div style="font-size: 2rem; font-weight: 700;">{{ $totalTanpaKategori }}</div>
        </div>
    </div>
</div>

<div class="row g-4 mb-2">
    <div class="col-lg-6">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-3">Breakdown Status Klien</h5>
            <div id="chart-status"></div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-3">Tren Klien Masuk (6 Bulan Terakhir)</h5>
            <div id="chart-tren"></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-2">
    <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-3">Pilah Jenis Kelamin</h5>
            <div id="chart-gender"></div>
            <p class="text-muted small mt-2 mb-0">*Dari klien yang sudah melalui Assessment</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-3">Pilah Kelompok Usia</h5>
            <div id="chart-usia"></div>
            <p class="text-muted small mt-2 mb-0">*Dari klien yang sudah melalui Assessment</p>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-3">Kategori PPKS</h5>
            @if ($kategoriBreakdown->isEmpty())
                <p class="text-muted">Belum ada data kategori.</p>
            @else
                <div id="chart-kategori"></div>
            @endif
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="table-card-custom">
            <div class="table-header-control">
                <h5 class="card-title mb-0">5 Sumber Aduan Terbanyak</h5>
            </div>
            <div class="table-responsive">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>Sumber Aduan</th>
                            <th>Jumlah Klien</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sumberAduanBreakdown as $row)
                            <tr>
                                <td>{{ $row->sumber_aduan }}</td>
                                <td>{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center py-3">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Breakdown Status (bar chart)
    new ApexCharts(document.querySelector("#chart-status"), {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        series: [{ name: 'Jumlah Klien', data: @json(array_column($statusBreakdown, 'total')) }],
        xaxis: { categories: @json(array_column($statusBreakdown, 'label')) },
        colors: ['#2e7d32'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
    }).render();

    // Tren bulanan (line chart)
    new ApexCharts(document.querySelector("#chart-tren"), {
        chart: { type: 'line', height: 260, toolbar: { show: false } },
        series: [{ name: 'Klien Masuk', data: @json($trenBulanan->pluck('total')) }],
        xaxis: { categories: @json($trenBulanan->pluck('label')) },
        colors: ['#1565c0'],
        stroke: { curve: 'smooth', width: 3 },
    }).render();

    // Gender (donut)
    new ApexCharts(document.querySelector("#chart-gender"), {
        chart: { type: 'donut', height: 230 },
        series: @json(array_values($genderBreakdown)),
        labels: @json(array_keys($genderBreakdown)),
        colors: ['#1565c0', '#d81b60'],
    }).render();

    // Usia (donut)
    new ApexCharts(document.querySelector("#chart-usia"), {
        chart: { type: 'donut', height: 230 },
        series: @json(array_values($ageBuckets)),
        labels: @json(array_keys($ageBuckets)),
        colors: ['#f9a825', '#2e7d32', '#6a1b9a'],
    }).render();

    @if ($kategoriBreakdown->isNotEmpty())
    // Kategori PPKS (bar horizontal)
    new ApexCharts(document.querySelector("#chart-kategori"), {
        chart: { type: 'bar', height: 230, toolbar: { show: false } },
        series: [{ name: 'Jumlah', data: @json($kategoriBreakdown->pluck('total')) }],
        xaxis: { categories: @json($kategoriBreakdown->pluck('kategori_ppks')) },
        plotOptions: { bar: { horizontal: true, borderRadius: 4 } },
        colors: ['#ef6c00'],
    }).render();
    @endif

});
</script>
@endpush