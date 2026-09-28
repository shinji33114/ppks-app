@extends('layouts.spark')

@section('page-title', 'Registrasi Klien Baru')
@section('page-subtitle', 'Tahap 1 — diisi oleh Petugas Piket')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-light shadow-sm p-4">

            <h5 class="card-title mb-4">Form Registrasi Awal (Tahap 1)</h5>

            <form method="POST" action="{{ route('clients.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="tanggal_masuk" class="form-label-custom">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" id="tanggal_masuk"
                           value="{{ old('tanggal_masuk') }}"
                           class="form-control-custom @error('tanggal_masuk') is-invalid-custom @enderror"
                           required>
                    @error('tanggal_masuk')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="sumber_aduan" class="form-label-custom">Sumber Aduan</label>
                    <input type="text" name="sumber_aduan" id="sumber_aduan"
                           value="{{ old('sumber_aduan') }}"
                           class="form-control-custom @error('sumber_aduan') is-invalid-custom @enderror"
                           placeholder="Misal: Laporan warga, Satpol PP, dsb."
                           required>
                    @error('sumber_aduan')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="tkp_kronologi" class="form-label-custom">Kronologi Singkat</label>
                    <textarea name="tkp_kronologi" id="tkp_kronologi" rows="4"
                              class="form-control-custom @error('tkp_kronologi') is-invalid-custom @enderror"
                              placeholder="Ceritakan singkat lokasi dan kronologi penemuan klien"
                              required>{{ old('tkp_kronologi') }}</textarea>
                    @error('tkp_kronologi')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label-custom">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" id="nama_lengkap"
                           value="{{ old('nama_lengkap') }}"
                           class="form-control-custom"
                           placeholder="Kosongkan jika belum diketahui (akan diisi 'Tanpa Nama / X')">
                </div>

                <div class="mb-3">
    <label class="form-label-custom">Kondisi Fisik & Penampilan (opsional)</label>
    <textarea name="kondisi_fisik" rows="2" class="form-control-custom"
              placeholder="Contoh: Tampak kurus, ada luka lecet di lengan">{{ old('kondisi_fisik') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label-custom">Nama Alias (opsional)</label>
    <input type="text" name="nama_alias" value="{{ old('nama_alias') }}" class="form-control-custom"
           placeholder="Nama panggilan lain, jika ada">
</div>

<div class="mb-3">
    <label class="form-label-custom">Data Dukung (opsional)</label>
    <input type="file" name="data_dukung" class="form-control-custom" accept=".pdf,.jpg,.jpeg,.png">
    <div class="text-muted small mt-1">KTP, KK, surat pengantar, atau dokumen identitas lain (PDF/gambar, maks 10MB)</div>
</div>
                
               <div class="mb-3">
    <label for="problem_awal" class="form-label-custom">Kategori PPKS (opsional)</label>
    <select name="problem_awal" id="problem_awal" class="form-control-custom">
        <option value="">- Belum diketahui / isi nanti -</option>
        <option value="Lansia Terlantar" @selected(old('problem_awal') == 'Lansia Terlantar')>Lansia Terlantar</option>
        <option value="Anak Jalanan" @selected(old('problem_awal') == 'Anak Jalanan')>Anak Jalanan</option>
        <option value="Anak Terlantar" @selected(old('problem_awal') == 'Anak Terlantar')>Anak Terlantar</option>
        <option value="Disabilitas Terlantar" @selected(old('problem_awal') == 'Disabilitas Terlantar')>Disabilitas Terlantar</option>
        <option value="Korban Kekerasan" @selected(old('problem_awal') == 'Korban Kekerasan')>Korban Kekerasan</option>
        <option value="Gelandangan / Pengemis" @selected(old('problem_awal') == 'Gelandangan / Pengemis')>Gelandangan / Pengemis</option>
        <option value="ODGJ Terlantar" @selected(old('problem_awal') == 'ODGJ Terlantar')>Orang Dengan Gangguan Jiwa (ODGJ) Terlantar</option>
        <option value="Lainnya" @selected(old('problem_awal') == 'Lainnya')>Lainnya</option>
    </select>
</div>

                <div class="mb-4">
                    <label for="foto_klien" class="form-label-custom">Foto Klien <span class="text-danger">*</span></label>
                    <input type="file" name="foto_klien" id="foto_klien" accept="image/*"
                           class="form-control-custom @error('foto_klien') is-invalid-custom @enderror" required>
                    @error('foto_klien')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn-custom btn-custom-primary">
                    <i class="bi bi-save me-1"></i> Simpan
                </button>
            </form>

        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4">
            <h6 class="card-title mb-3"><i class="bi bi-info-circle me-1"></i> Panduan Tahap 1</h6>
            <ul class="ps-3 mb-0" style="font-size: 0.875rem; line-height: 1.8;">
                <li>Field wajib: tanggal masuk, sumber aduan, kronologi, nama (atau Tanpa Nama / X), dan foto klien.</li>
                <li>Nama boleh dikosongkan jika identitas klien belum diketahui.</li>
                <li>Setelah disimpan, Peksos akan otomatis mendapat notifikasi untuk melanjutkan ke tahap Assessment.</li>
                <li>Data ini akan terkunci begitu Peksos mulai mengisi Tahap 2.</li>
            </ul>
        </div>
    </div>
</div>
@endsection