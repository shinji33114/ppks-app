@extends('layouts.spark')

@section('page-title', 'Revisi Data Arsip')
@section('page-subtitle', $client->nama_lengkap)

@section('content')

<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>Data ini sudah diarsipkan. Setiap perubahan yang Anda simpan di sini akan tercatat permanen di log revisi, termasuk nama Anda dan waktunya.</div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-4">Form Revisi</h5>

            <form method="POST" action="{{ route('clients.revisi.update', $client->id) }}">
                @csrf
                @method('PUT')

                <h6 class="text-muted text-uppercase small mb-3">Data Tahap 1</h6>
                <div class="mb-3">
                    <label class="form-label-custom">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $client->nama_lengkap) }}" class="form-control-custom" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Nama Alias</label>
                    <input type="text" name="nama_alias" value="{{ old('nama_alias', $client->nama_alias) }}" class="form-control-custom">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Sumber Aduan</label>
                    <input type="text" name="sumber_aduan" value="{{ old('sumber_aduan', $client->sumber_aduan) }}" class="form-control-custom" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">TKP / Kronologi</label>
                    <textarea name="tkp_kronologi" rows="3" class="form-control-custom" required>{{ old('tkp_kronologi', $client->tkp_kronologi) }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Kategori PPKS</label>
                    <input type="text" name="problem_awal" value="{{ old('problem_awal', $client->problem_awal) }}" class="form-control-custom">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Kondisi Fisik</label>
                    <textarea name="kondisi_fisik" rows="2" class="form-control-custom">{{ old('kondisi_fisik', $client->kondisi_fisik) }}</textarea>
                </div>

                <h6 class="text-muted text-uppercase small mb-3 mt-4">Data Tahap 3 — Keluar</h6>
                <div class="mb-3">
                    <label class="form-label-custom">Rujukan / Tujuan</label>
                    <input type="text" name="rujukan" value="{{ old('rujukan', $client->rujukan) }}" class="form-control-custom">
                </div>
                <div class="mb-3">
                    <label class="form-label-custom">Tanggal Keluar</label>
                    <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar', optional($client->tanggal_keluar)->format('Y-m-d')) }}" class="form-control-custom">
                </div>
                <div class="mb-4">
                    <label class="form-label-custom">No. HP Penanggung Jawab</label>
                    <input type="text" name="no_hp_penanggung_jawab" value="{{ old('no_hp_penanggung_jawab', $client->no_hp_penanggung_jawab) }}" class="form-control-custom">
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">Alasan Revisi <span class="text-danger">*</span></label>
                    <textarea name="alasan" rows="2" class="form-control-custom @error('alasan') is-invalid-custom @enderror"
                              placeholder="Wajib diisi, contoh: koreksi ejaan nama sesuai KTP yang baru diterima" required>{{ old('alasan') }}</textarea>
                    @error('alasan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-custom btn-custom-primary">
                    <i class="bi bi-save me-1"></i> Simpan Revisi
                </button>
                <a href="{{ route('clients.show', $client->id) }}" class="btn-custom btn-custom-secondary ms-2">Batal</a>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-3"><i class="bi bi-clock-history me-2"></i>Riwayat Revisi</h5>

            @forelse ($client->revisions as $rev)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $rev->user->name ?? 'Pengguna terhapus' }}</strong>
                        <span class="text-muted small">{{ $rev->created_at->format('d M Y, H:i') }}</span>
                    </div>
                    <ul class="mb-1 mt-1 ps-3 small">
                        @foreach ($rev->changes as $field => $change)
                            <li>
                                <strong>{{ $change['label'] }}</strong>:
                                "{{ $change['from'] ?: '(kosong)' }}" &rarr; "{{ $change['to'] ?: '(kosong)' }}"
                            </li>
                        @endforeach
                    </ul>
                    <div class="text-muted small fst-italic">Alasan: {{ $rev->alasan }}</div>
                </div>
            @empty
                <p class="text-muted mb-0">Belum ada revisi untuk data ini.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection