@extends('layouts.spark')

@section('page-title', 'Registrasi Klien Baru')
@section('page-subtitle', 'Tahap 1 — Registrasi Awal')

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-light shadow-sm p-4">

            <h5 class="card-title mb-3">Form Registrasi Awal (Tahap 1)</h5>

            <div class="alert alert-info small mb-4">
                Semua kolom wajib diisi. Jika data memang tidak ada, isi dengan tanda <strong>-</strong>.
            </div>

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
                           placeholder="Isi - jika belum diketahui (akan tercatat 'Tanpa Nama / X')"
                           required>
                </div>

                <div class="mb-3">
    <label class="form-label-custom">Kondisi Fisik & Penampilan</label>
    <textarea name="kondisi_fisik" rows="2" class="form-control-custom" required
              placeholder="Contoh: Tampak kurus, ada luka lecet di lengan. Isi - jika tidak ada">{{ old('kondisi_fisik') }}</textarea>
</div>

<div class="mb-3">
    <label class="form-label-custom">Nama Alias</label>
    <input type="text" name="nama_alias" value="{{ old('nama_alias') }}" class="form-control-custom" required
           placeholder="Nama panggilan lain. Isi - jika tidak ada">
</div>

<div class="mb-3">
    <label class="form-label-custom">Data Dukung</label>
    <input type="file" name="data_dukung" id="data_dukung" class="form-control-custom" accept=".pdf,.jpg,.jpeg,.png">
    <div class="text-muted small mt-1">KTP, KK, surat pengantar, atau dokumen identitas lain (PDF/gambar, maks 10MB)</div>
    <div class="form-check mt-2">
        <input class="form-check-input" type="checkbox" name="tanpa_data_dukung" id="tanpa_data_dukung" value="1"
               @checked(old('tanpa_data_dukung'))>
        <label class="form-check-label small" for="tanpa_data_dukung">Tidak ada data dukung (-)</label>
    </div>
</div>
                
               <div class="mb-3">
    <label for="kategori_ppks" class="form-label-custom">Kategori PPKS</label>
    <select name="kategori_ppks" id="kategori_ppks" class="form-control-custom" required>
        <option value="">- Pilih -</option>
        <option value="-" @selected(old('kategori_ppks') === '-')>- Tidak diketahui -</option>
        @foreach (\App\Models\Client::KATEGORI_PPKS as $kategori)
            <option value="{{ $kategori }}" @selected(old('kategori_ppks') == $kategori)>{{ $kategori }}</option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="problem_awal" class="form-label-custom">Problem Awal / Bentuk Pelanggaran</label>
    <textarea name="problem_awal" id="problem_awal" rows="2" class="form-control-custom" required
              placeholder="Uraikan singkat problem awal atau bentuk pelanggaran yang dialami klien. Isi - jika tidak ada">{{ old('problem_awal') }}</textarea>
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
                <li>Semua kolom wajib diisi. Jika datanya tidak ada, isi dengan tanda <strong>-</strong>.</li>
                <li>Nama diisi <strong>-</strong> jika identitas klien belum diketahui (akan tercatat "Tanpa Nama / X").</li>
                <li>Foto klien wajib diunggah. Data dukung wajib diunggah, atau centang "Tidak ada data dukung".</li>
                <li>Setelah disimpan, Admin akan mendapat notifikasi untuk melanjutkan ke tahap Assessment.</li>
                <li>Data ini akan terkunci begitu Admin mulai mengisi Tahap 2.</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cb = document.getElementById('tanpa_data_dukung');
    const file = document.getElementById('data_dukung');
    if (!cb || !file) return;
    const sync = () => {
        file.disabled = cb.checked;
        file.required = !cb.checked;
        if (cb.checked) file.value = '';
    };
    cb.addEventListener('change', sync);
    sync();
});
</script>
@endpush
