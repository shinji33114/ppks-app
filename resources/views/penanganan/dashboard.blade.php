@extends('layouts.spark')

@section('page-title', 'Penanganan')
@section('page-subtitle', 'Daftar klien yang siap atau sedang ditangani')

@section('content')
<div class="table-card-custom">
    <div class="table-header-control">
        <h5 class="card-title mb-0">Daftar Penanganan Klien</h5>
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Klien</th>
                    <th>Tanggal Masuk</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                        <td>{{ $client->nama_lengkap }}</td>
                        <td>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->format('d M Y') }}</td>
                        <td>
                            <span class="badge-table pending">
                                {{ $client->status_label }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('penanganan.index', $client->id) }}"
                               class="btn-custom btn-custom-primary">
                                <i class="bi bi-journal-text me-1"></i> Kelola Penanganan
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Belum ada klien yang perlu ditangani.</td>
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
