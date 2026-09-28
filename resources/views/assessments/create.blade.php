@extends('layouts.spark')

@section('page-title', 'Assessment Psikososial')
@section('page-subtitle', 'Tahap 2 — ' . $client->nama_lengkap)

@section('content')
<form method="POST" action="{{ route('assessment.store', $client->id) }}">
    @csrf

    <!-- A. Identitas Lanjutan -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-person-vcard me-2"></i>A. Identitas Lanjutan</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-custom">NIK</label>
                <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" class="form-control-custom">
            </div>
            <div class="col-md-3">
                <label class="form-label-custom">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir') }}" class="form-control-custom">
            </div>
            <div class="col-md-3">
                <label class="form-label-custom">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="L" @selected(old('jenis_kelamin') == 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin') == 'P')>Perempuan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Status Perkawinan</label>
                <input type="text" name="status_perkawinan" value="{{ old('status_perkawinan') }}" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Pendidikan Terakhir</label>
                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir') }}" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Alamat Domisili</label>
                <textarea name="alamat_domisili" rows="2" class="form-control-custom">{{ old('alamat_domisili') }}</textarea>
            </div>
        </div>
    </div>

    <!-- B. Kondisi Ekonomi -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-cash-coin me-2"></i>B. Kondisi Ekonomi</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-custom">Pekerjaan (klien/wali)</label>
                <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Sumber Penghasilan</label>
                <input type="text" name="sumber_penghasilan" value="{{ old('sumber_penghasilan') }}" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kepemilikan Kartu Bantuan Sosial</label>
                <input type="text" name="kepemilikan_bansos" value="{{ old('kepemilikan_bansos') }}"
                       placeholder="Misal: PKH, KIS, tidak ada" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kondisi Tempat Tinggal</label>
                <input type="text" name="kondisi_tempat_tinggal" value="{{ old('kondisi_tempat_tinggal') }}" class="form-control-custom">
            </div>
        </div>
    </div>

    <!-- C. Kondisi Psikososial -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-people me-2"></i>C. Kondisi Psikososial</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-custom">Latar Belakang Masalah / Riwayat Kasus <span class="text-danger">*</span></label>
                <textarea name="riwayat_masalah" rows="3" class="form-control-custom @error('riwayat_masalah') is-invalid-custom @enderror" required>{{ old('riwayat_masalah') }}</textarea>
                @error('riwayat_masalah') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Relasi dengan Keluarga</label>
                <select name="relasi_keluarga" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="baik" @selected(old('relasi_keluarga') == 'baik')>Baik</option>
                    <option value="kurang_baik" @selected(old('relasi_keluarga') == 'kurang_baik')>Kurang Baik</option>
                    <option value="terputus" @selected(old('relasi_keluarga') == 'terputus')>Terputus</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label-custom">Dukungan Sosial yang Tersedia</label>
                <input type="text" name="dukungan_sosial" value="{{ old('dukungan_sosial') }}" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Riwayat Kekerasan / Trauma (jika ada)</label>
                <textarea name="riwayat_trauma" rows="2" class="form-control-custom">{{ old('riwayat_trauma') }}</textarea>
            </div>
        </div>
    </div>

    <!-- D. Observasi Kondisi Mental -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-emoji-neutral me-2"></i>D. Observasi Kondisi Mental</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-custom">Kondisi Emosi Saat Assessment</label>
                <input type="text" name="kondisi_emosi" value="{{ old('kondisi_emosi') }}"
                       placeholder="Misal: Tenang, Cemas, Mudah tersinggung" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Orientasi Realita</label>
                <select name="orientasi_realita" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="baik" @selected(old('orientasi_realita') == 'baik')>Baik</option>
                    <option value="terganggu" @selected(old('orientasi_realita') == 'terganggu')>Terganggu</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Catatan Observasi Tambahan</label>
                <textarea name="catatan_observasi" rows="2" class="form-control-custom">{{ old('catatan_observasi') }}</textarea>
            </div>
        </div>
    </div>

    <!-- E. Simpulan & Rekomendasi -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-clipboard2-check me-2"></i>E. Simpulan & Rekomendasi</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-custom">Simpulan Awal <span class="text-danger">*</span></label>
                <textarea name="simpulan_awal" rows="3" class="form-control-custom @error('simpulan_awal') is-invalid-custom @enderror" required>{{ old('simpulan_awal') }}</textarea>
                @error('simpulan_awal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label-custom">Rekomendasi Penanganan <span class="text-danger">*</span></label>
                <textarea name="rekomendasi_penanganan" rows="3" class="form-control-custom @error('rekomendasi_penanganan') is-invalid-custom @enderror" required>{{ old('rekomendasi_penanganan') }}</textarea>
                @error('rekomendasi_penanganan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <button type="submit" class="btn-custom btn-custom-primary">
        <i class="bi bi-save me-1"></i> Simpan Assessment
    </button>
    <a href="{{ route('clients.show', $client->id) }}" class="btn-custom btn-custom-secondary ms-2">Batal</a>
</form>
@endsection