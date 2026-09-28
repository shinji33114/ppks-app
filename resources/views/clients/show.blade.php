@extends('layouts.spark')

@section('page-title', 'Detail Klien')
@section('page-subtitle', $client->nama_lengkap)

@section('content')
<div class="row g-4">

    <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4 text-center">
            @if ($client->foto_klien)
                <img src="{{ asset('storage/' . $client->foto_klien) }}" alt="Foto Klien"
                     class="rounded-3 mb-3" style="width: 100%; max-height: 280px; object-fit: cover;">
            @else
                <div class="d-flex align-items-center justify-content-center bg-light rounded-3 mb-3"
                     style="height: 200px;">
                    <i class="bi bi-person-fill" style="font-size: 3rem; color: #ccc;"></i>
                </div>
            @endif

            <h5 class="mb-1">{{ $client->nama_lengkap }}</h5>

            <span class="badge-table {{ $client->status_badge_class }} d-inline-block mb-3">{{ $client->status_label }}</span>

            @role('peksos')
                @if ($client->status === 'draft')
                    <a href="{{ route('assessment.create', $client->id) }}" class="btn-custom btn-custom-primary w-100">
                        <i class="bi bi-arrow-right-circle me-1"></i> Lanjutkan ke Assessment
                    </a>
                @endif
            @endrole

            @role('petugas-piket')
                @if ($client->status === 'draft' && $client->locked_at === null && $client->created_at->addDay()->isFuture())
                    <a href="{{ route('clients.edit', $client) }}" class="btn-custom btn-custom-secondary w-100 mt-2">
                        <i class="bi bi-pencil me-1"></i> Edit Data Tahap 1
                    </a>
                @endif
            @endrole

            @role('admin-rps')
    @if (in_array($client->status, ['assessment', 'penanganan']))
        <a href="{{ route('penanganan.index', $client->id) }}" class="btn-custom btn-custom-primary w-100">
            <i class="bi bi-clipboard2-pulse me-1"></i> Kelola Penanganan
        </a>
    @endif
@endrole
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-4">Data Registrasi Awal (Tahap 1)</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Tanggal Masuk</div>
                    <div class="fw-medium">{{ \Carbon\Carbon::parse($client->tanggal_masuk)->format('d F Y') }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Sumber Aduan</div>
                    <div class="fw-medium">{{ $client->sumber_aduan }}</div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">TKP / Kronologi</div>
                    <div class="fw-medium">{{ $client->tkp_kronologi }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Dicatat pada</div>
                    <div class="fw-medium">{{ $client->created_at->format('d M Y, H:i') }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Terakhir diperbarui</div>
                    <div class="fw-medium">{{ $client->updated_at->diffForHumans() }}</div>
                </div>
            </div>
        </div>

        @if ($client->status === 'draft')
            <div class="card border-light shadow-sm p-4 mt-4" style="background-color: #fffbea;">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-info-circle-fill text-warning mt-1"></i>
                    <div>
                        <strong>Menunggu Assessment</strong>
                        <p class="mb-0 text-muted small">
                            Data ini masih bisa diedit oleh Petugas Piket sampai Peksos mulai mengisi Tahap 2.
                            Setelah itu, data Tahap 1 akan otomatis terkunci.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>

<div class="mt-4">
    <a href="{{ route('clients.index') }}" class="btn-custom btn-custom-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Klien
    </a>
</div>
@endsection