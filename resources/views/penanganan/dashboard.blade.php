@extends('layouts.spark')

@section('page-title', 'Pendampingan')
@section('page-subtitle', 'Daftar penerima layanan yang siap atau sedang didampingi')

@section('content')
<div class="workflow-page">
    <section class="workflow-hero workflow-hero-handling mb-4">
        <div class="workflow-hero-copy">
            <span class="workflow-eyebrow"><i class="bi bi-heart-pulse me-1"></i> Tahap 3 · Pendampingan</span>
            <h2>Dampingi penerima layanan</h2>
            <p>Catat kegiatan dan tindak lanjut bagi penerima layanan yang sedang didampingi.</p>
        </div>
        <div class="workflow-hero-stat">
            <span class="workflow-stat-icon"><i class="bi bi-people"></i></span>
            <div><strong>{{ number_format($clients->total()) }}</strong><span>orang dalam daftar</span></div>
        </div>
    </section>

    <section class="workflow-panel">
        <div class="workflow-panel-header">
            <div class="workflow-panel-heading">
                <span class="workflow-panel-icon workflow-panel-icon-handling"><i class="bi bi-journal-medical"></i></span>
                <div>
                    <h3>Siap didampingi</h3>
                    <p>Penerima layanan yang sudah dinilai atau sedang didampingi</p>
                </div>
            </div>
            <span class="workflow-count">{{ number_format($clients->total()) }} orang</span>
        </div>

        <div class="table-responsive workflow-table-wrap">
            <table class="table-custom workflow-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama penerima layanan</th>
                        <th>Tanggal masuk</th>
                        <th>Status layanan</th>
                        <th class="text-end">Pilihan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td><span class="workflow-row-number">{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</span></td>
                            <td>
                                <div class="workflow-person">
                                    <span class="workflow-avatar workflow-avatar-handling"><i class="bi bi-person-fill"></i></span>
                                    <span class="workflow-person-name">{{ $client->nama_lengkap }}</span>
                                </div>
                            </td>
                            <td><span class="workflow-date"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}</span></td>
                            <td>
                                <span class="workflow-status {{ $client->status === \App\Models\Client::STATUS_PENANGANAN ? 'is-active' : 'is-ready' }}">
                                    <span></span>{{ $client->status_label }}
                                </span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('penanganan.index', $client->id) }}" class="btn-custom btn-custom-primary workflow-action">
                                    <i class="bi bi-journal-text me-1"></i> Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="workflow-empty">
                                <span class="workflow-empty-icon"><i class="bi bi-heart-pulse"></i></span>
                                <strong>Belum ada penerima layanan di daftar</strong>
                                <span>Orang yang siap didampingi akan muncul di sini.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($clients->hasPages())
            <div class="workflow-pagination">{{ $clients->links('pagination::bootstrap-5') }}</div>
        @endif
    </section>
</div>
@endsection
