@extends('layouts.spark')

@section('page-title', 'Beranda')
@section('page-subtitle', 'Ringkasan pekerjaan Anda hari ini')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="card border-light shadow-sm p-4">
            <h2 class="card-title mb-2">Selamat datang, {{ auth()->user()->name }}</h2>
            <p class="mb-0">
                Tugas Anda:
                @php
                    $roleLabels = ['admin' => 'Petugas', 'super-admin' => 'Pengelola utama'];
                @endphp
                @foreach (auth()->user()->getRoleNames() as $role)
                    <span class="badge bg-success">{{ $roleLabels[$role] ?? $role }}</span>
                @endforeach
            </p>
        </div>
    </div>

    {{-- Ringkasan penilaian yang menunggu --}}
    @hasanyrole('admin|super-admin')
        @php($pendingCount = \App\Models\Client::where('status', \App\Models\Client::STATUS_DRAFT)->count())
        <div class="col-md-6">
            <div class="card border-light shadow-sm p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-25"
                         style="width: 56px; height: 56px;">
                        <i class="bi bi-clipboard2-pulse text-warning" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Menunggu penilaian</div>
                        <div style="font-size: 1.75rem; font-weight: 700;">{{ $pendingCount }}</div>
                    </div>
                </div>
                @if ($pendingCount > 0)
                    <a href="{{ route('assessment.index') }}" class="btn-custom btn-custom-primary w-100 mt-3">
                        Lihat daftar penerima layanan
                    </a>
                @endif
            </div>
        </div>
    @endhasanyrole

    {{-- Ringkasan penerima layanan yang sedang didampingi --}}
    @hasanyrole('admin|super-admin')
        @php($activeCount = \App\Models\Client::whereIn('status', [\App\Models\Client::STATUS_ASSESSMENT, \App\Models\Client::STATUS_PENANGANAN])->count())
        <div class="col-md-6">
            <div class="card border-light shadow-sm p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-25"
                         style="width: 56px; height: 56px;">
                        <i class="bi bi-journal-text text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Sedang didampingi</div>
                        <div style="font-size: 1.75rem; font-weight: 700;">{{ $activeCount }}</div>
                    </div>
                </div>
                @if ($activeCount > 0)
                    <a href="{{ route('clients.index') }}" class="btn-custom btn-custom-primary w-100 mt-3">
                        Lihat daftar penerima layanan
                    </a>
                @endif
            </div>
        </div>
    @endhasanyrole

   @hasanyrole('admin|super-admin')
       @include('partials.sop-dashboard')
   @endhasanyrole
</div>
@endsection