@extends('layouts.spark')

@section('page-title', 'Assessment')
@section('page-subtitle', 'Daftar klien yang menunggu assessment')

@section('content')
<div class="workflow-page">
    <section class="workflow-hero workflow-hero-assessment mb-4">
        <div class="workflow-hero-copy">
            <span class="workflow-eyebrow"><i class="bi bi-clipboard2-pulse me-1"></i> Tahap 2 · Assessment</span>
            <h2>Mulai dari memahami kebutuhan</h2>
            <p>Lengkapi assessment psikososial untuk menentukan dukungan yang paling sesuai bagi setiap klien.</p>
        </div>
        <div class="workflow-hero-stat">
            <span class="workflow-stat-icon"><i class="bi bi-hourglass-split"></i></span>
            <div><strong>{{ number_format($clients->total()) }}</strong><span>menunggu assessment</span></div>
        </div>
    </section>

    <section class="workflow-panel">
        <div class="workflow-panel-header">
            <div class="workflow-panel-heading">
                <span class="workflow-panel-icon"><i class="bi bi-inboxes"></i></span>
                <div>
                    <h3>Antrean assessment</h3>
                    <p>Klien yang siap diproses pada Tahap 2</p>
                </div>
            </div>
            <span class="workflow-count">{{ number_format($clients->total()) }} klien</span>
        </div>

        <div class="table-responsive workflow-table-wrap">
            <table class="table-custom workflow-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Klien</th>
                        <th>Tanggal Masuk</th>
                        <th>Sumber Aduan</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td><span class="workflow-row-number">{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</span></td>
                            <td>
                                <div class="workflow-person">
                                    <span class="workflow-avatar workflow-avatar-assessment"><i class="bi bi-person-fill"></i></span>
                                    <span class="workflow-person-name">{{ $client->nama_lengkap }}</span>
                                </div>
                            </td>
                            <td><span class="workflow-date"><i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}</span></td>
                            <td><span class="workflow-source">{{ $client->sumber_aduan }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('assessment.create', $client->id) }}" class="btn-custom btn-custom-primary workflow-action">
                                    <i class="bi bi-play-fill me-1"></i> Mulai Assessment
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="workflow-empty">
                                <span class="workflow-empty-icon"><i class="bi bi-clipboard2-check"></i></span>
                                <strong>Antrean assessment kosong</strong>
                                <span>Klien yang siap di-assessment akan muncul di sini.</span>
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
