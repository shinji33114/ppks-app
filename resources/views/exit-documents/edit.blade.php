@extends('layouts.spark')

@section('page-title', 'Perbaiki surat')
@section('page-subtitle', $document->client->nama_lengkap)

@section('content')
    <div class="card p-4">
        <div class="alert alert-warning">
            Surat ini perlu diperbaiki. Perbarui data di bawah, lalu simpan kembali suratnya.
            <br>Catatan sebelumnya: {{ $document->catatan_penolakan }}
        </div>
        <form method="POST" action="{{ route('exit-documents.resubmit', $document) }}">
            @csrf @method('PATCH')
            <div class="mb-3">
                <label class="form-label-custom">Jenis surat</label>
                <select name="type" class="form-control-custom" required>
                    <option value="surat_keluar" @selected($document->type === 'surat_keluar')>Surat keluar atau pemulangan</option>
                    <option value="rujukan" @selected($document->type === 'rujukan')>Surat rujukan</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-custom">Nomor surat (jika ada)</label>
                <input name="nomor_surat" value="{{ old('nomor_surat', $document->nomor_surat) }}" class="form-control-custom">
            </div>
            <div class="mb-3">
                <label class="form-label-custom">Tujuan surat</label>
                <input name="tujuan" value="{{ old('tujuan', $document->tujuan) }}" class="form-control-custom" required>
            </div>
            <div class="mb-3">
                <label class="form-label-custom">Keterangan</label>
                <textarea name="keterangan" class="form-control-custom">{{ old('keterangan', $document->keterangan) }}</textarea>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label-custom">Tanggal surat</label>
                    <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', $document->tanggal_surat->format('Y-m-d')) }}" class="form-control-custom" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label-custom">Tanggal layanan berakhir</label>
                    <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar', $document->tanggal_keluar->format('Y-m-d')) }}" min="{{ $document->client->tanggal_masuk->format('Y-m-d') }}" class="form-control-custom" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label-custom">Nomor HP penanggung jawab</label>
                <input name="no_hp_penanggung_jawab" value="{{ old('no_hp_penanggung_jawab', $document->no_hp_penanggung_jawab) }}" class="form-control-custom" required>
            </div>
            <a href="{{ route('exit-documents.mine') }}" class="btn btn-light">Batal</a>
            <button class="btn-custom btn-custom-primary">Simpan surat</button>
        </form>
    </div>
@endsection
