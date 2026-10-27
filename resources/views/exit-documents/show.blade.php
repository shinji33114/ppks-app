@extends('layouts.spark')

@section('page-title', 'Detail surat')
@section('page-subtitle', $document->client->nama_lengkap)

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card p-4">
                <h5 class="card-title mb-3">Data penerima layanan</h5>
                @if ($document->client->foto_klien)
                    <img src="{{ route('clients.photo', $document->client) }}" alt="Foto Penerima Layanan"
                         class="rounded-3 mb-3 w-100" style="max-height: 240px; object-fit: cover;">
                @endif
                <dl class="row mb-0">
                    <dt class="col-sm-5">Nama</dt><dd class="col-sm-7">{{ $document->client->nama_lengkap }}</dd>
                    <dt class="col-sm-5">Status</dt><dd class="col-sm-7">{{ $document->client->status_label }}</dd>
                    <dt class="col-sm-5">Tanggal masuk</dt><dd class="col-sm-7">{{ $document->client->tanggal_masuk->translatedFormat('d F Y') }}</dd>
                    <dt class="col-sm-5">Lokasi dan cerita kejadian</dt><dd class="col-sm-7">{{ $document->client->tkp_kronologi }}</dd>
                </dl>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card p-4">
                <h5 class="card-title mb-3">Rincian surat</h5>
                @if (in_array($document->status, ['selesai', 'disetujui'], true) && $document->pdf_path)
                    <a href="{{ route('exit-documents.pdf', ['document' => $document->id, 'v' => $document->updated_at->timestamp]) }}" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-file-earmark-pdf me-1"></i> Unduh surat
                    </a>
                @endif
                <dl class="row">
                    <dt class="col-sm-4">Jenis surat</dt><dd class="col-sm-8">{{ $document->type === 'rujukan' ? 'Surat rujukan' : 'Surat keluar atau pemulangan' }}</dd>
                    <dt class="col-sm-4">Nomor surat</dt><dd class="col-sm-8">{{ $document->nomor_surat ?: '-' }}</dd>
                    <dt class="col-sm-4">Tanggal surat</dt><dd class="col-sm-8">{{ $document->tanggal_surat->translatedFormat('d F Y') }}</dd>
                    <dt class="col-sm-4">Tujuan</dt><dd class="col-sm-8">{{ $document->tujuan }}</dd>
                    <dt class="col-sm-4">Tanggal layanan berakhir</dt><dd class="col-sm-8">{{ $document->tanggal_keluar->translatedFormat('d F Y') }}</dd>
                    <dt class="col-sm-4">Nomor HP penanggung jawab</dt><dd class="col-sm-8">{{ $document->no_hp_penanggung_jawab }}</dd>
                    <dt class="col-sm-4">Keterangan tambahan</dt><dd class="col-sm-8">{{ $document->keterangan ?: '-' }}</dd>
                    <dt class="col-sm-4">Diterbitkan oleh</dt><dd class="col-sm-8">{{ $document->creator->name }}</dd>
                </dl>

                @if ($document->status === 'ditolak')
                    <div class="alert alert-warning mb-0">
                        Surat ini perlu diperbaiki. Ubah data surat melalui menu Surat Keluar dan Rujukan.
                    </div>
                @else
                    <div class="alert alert-success">Surat sudah dibuat dan tidak dapat diubah.</div>
                    <a href="{{ route('exit-documents.print', $document) }}" target="_blank"
                       class="btn btn-primary">
                        <i class="bi bi-printer me-1"></i> Lihat dan cetak surat
                    </a>
                @endif
            </div>
        </div>
    </div>
@endsection
