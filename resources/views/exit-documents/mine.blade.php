@extends('layouts.spark')

@section('page-title', 'Surat Saya')
@section('page-subtitle', 'Pantau pengajuan surat keluar dan rujukan')

@section('content')
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
    <div class="table-card-custom">
        <div class="table-header-control"><h5 class="card-title mb-0">Riwayat Pengajuan</h5></div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>Klien</th><th>Jenis</th><th>Tujuan</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td>{{ $document->client->nama_lengkap }}</td>
                            <td>{{ $document->type === 'rujukan' ? 'Surat Rujukan' : 'Surat Keluar' }}</td>
                            <td>{{ $document->tujuan }}</td>
                            <td>{{ str_replace('_', ' ', ucfirst($document->status)) }}</td>
                            <td>
                                @if ($document->status === 'ditolak')
                                    <a href="{{ route('exit-documents.edit', $document) }}" class="btn btn-sm btn-warning">Perbaiki</a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">Belum ada pengajuan surat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 px-3 pb-3">{{ $documents->links() }}</div>
    </div>
@endsection
