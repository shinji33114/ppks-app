@extends('layouts.spark')

@section('page-title', 'Data Klien')
@section('page-subtitle', 'Semua klien yang tercatat dalam sistem')

@section('content')
<div class="table-card-custom">

    <div class="table-header-control">
        <h5 class="card-title mb-0">Daftar Klien</h5>

        @role('petugas-piket')
        <a href="{{ route('clients.create') }}" class="btn-custom btn-custom-primary">
            <i class="bi bi-plus-lg me-1"></i> Registrasi Klien Baru
        </a>
        @endrole
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Klien</th>
                    <th>Tanggal Masuk</th>
                    <th>Sumber Aduan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                        <td>
                            <div class="table-user-cell">
                                <div>
                                    <div class="table-user-name">{{ $client->nama_lengkap }}</div>
                                    @if ($client->nama_alias)
                                        <div class="table-user-sub">alias {{ $client->nama_alias }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}</td>
                        <td>{{ $client->sumber_aduan }}</td>
                        <td>
                            <span class="badge-table {{ $client->status_badge_class }}">{{ $client->status_label }}</span>
                        </td>
                        <td>
                            <div class="table-action-group">
                                <a href="{{ route('clients.show', $client->id) }}" class="table-btn-action" title="Lihat detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">Belum ada data klien.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3 px-3 pb-3">
        {{ $clients->links() }}
    </div>

</div>
@endsection