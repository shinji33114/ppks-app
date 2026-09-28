@extends('layouts.spark')

@section('page-title', 'Persetujuan Surat')
@section('page-subtitle', 'Surat keluar dan rujukan yang menunggu persetujuan')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="table-card-custom">
        <div class="table-header-control"><h5 class="card-title mb-0">Menunggu Persetujuan</h5></div>
        <div class="table-responsive">
            <table class="table-custom">
                <thead><tr><th>Klien</th><th>Jenis</th><th>Tujuan</th><th>Diajukan oleh</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($documents as $document)
                        <tr>
                            <td><a href="{{ route('exit-documents.show', $document) }}">{{ $document->client->nama_lengkap }}</a></td>
                            <td>{{ $document->type === 'rujukan' ? 'Surat Rujukan' : 'Surat Keluar' }}</td>
                            <td>{{ $document->tujuan }}</td>
                            <td>{{ $document->creator->name }}</td>
                            <td>
                                <a href="{{ route('exit-documents.show', $document) }}" class="btn btn-sm btn-outline-primary">Periksa</a>
                                <form method="POST" action="{{ route('exit-documents.approve', $document) }}" class="d-inline">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-success" onclick="return confirm('Setujui surat ini?')">Setujui</button>
                                </form>
                                <form method="POST" action="{{ route('exit-documents.reject', $document) }}" class="d-inline mt-1">
                                    @csrf @method('PATCH')
                                    <input type="text" name="catatan_penolakan" required placeholder="Alasan penolakan" class="form-control form-control-sm d-inline-block" style="max-width: 180px;">
                                    <button class="btn btn-sm btn-danger">Tolak</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4">Tidak ada surat yang menunggu persetujuan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3 px-3 pb-3">{{ $documents->links() }}</div>
    </div>
@endsection
