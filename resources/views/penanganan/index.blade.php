@extends('layouts.spark')

@section('page-title', 'Penanganan Klien')
@section('page-subtitle', $client->nama_lengkap)

@section('content')
<div class="row g-4">

    <div class="col-lg-7">
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-4"><i class="bi bi-journal-plus me-2"></i>Tambah Catatan Tindakan</h5>

            <form method="POST" action="{{ route('penanganan.store', $client->id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">Tanggal Tindakan</label>
                        <input type="date" name="tanggal_tindakan" value="{{ old('tanggal_tindakan', now()->format('Y-m-d')) }}"
                               class="form-control-custom @error('tanggal_tindakan') is-invalid-custom @enderror">
                        @error('tanggal_tindakan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label-custom">Catatan / Bimbingan</label>
                        <textarea name="catatan_tindakan" rows="1"
                                  class="form-control-custom @error('catatan_tindakan') is-invalid-custom @enderror"
                                  placeholder="Contoh: Bimbingan konseling individu, klien mulai kooperatif">{{ old('catatan_tindakan') }}</textarea>
                        @error('catatan_tindakan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                <button type="submit" class="btn-custom btn-custom-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Catatan
                </button>
            </form>
        </div>

        <div class="card border-light shadow-sm p-4">
            <h5 class="card-title mb-4"><i class="bi bi-clock-history me-2"></i>Riwayat Tindakan</h5>

            @forelse ($followups as $log)
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>{{ \Carbon\Carbon::parse($log->tanggal_tindakan)->format('d F Y') }}</strong>
                        <span class="text-muted small">oleh {{ $log->user->name ?? 'Tidak diketahui' }}</span>
                    </div>
                    <p class="mb-0 mt-1">{{ $log->catatan_tindakan }}</p>
                </div>
            @empty
                <p class="text-muted mb-0">Belum ada catatan tindakan.</p>
            @endforelse
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-light shadow-sm p-4" style="background-color: #fff5f5;">
            <h5 class="card-title mb-3"><i class="bi bi-file-earmark-text me-2"></i>Ajukan Surat Keluar/Rujukan</h5>
            <p class="text-muted small mb-4">
                Surat keluar atau surat rujukan harus disetujui Kepala RPS sebelum status klien berubah menjadi selesai atau dirujuk.
            </p>

            <form method="POST" action="{{ route('exit-documents.store', $client->id) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label-custom">Jenis Surat</label>
                    <select name="type" class="form-control-custom @error('type') is-invalid-custom @enderror">
                        <option value="">- Pilih -</option>
                        <option value="surat_keluar" @selected(old('type') == 'surat_keluar')>Surat Keluar / Pemulangan</option>
                        <option value="rujukan" @selected(old('type') == 'rujukan')>Surat Rujukan</option>
                    </select>
                    @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Nomor Surat</label>
                    <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}"
                           class="form-control-custom @error('nomor_surat') is-invalid-custom @enderror">
                    @error('nomor_surat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Tujuan / Penerima</label>
                    <input type="text" name="tujuan" value="{{ old('tujuan') }}"
                           placeholder="Misal: Kembali ke keluarga, Panti X, RS Jiwa Y"
                           class="form-control-custom @error('tujuan') is-invalid-custom @enderror">
                    @error('tujuan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Keterangan</label>
                    <textarea name="keterangan" rows="2" class="form-control-custom">{{ old('keterangan') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', now()->format('Y-m-d')) }}"
                           class="form-control-custom @error('tanggal_surat') is-invalid-custom @enderror">
                    @error('tanggal_surat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Tanggal Keluar</label>
                    <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar') }}"
                           min="{{ \Carbon\Carbon::parse($client->tanggal_masuk)->format('Y-m-d') }}"
                           class="form-control-custom @error('tanggal_keluar') is-invalid-custom @enderror">
                    @error('tanggal_keluar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="text-muted small mt-1">
                        Tidak boleh sebelum tanggal masuk: {{ \Carbon\Carbon::parse($client->tanggal_masuk)->format('d M Y') }}
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">No. HP Penanggung Jawab</label>
                    <input type="text" name="no_hp_penanggung_jawab" value="{{ old('no_hp_penanggung_jawab') }}"
                           placeholder="08xxxxxxxxxx"
                           class="form-control-custom @error('no_hp_penanggung_jawab') is-invalid-custom @enderror">
                    @error('no_hp_penanggung_jawab') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-custom btn-custom-primary w-100"
                        onclick="return confirm('Ajukan surat ini untuk persetujuan Kepala RPS?')">
                    <i class="bi bi-send me-1"></i> Ajukan untuk Persetujuan
                </button>
            </form>
        </div>
    </div>

</div>

<div class="mt-4">
    <a href="{{ route('clients.show', $client->id) }}" class="btn-custom btn-custom-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Klien
    </a>
</div>
@endsection