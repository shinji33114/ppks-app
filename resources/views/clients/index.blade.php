@extends('layouts.spark')


@section('content')
@php
    $stages = [
        'draft' => 'Pendaftaran',
        'assessment' => 'Penilaian Informasi',
        'penanganan' => 'Pendampingan',
        'final' => 'Selesai / Dirujuk',
    ];
    $stageIcons = [
        'draft' => 'bi-person-vcard',
        'assessment' => 'bi-clipboard2-pulse',
        'penanganan' => 'bi-heart-pulse',
        'final' => 'bi-patch-check',
    ];
@endphp

@push('styles')
<style>
    .client-page { --client-green: #13795b; --client-ink: #173b32; }
    .client-overview {
        position: relative; display: flex; min-height: 118px; align-items: center; justify-content: space-between;
        gap: 1.5rem; overflow: hidden; padding: 1.5rem 1.75rem; border: 1px solid rgba(19, 121, 91, .12);
        border-radius: 1rem; background: linear-gradient(115deg, rgba(255,255,255,.97), rgba(234,247,241,.96));
        box-shadow: 0 10px 28px rgba(22, 59, 50, .06);
    }
    .client-overview::after {
        position: absolute; top: -80px; right: 14%; width: 220px; height: 220px; border-radius: 50%;
        background: rgba(19, 121, 91, .06); content: '';
    }
    .client-overview-content, .client-overview-total { position: relative; z-index: 1; }
    .client-overview-content { display: flex; align-items: center; gap: 1rem; }
    .client-overview-icon {
        display: grid; width: 54px; height: 54px; flex: 0 0 auto; place-items: center; border-radius: 16px;
        background: #e1f2e9; color: var(--client-green); font-size: 1.45rem;
    }
    .client-overview-eyebrow { margin-bottom: .25rem; color: var(--client-green); font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .client-overview-title { margin: 0; color: var(--client-ink); font-size: 1.2rem; font-weight: 700; }
    .client-overview-copy { margin: .25rem 0 0; color: #71827b; font-size: .9rem; }
    .client-overview-total { min-width: 110px; text-align: right; }
    .client-overview-total strong { display: block; color: var(--client-ink); font-size: 1.8rem; line-height: 1.1; }
    .client-overview-total span { color: #71827b; font-size: .8rem; }
    .client-stage-card {
        --stage-color: #13795b; --stage-tint: #e5f4eb;
        position: relative; display: block; height: 100%; overflow: hidden; padding: 1.15rem;
        border: 1px solid rgba(24, 55, 45, .09); border-radius: .9rem; background: rgba(255,255,255,.96);
        color: inherit; text-decoration: none; box-shadow: 0 5px 18px rgba(22, 59, 50, .045);
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
    }
    .client-stage-card::before { position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--stage-color); content: ''; opacity: .55; }
    .client-stage-card:hover, .client-stage-card:focus-visible {
        transform: translateY(-3px); border-color: color-mix(in srgb, var(--stage-color), white 68%);
        color: inherit; box-shadow: 0 12px 24px rgba(22, 59, 50, .1); outline: none;
    }
    .client-stage-card.active { border-color: color-mix(in srgb, var(--stage-color), white 52%); box-shadow: 0 0 0 3px color-mix(in srgb, var(--stage-color), transparent 88%), 0 8px 20px rgba(22, 59, 50, .07); }
    .client-stage-card.active::before { width: 5px; opacity: 1; }
    .client-stage-card[data-stage="assessment"] { --stage-color: #3676c8; --stage-tint: #eaf1fc; }
    .client-stage-card[data-stage="penanganan"] { --stage-color: #bc7b19; --stage-tint: #fff3dd; }
    .client-stage-card[data-stage="final"] { --stage-color: #7654ad; --stage-tint: #f0eafa; }
    .client-stage-top { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .client-stage-index { display: inline-flex; align-items: center; gap: .45rem; color: #82918b; font-size: .72rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
    .client-stage-index i { display: grid; width: 30px; height: 30px; place-items: center; border-radius: 9px; background: var(--stage-tint); color: var(--stage-color); font-size: .95rem; }
    .client-stage-count { padding: .25rem .6rem; border-radius: 2rem; background: var(--stage-tint); color: var(--stage-color); font-size: .75rem; font-weight: 700; white-space: nowrap; }
    .client-stage-title { margin: .9rem 0 .15rem; color: #243d34; font-size: 1rem; font-weight: 700; }
    .client-stage-subtitle { color: #82918b; font-size: .8rem; }
    .client-stage-footer { display: flex; align-items: center; justify-content: space-between; margin-top: .85rem; padding-top: .7rem; border-top: 1px solid #eef1ef; color: var(--stage-color); font-size: .77rem; font-weight: 600; }
    .client-list-card { overflow: hidden; border: 1px solid rgba(24, 55, 45, .09); border-radius: 1rem; background: rgba(255,255,255,.97); box-shadow: 0 8px 26px rgba(22, 59, 50, .055); }
    .client-list-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.5rem; }
    .client-list-heading { display: flex; align-items: center; gap: .8rem; }
    .client-list-heading-icon { display: grid; width: 42px; height: 42px; place-items: center; border-radius: 12px; background: #edf5f0; color: var(--client-green); font-size: 1.1rem; }
    .client-list-title { margin: 0; color: var(--client-ink); font-size: 1rem; font-weight: 700; }
    .client-list-caption { margin-top: .15rem; color: #84928c; font-size: .8rem; }
    .client-result-count { padding: .35rem .7rem; border-radius: 2rem; background: #f1f5f3; color: #53665d; font-size: .78rem; font-weight: 600; white-space: nowrap; }
    .client-filter-bar { display: flex; flex-wrap: wrap; align-items: end; gap: .85rem; padding: 1rem 1.5rem; border-top: 1px solid #edf0ee; border-bottom: 1px solid #edf0ee; background: #fafcfb; }
    .client-filter-field { min-width: 220px; }
    .client-search-field { flex: 1 1 320px; }
    .client-filter-field label { display: block; margin-bottom: .35rem; color: #65766e; font-size: .76rem; font-weight: 600; }
    .client-filter-input { min-height: 40px; border-color: #dfe7e2; border-radius: .65rem; }
    .client-filter-input:focus { border-color: #65a68a; box-shadow: 0 0 0 .2rem rgba(19, 121, 91, .12); }
    .client-table-wrap { padding: .2rem 1rem 0; }
    .client-list-card .table-custom { margin-bottom: 0; }
    .client-list-card .table-custom thead th { padding: .9rem .75rem; border-bottom: 1px solid #e9efeb; background: #fff; color: #82918b; font-size: .7rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; }
    .client-list-card .table-custom tbody td { padding: .95rem .75rem; border-bottom: 1px solid #f0f3f1; vertical-align: middle; }
    .client-list-card .table-custom tbody tr:last-child td { border-bottom: 0; }
    .client-list-card .table-custom tbody tr:hover { background: #f8fbf9; }
    .client-row-number { color: #98a59f; font-size: .8rem; font-weight: 600; }
    .client-name-cell { display: flex; align-items: center; gap: .7rem; }
    .client-avatar { display: grid; width: 38px; height: 38px; flex: 0 0 auto; place-items: center; border-radius: 12px; background: #e8f3ed; color: var(--client-green); font-size: .95rem; }
    .client-name-primary { color: #263d34; font-weight: 650; }
    .client-name-alias { margin-top: .1rem; color: #84928c; font-size: .78rem; }
    .client-date { color: #52645c; font-size: .85rem; white-space: nowrap; }
    .client-source { max-width: 240px; color: #687970; font-size: .85rem; }
    .client-empty-state { padding: 3rem 1rem !important; text-align: center; }
    .client-empty-icon { display: grid; width: 52px; height: 52px; margin: 0 auto .75rem; place-items: center; border-radius: 16px; background: #f0f5f2; color: #7b9588; font-size: 1.4rem; }
    .client-pagination { padding: 1rem 1.5rem; border-top: 1px solid #edf0ee; }
    @media (max-width: 767.98px) {
        .client-overview { align-items: flex-start; padding: 1.2rem; }
        .client-overview-total { min-width: auto; }
        .client-overview-total strong { font-size: 1.5rem; }
        .client-overview-icon { width: 46px; height: 46px; }
        .client-list-header { align-items: flex-start; padding: 1rem; }
        .client-filter-bar { padding: 1rem; }
        .client-filter-field { flex: 1 1 100%; }
        .client-table-wrap { padding: .2rem .5rem 0; }
        .client-pagination { padding: 1rem; }
        .client-stage-top { flex-wrap: wrap; }
        .client-stage-card { min-width: 0; }
        .client-table-wrap .table-custom { min-width: 0; display: block; }
        .client-table-wrap .table-custom thead { display: none; }
        .client-table-wrap .table-custom tbody { display: grid; gap: .65rem; padding: .65rem; }
        .client-table-wrap .table-custom tbody tr.client-data-row {
            display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .65rem .85rem;
            padding: .85rem; border: 1px solid #edf1ee; border-radius: .85rem; background: #fff;
            box-shadow: 0 3px 12px rgba(22, 59, 50, .035);
        }
        .client-table-wrap .table-custom tbody tr.client-data-row td {
            display: flex; min-width: 0; flex-direction: column; align-items: flex-start; gap: .2rem;
            padding: 0; border: 0;
        }
        .client-table-wrap .table-custom tbody tr.client-data-row td::before {
            color: #899890; content: attr(data-label); font-size: .65rem; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
        }
        .client-table-wrap .table-custom tbody tr.client-data-row td:first-child { display: none; }
        .client-table-wrap .table-custom tbody tr.client-data-row td:nth-child(2) { grid-column: 1 / -1; }
        .client-table-wrap .table-custom tbody tr.client-data-row td:nth-child(2)::before { content: none; }
        .client-table-wrap .table-custom tbody tr.client-data-row td:nth-child(2) .client-name-cell { width: 100%; }
        .client-table-wrap .table-custom tbody tr.client-data-row td:nth-child(6) {
            grid-column: 1 / -1; flex-direction: row; align-items: center; justify-content: space-between;
            padding-top: .5rem; border-top: 1px solid #edf1ee;
        }
        .client-table-wrap .table-custom tbody tr.client-data-row td:nth-child(6) .table-action-group { margin-left: auto; }
        .client-table-wrap .table-custom tbody tr.client-empty-row { display: block; }
        .client-table-wrap .table-custom tbody tr.client-empty-row td { display: block; }
    }
    @media (prefers-reduced-motion: reduce) {
        .client-stage-card { transition: none; }
        .client-stage-card:hover, .client-stage-card:focus-visible { transform: none; }
    }
</style>
@endpush

<div class="client-page">
    <section class="client-overview mb-4">
        <div class="client-overview-content">
            <div class="client-overview-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="client-overview-eyebrow">Data penerima layanan</div>
                <h2 class="client-overview-title">Periksa perkembangan layanan</h2>
                <p class="client-overview-copy">Pilih tahap layanan atau cari berdasarkan tanggal masuk.</p>
            </div>
        </div>
        <div class="client-overview-total">
            <strong>{{ number_format($clients->total()) }}</strong>
            <span>{{ $stage || $tanggalMasuk ? 'penerima layanan ditemukan' : 'penerima layanan tercatat' }}</span>
        </div>
    </section>

    <section class="row g-3 mb-4" aria-label="Tahap layanan">
        @foreach ($stages as $stageKey => $stageName)
            <div class="col-6 col-xl-3">
                <a
                    href="{{ route('clients.index', array_merge(request()->except(['page', 'stage']), ['stage' => $stageKey])) }}"
                    class="client-stage-card {{ $stage === $stageKey ? 'active' : '' }}"
                    data-stage="{{ $stageKey }}"
                    @if ($stage === $stageKey) aria-current="step" @endif
                >
                    <div class="client-stage-top">
                        <span class="client-stage-index"><i class="bi {{ $stageIcons[$stageKey] }}"></i> {{ $stageName }}</span>
                        <span class="client-stage-count">{{ number_format($stageCounts[$stageKey]) }} orang</span>
                    </div>
                    <div class="client-stage-subtitle">
                        @if ($stageKey === 'draft')
                            Menunggu penilaian
                        @elseif ($stageKey === 'assessment')
                            Sedang dinilai
                        @elseif ($stageKey === 'penanganan')
                            Sedang ditangani
                        @else
                            Layanan selesai atau penerima layanan dirujuk
                        @endif
                    </div>
                    <div class="client-stage-footer">
                        <span>{{ $stage === $stageKey ? 'Tahap yang dipilih' : 'Lihat tahap ini' }}</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </a>
            </div>
        @endforeach
    </section>

    <section class="client-list-card">
        <div class="client-list-header">
            <div class="client-list-heading">
                <div class="client-list-heading-icon"><i class="bi bi-list-ul"></i></div>
                <div>
                    <h3 class="client-list-title">{{ $stage ? 'Penerima layanan — ' . $stages[$stage] : 'Daftar Penerima Layanan' }}</h3>
                    <div class="client-list-caption">Orang yang sudah didaftarkan untuk menerima layanan</div>
                </div>
            </div>
            
        </div>

        @if ($stage || $tanggalMasuk)
            <div class="px-4 pb-3">
                <div class="d-flex flex-wrap gap-2">
                    @if ($stage)
                        <a href="{{ route('clients.index', request()->except(['page', 'stage'])) }}" class="badge rounded-pill text-bg-success text-decoration-none">
                            <i class="bi bi-activity me-1"></i>{{ $stages[$stage] }} <i class="bi bi-x ms-1"></i>
                        </a>
                    @endif
                    @if ($tanggalMasuk)
                        <a href="{{ route('clients.index', request()->except(['page', 'tanggal_masuk'])) }}" class="badge rounded-pill text-bg-light border text-decoration-none">
                            <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($tanggalMasuk)->translatedFormat('d M Y') }} <i class="bi bi-x ms-1"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        <form method="GET" action="{{ route('clients.index') }}" class="client-filter-bar">
            @if ($stage)
                <input type="hidden" name="stage" value="{{ $stage }}">
            @endif
            <div class="client-filter-field client-search-field">
                <label for="filter-search"><i class="bi bi-search me-1"></i> Cari nama penerima layanan</label>
                <input
                    type="search"
                    id="filter-search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Ketik nama atau nama panggilan"
                    class="form-control client-filter-input"
                >
            </div>
            <div class="client-filter-field">
                <label for="filter-tanggal-masuk"><i class="bi bi-calendar3 me-1"></i> Tanggal masuk</label>
                <input
                    type="date"
                    id="filter-tanggal-masuk"
                    name="tanggal_masuk"
                    value="{{ $tanggalMasuk }}"
                    class="form-control client-filter-input"
                    onchange="this.form.requestSubmit()"
                >
            </div>
            <button type="submit" class="btn-custom btn-custom-primary">
                <i class="bi bi-search me-1"></i> Cari
            </button>
            @if (request('search'))
                <a href="{{ route('clients.index', request()->except(['page', 'search'])) }}" class="btn-custom btn-custom-secondary">
                    Hapus pencarian
                </a>
            @endif
            @if ($tanggalMasuk)
                <a href="{{ route('clients.index', request()->except(['page', 'tanggal_masuk'])) }}" class="btn-custom btn-custom-secondary">
                    Hapus tanggal
                </a>
            @endif
            @if ($stage)
                <a href="{{ route('clients.index', request()->except(['page', 'stage'])) }}" class="small ms-auto">
                    Tampilkan semua tahap
                </a>
            @endif
        </form>

        <div class="table-responsive client-table-wrap">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Penerima Layanan</th>
                        <th>Tanggal Masuk</th>
                        <th>Sumber Aduan</th>
                        <th>Status Layanan</th>
                        <th class="text-end">Pilihan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr class="client-data-row">
                            <td data-label="Nomor"><span class="client-row-number">{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</span></td>
                            <td data-label="Nama penerima layanan">
                                <div class="client-name-cell">
                                    <span class="client-avatar"><i class="bi bi-person-fill"></i></span>
                                    <div>
                                        <div class="client-name-primary">{{ $client->nama_lengkap }}</div>
                                        @if ($client->nama_alias)
                                            <div class="client-name-alias">alias {{ $client->nama_alias }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Tanggal masuk"><span class="client-date"><i class="bi bi-calendar3 me-1 text-muted"></i>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}</span></td>
                            <td data-label="Sumber aduan"><div class="client-source text-truncate">{{ $client->sumber_aduan }}</div></td>
                            <td data-label="Status">
                                <span class="badge-table {{ $client->status_badge_class }}">{{ $client->status_label }}</span>
                            </td>
                            <td data-label="Pilihan" class="text-end">
                                <div class="table-action-group justify-content-end">
                                    <a href="{{ route('clients.show', $client->id) }}" class="table-btn-action" title="Lihat detail" aria-label="Lihat detail {{ $client->nama_lengkap }}">
                                        <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="client-empty-row">
                            <td colspan="6" class="client-empty-state">
                                <div class="client-empty-icon"><i class="bi bi-person-search"></i></div>
                                <div class="fw-semibold text-dark">Data tidak ditemukan</div>
                                <div class="text-muted small mt-1">Periksa nama, tanggal masuk, atau tahap layanan.</div>
                                @if ($stage || $tanggalMasuk)
                                    <a href="{{ route('clients.index') }}" class="btn-custom btn-custom-secondary mt-3">Tampilkan semua data</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
            <div class="client-pagination">
                {{ $clients->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
</div>
@endsection