@extends('layouts.spark')

@section('page-title', 'Assessment')
@section('page-subtitle', 'Daftar klien yang menunggu assessment')

@section('content')
<div class="table-card-custom">
    <div class="table-header-control">
        <h5 class="card-title mb-0">Klien Menunggu Assessment</h5>
    </div>

    <div class="table-responsive">
        <table class="table-custom">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Klien</th>
                    <th>Tanggal Masuk</th>
                    <th>Sumber Aduan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ $loop->iteration + ($clients->currentPage() - 1) * $clients->perPage() }}</td>
                        <td>{{ $client->nama_lengkap }}</td>
                        <td>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->format('d M Y') }}</td>
                        <td>{{ $client->sumber_aduan }}</td>
                        <td>
                            <a href="{{ route('assessment.create', $client->id) }}"
                               class="btn-custom btn-custom-primary">
                                <i class="bi bi-clipboard2-pulse me-1"></i> Mulai Assessment
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">Belum ada klien yang menunggu assessment.</td>
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
