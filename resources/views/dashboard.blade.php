@extends('layouts.spark')

@section('page-title', 'Dashboard')
@section('page-subtitle', 'Selamat datang di Sistem Pelayanan PPKS')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card border-light shadow-sm p-4">
            <h2 class="card-title mb-2">Selamat datang, {{ auth()->user()->name }}</h2>
            <p class="mb-0">
                Anda login sebagai:
                @foreach (auth()->user()->getRoleNames() as $role)
                    <span class="badge bg-success">{{ $role }}</span>
                @endforeach
            </p>
        </div>
    </div>

    {{-- C5: ringkasan pekerjaan yang menunggu, langsung terlihat di dashboard Peksos --}}
    @role('peksos')
        @php($pendingCount = \App\Models\Client::where('status', \App\Models\Client::STATUS_DRAFT)->count())
        <div class="col-md-6">
            <div class="card border-light shadow-sm p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-25"
                         style="width: 56px; height: 56px;">
                        <i class="bi bi-clipboard2-pulse text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Menunggu Assessment</div>
                        <div style="font-size: 1.75rem; font-weight: 700;">{{ $pendingCount }}</div>
                    </div>
                </div>
                @if ($pendingCount > 0)
                    <a href="{{ route('assessment.index') }}" class="btn-custom btn-custom-primary w-100 mt-3">
                        Lihat Daftar Klien
                    </a>
                @endif
            </div>
        </div>
    @endrole

    {{-- Ringkasan serupa untuk Admin RPS: klien yang sedang berjalan di Tahap 3 --}}
    @role('admin-rps')
        @php($activeCount = \App\Models\Client::whereIn('status', [\App\Models\Client::STATUS_ASSESSMENT, \App\Models\Client::STATUS_PENANGANAN])->count())
        <div class="col-md-6">
            <div class="card border-light shadow-sm p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-25"
                         style="width: 56px; height: 56px;">
                        <i class="bi bi-journal-text text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Klien Dalam Penanganan</div>
                        <div style="font-size: 1.75rem; font-weight: 700;">{{ $activeCount }}</div>
                    </div>
                </div>
                @if ($activeCount > 0)
                    <a href="{{ route('clients.index') }}" class="btn-custom btn-custom-primary w-100 mt-3">
                        Lihat Daftar Klien
                    </a>
                @endif
            </div>
        </div>
    @endrole
</div>
@endsection