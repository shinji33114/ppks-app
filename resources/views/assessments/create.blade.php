@extends('layouts.spark')

@section('page-title', $assessment ? 'Edit Assessment Psikososial' : 'Assessment Psikososial')
@section('page-subtitle', 'Tahap 2 — ' . $client->nama_lengkap)

@section('content')
<form method="POST" action="{{ $assessment ? route('assessment.update', $client->id) : route('assessment.store', $client->id) }}">
    @csrf
    @if ($assessment)
        @method('PUT')
    @endif

    <!-- A. Identitas Lanjutan -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-person-vcard me-2"></i>A. Identitas Lanjutan</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-custom">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $client->nama_lengkap) }}" class="form-control-custom">
                <div class="text-muted small mt-1">Perbarui jika identitas sudah ditemukan (mis. lewat DUKCAPIL/Inafis).</div>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">NIK</label>
                <input type="text" name="nik" value="{{ old('nik', $assessment?->nik) }}" maxlength="16" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Tempat Lahir</label>
                <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $assessment?->tempat_lahir) }}" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $assessment?->tanggal_lahir) }}" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Umur (tahun)</label>
                <input type="number" name="umur" id="umur" min="0" max="150" value="{{ old('umur', $assessment?->umur) }}"
                       class="form-control-custom" placeholder="Perkiraan jika tanggal lahir tidak diketahui">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="L" @selected(old('jenis_kelamin', $assessment?->jenis_kelamin) == 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $assessment?->jenis_kelamin) == 'P')>Perempuan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Status Perkawinan</label>
                <input type="text" name="status_perkawinan" value="{{ old('status_perkawinan', $assessment?->status_perkawinan) }}" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Pendidikan Terakhir</label>
                <input type="text" name="pendidikan_terakhir" value="{{ old('pendidikan_terakhir', $assessment?->pendidikan_terakhir) }}" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Alamat (sesuai KTP)</label>
                <textarea name="alamat_domisili" rows="2" class="form-control-custom">{{ old('alamat_domisili', $assessment?->alamat_domisili) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Detail Alamat Pengakuan</label>
                <textarea name="detail_alamat_pengakuan" rows="2" class="form-control-custom"
                          placeholder="Alamat sesuai pengakuan klien (RT/RW, patokan, dsb.)">{{ old('detail_alamat_pengakuan', $assessment?->detail_alamat_pengakuan) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Status Pendidikan</label>
                <select name="status_pendidikan" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    @foreach (['Sedang Bersekolah', 'Putus Sekolah', 'Tidak Pernah Sekolah', 'Tamat / Tidak Bersekolah Lagi'] as $opt)
                        <option value="{{ $opt }}" @selected(old('status_pendidikan', $assessment?->status_pendidikan) == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kategori Umur</label>
                <select name="kategori_umur" class="form-control-custom @error('kategori_umur') is-invalid-custom @enderror">
                    <option value="">- Pilih -</option>
                    @foreach (\App\Models\ClientAssessment::KATEGORI_UMUR as $value => $label)
                        <option value="{{ $value }}" @selected(old('kategori_umur', $assessment?->kategori_umur) == $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- B. Kondisi Ekonomi -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-cash-coin me-2"></i>B. Kondisi Ekonomi</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-custom">Bagaimana Kondisi Ekonomi 2 Bulan Terakhir?</label>
                <textarea name="kondisi_ekonomi" rows="2" class="form-control-custom">{{ old('kondisi_ekonomi', $assessment?->kondisi_ekonomi) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Pekerjaan (klien/wali)</label>
                <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $assessment?->pekerjaan) }}" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Sumber Penghasilan</label>
                <input type="text" name="sumber_penghasilan" value="{{ old('sumber_penghasilan', $assessment?->sumber_penghasilan) }}" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kepemilikan Kartu Bantuan Sosial</label>
                <input type="text" name="kepemilikan_bansos" value="{{ old('kepemilikan_bansos', $assessment?->kepemilikan_bansos) }}"
                       placeholder="Misal: PKH, KIS, tidak ada" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kondisi Tempat Tinggal</label>
                <input type="text" name="kondisi_tempat_tinggal" value="{{ old('kondisi_tempat_tinggal', $assessment?->kondisi_tempat_tinggal) }}" class="form-control-custom">
            </div>
        </div>
    </div>

    <!-- C. Kondisi Psikososial -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-people me-2"></i>C. Kondisi Psikososial</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-custom">Latar Belakang Masalah / Riwayat Kasus</label>
                <textarea name="riwayat_masalah" rows="3" class="form-control-custom @error('riwayat_masalah') is-invalid-custom @enderror">{{ old('riwayat_masalah', $assessment?->riwayat_masalah) }}</textarea>
                @error('riwayat_masalah') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Relasi dengan Keluarga</label>
                <select name="relasi_keluarga" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="baik" @selected(old('relasi_keluarga', $assessment?->relasi_keluarga) == 'baik')>Baik</option>
                    <option value="kurang_baik" @selected(old('relasi_keluarga', $assessment?->relasi_keluarga) == 'kurang_baik')>Kurang Baik</option>
                    <option value="terputus" @selected(old('relasi_keluarga', $assessment?->relasi_keluarga) == 'terputus')>Terputus</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label-custom">Dukungan Sosial yang Tersedia</label>
                <input type="text" name="dukungan_sosial" value="{{ old('dukungan_sosial', $assessment?->dukungan_sosial) }}" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Riwayat Kekerasan / Trauma (jika ada)</label>
                <textarea name="riwayat_trauma" rows="2" class="form-control-custom">{{ old('riwayat_trauma', $assessment?->riwayat_trauma) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Permasalahan pada Lingkup Keluarga (2 Bulan Terakhir)</label>
                <textarea name="masalah_keluarga" rows="2" class="form-control-custom">{{ old('masalah_keluarga', $assessment?->masalah_keluarga) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Permasalahan pada Lingkup Sekolah (2 Bulan Terakhir)</label>
                <textarea name="masalah_sekolah" rows="2" class="form-control-custom">{{ old('masalah_sekolah', $assessment?->masalah_sekolah) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Permasalahan pada Lingkup Sosial Masyarakat (2 Bulan Terakhir)</label>
                <textarea name="masalah_sosial" rows="2" class="form-control-custom">{{ old('masalah_sosial', $assessment?->masalah_sosial) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Cara Pandang Klien terhadap Masalah yang Sedang Dihadapi</label>
                <textarea name="cara_pandang_klien" rows="2" class="form-control-custom">{{ old('cara_pandang_klien', $assessment?->cara_pandang_klien) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Rencana Kedepan</label>
                <textarea name="rencana_kedepan" rows="2" class="form-control-custom">{{ old('rencana_kedepan', $assessment?->rencana_kedepan) }}</textarea>
            </div>
        </div>
    </div>

    <!-- D. Observasi Kondisi Mental -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-emoji-neutral me-2"></i>D. Observasi Kondisi Mental</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-custom">Alam Perasaan (Kondisi Emosi Saat Assessment)</label>
                <input type="text" name="kondisi_emosi" value="{{ old('kondisi_emosi', $assessment?->kondisi_emosi) }}"
                       placeholder="Misal: Tenang, Cemas, Mudah tersinggung" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Orientasi Realita</label>
                <select name="orientasi_realita" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="baik" @selected(old('orientasi_realita', $assessment?->orientasi_realita) == 'baik')>Baik</option>
                    <option value="terganggu" @selected(old('orientasi_realita', $assessment?->orientasi_realita) == 'terganggu')>Terganggu</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Cara Komunikasi</label>
                <input type="text" name="cara_komunikasi" value="{{ old('cara_komunikasi', $assessment?->cara_komunikasi) }}"
                       placeholder="Misal: Kooperatif, Pasif, Menghindari kontak mata" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Arus Percakapan</label>
                <input type="text" name="arus_percakapan" value="{{ old('arus_percakapan', $assessment?->arus_percakapan) }}"
                       placeholder="Misal: Lancar, Terhambat, Berpindah-pindah topik" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Ingatan</label>
                <input type="text" name="ingatan" value="{{ old('ingatan', $assessment?->ingatan) }}"
                       placeholder="Misal: Baik, Lupa kejadian tertentu" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Arah Perilaku</label>
                <input type="text" name="arah_perilaku" value="{{ old('arah_perilaku', $assessment?->arah_perilaku) }}"
                       placeholder="Misal: Terarah, Gelisah, Agresif" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Catatan Observasi Tambahan</label>
                <textarea name="catatan_observasi" rows="2" class="form-control-custom">{{ old('catatan_observasi', $assessment?->catatan_observasi) }}</textarea>
            </div>
        </div>
    </div>

    <!-- E. Simpulan & Rekomendasi -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-clipboard2-check me-2"></i>E. Simpulan & Rekomendasi</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-custom">Simpulan Awal <span class="text-danger">*</span></label>
                <textarea name="simpulan_awal" rows="3" class="form-control-custom @error('simpulan_awal') is-invalid-custom @enderror" required>{{ old('simpulan_awal', $assessment?->simpulan_awal) }}</textarea>
                @error('simpulan_awal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label-custom">Rekomendasi Penanganan</label>
                <textarea name="rekomendasi_penanganan" rows="3" class="form-control-custom @error('rekomendasi_penanganan') is-invalid-custom @enderror">{{ old('rekomendasi_penanganan', $assessment?->rekomendasi_penanganan) }}</textarea>
                @error('rekomendasi_penanganan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <button type="submit" class="btn-custom btn-custom-primary">
        <i class="bi bi-save me-1"></i> {{ $assessment ? 'Perbarui Assessment' : 'Simpan Assessment' }}
    </button>
    <a href="{{ route('clients.show', $client->id) }}" class="btn-custom btn-custom-secondary ms-2">Batal</a>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const umur = document.getElementById('umur');
    const kategori = document.querySelector('select[name="kategori_umur"]');
    if (!umur || !kategori) return;

    umur.addEventListener('input', function () {
        if (kategori.value !== '' || umur.value === '') return;
        const n = parseInt(umur.value, 10);
        if (isNaN(n)) return;
        kategori.value = n <= 5 ? 'balita' : n <= 12 ? 'anak' : n <= 17 ? 'remaja' : n <= 59 ? 'dewasa' : 'lansia';
    });
});
</script>
@endpush