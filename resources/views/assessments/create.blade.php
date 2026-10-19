@extends('layouts.spark')

@section('page-title', $assessment ? 'Edit Assessment Psikososial' : 'Assessment Psikososial')
@section('page-subtitle', 'Tahap 2 — ' . $client->nama_lengkap)

@section('content')
@php
    // Field bertipe (tanggal/angka/NIK/pilihan) yang diisi "-" disimpan sebagai NULL. Karena itu pada
    // form edit, nilai NULL ditampilkan kembali sebagai "-" supaya form tetap valid tanpa diisi ulang.
    $strip = fn (string $f) => old($f) === '-' || (old($f) === null && $assessment && $assessment->{$f} === null);
@endphp

<div class="workflow-page assessment-workflow">
    <section class="workflow-hero workflow-hero-assessment mb-4">
        <div class="workflow-hero-copy">
            <span class="workflow-eyebrow"><i class="bi bi-clipboard2-pulse me-1"></i> Tahap 2 · Assessment psikososial</span>
            <h2>{{ $assessment ? 'Perbarui hasil assessment' : 'Assessment klien' }}</h2>
            <p>Catat informasi klien secara menyeluruh untuk membantu menyusun rekomendasi penanganan.</p>
        </div>
        <div class="assessment-client-chip">
            <span class="workflow-avatar workflow-avatar-assessment"><i class="bi bi-person-fill"></i></span>
            <span><small>Nama klien</small><strong>{{ $client->nama_lengkap }}</strong></span>
        </div>
    </section>

    <div class="assessment-guidance">
        <span class="assessment-guidance-icon"><i class="bi bi-info-circle-fill"></i></span>
        <div>
            <strong>Petunjuk pengisian</strong>
            <p>Semua kolom wajib diisi. Jika data tidak tersedia, isi dengan tanda <strong>-</strong> atau pilih “Tidak diketahui”.</p>
        </div>
    </div>

<form class="assessment-form" method="POST" action="{{ $assessment ? route('assessment.update', $client->id) : route('assessment.store', $client->id) }}">
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
                <input type="text" required name="nama_lengkap" value="{{ old('nama_lengkap', $client->nama_lengkap) }}"
                       placeholder="Nama lengkap sesuai identitas resmi" class="form-control-custom">
                <div class="text-muted small mt-1">Perbarui jika identitas sudah ditemukan (mis. lewat DUKCAPIL/Inafis).</div>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">NIK</label>
                <input type="text" inputmode="numeric" name="nik" required maxlength="16" pattern="[0-9]{16}|-"
                       title="16 digit angka, atau isi tanda - jika tidak ada"
                       value="{{ $strip('nik') ? '-' : old('nik', $assessment?->nik) }}"
                       placeholder="16 digit NIK, isi - jika tidak ada" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Tempat Lahir</label>
                <input type="text" required name="tempat_lahir" value="{{ old('tempat_lahir', $assessment?->tempat_lahir) }}"
                       placeholder="Kota/kabupaten tempat lahir" class="form-control-custom">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" required @disabled($strip('tanggal_lahir'))
                       value="{{ $strip('tanggal_lahir') ? '' : old('tanggal_lahir', $assessment?->tanggal_lahir?->format('Y-m-d')) }}" class="form-control-custom">
                <div class="form-check mt-1">
                    <input class="form-check-input" type="checkbox" name="tanggal_lahir" value="-" id="tanggal_lahir_strip" @checked($strip('tanggal_lahir'))>
                    <label class="form-check-label small" for="tanggal_lahir_strip">Tidak diketahui (-)</label>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Umur (tahun)</label>
                <input type="text" inputmode="numeric" name="umur" id="umur" required pattern="[0-9]{1,3}|-"
                       title="Angka 0-150, atau isi tanda - jika tidak diketahui"
                       value="{{ $strip('umur') ? '-' : old('umur', $assessment?->umur) }}"
                       class="form-control-custom" placeholder="Perkiraan; isi - jika tidak diketahui">
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Jenis Kelamin</label>
                <select required name="jenis_kelamin" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="-" @selected($strip('jenis_kelamin'))>- Tidak diketahui -</option>
                    <option value="L" @selected(old('jenis_kelamin', $assessment?->jenis_kelamin) == 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $assessment?->jenis_kelamin) == 'P')>Perempuan</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Status Perkawinan</label>
                <select required name="status_perkawinan" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    @foreach (\App\Models\ClientAssessment::STATUS_PERKAWINAN as $opt)
                        <option value="{{ $opt }}" @selected(old('status_perkawinan', $assessment?->status_perkawinan) === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Pendidikan Terakhir</label>
                @php
                    $pendidikanTerpilih = old('pendidikan_terakhir', $assessment?->pendidikan_terakhir);
                    // Data lama (dulu teks bebas) yang tidak ada di daftar tetap ditampilkan agar tidak hilang saat edit.
                    $pendidikanLama = filled($pendidikanTerpilih) && $pendidikanTerpilih !== '-'
                        && ! in_array($pendidikanTerpilih, \App\Models\ClientAssessment::PENDIDIKAN_TERAKHIR, true);
                @endphp
                <select required name="pendidikan_terakhir" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="-" @selected($strip('pendidikan_terakhir'))>- Tidak diketahui -</option>
                    @foreach (\App\Models\ClientAssessment::PENDIDIKAN_TERAKHIR as $opt)
                        <option value="{{ $opt }}" @selected($pendidikanTerpilih === $opt)>{{ $opt }}</option>
                    @endforeach
                    @if ($pendidikanLama)
                        <option value="{{ $pendidikanTerpilih }}" selected>{{ $pendidikanTerpilih }} (data lama)</option>
                    @endif
                </select>
                <div class="text-muted small mt-1">Jenjang tertinggi yang telah ditamatkan.</div>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Alamat (sesuai KTP)</label>
                <textarea required name="alamat_domisili" rows="2" class="form-control-custom"
                          placeholder="Alamat lengkap sesuai KTP: jalan, RT/RW, kelurahan, kecamatan, kota">{{ old('alamat_domisili', $assessment?->alamat_domisili) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Detail Alamat Pengakuan</label>
                <textarea required name="detail_alamat_pengakuan" rows="2" class="form-control-custom"
                          placeholder="Alamat sesuai pengakuan klien (RT/RW, patokan, dsb.)">{{ old('detail_alamat_pengakuan', $assessment?->detail_alamat_pengakuan) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Status Pendidikan</label>
                <select required name="status_pendidikan" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="-" @selected($strip('status_pendidikan'))>- Tidak diketahui -</option>
                    @foreach (['Sedang Bersekolah', 'Putus Sekolah', 'Tidak Pernah Sekolah', 'Tamat / Tidak Bersekolah Lagi'] as $opt)
                        <option value="{{ $opt }}" @selected(old('status_pendidikan', $assessment?->status_pendidikan) == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kategori Umur</label>
                <select required name="kategori_umur" class="form-control-custom @error('kategori_umur') is-invalid-custom @enderror">
                    <option value="">- Pilih -</option>
                    <option value="-" @selected($strip('kategori_umur'))>- Tidak diketahui -</option>
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
                <textarea required name="kondisi_ekonomi" rows="2" class="form-control-custom"
                          placeholder="Uraikan penghasilan, pengeluaran, dan kecukupan kebutuhan sehari-hari 2 bulan terakhir">{{ old('kondisi_ekonomi', $assessment?->kondisi_ekonomi) }}</textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Pekerjaan (klien/wali)</label>
                <input type="text" required name="pekerjaan" value="{{ old('pekerjaan', $assessment?->pekerjaan) }}"
                       placeholder="Misal: Buruh harian, Tidak bekerja, Pedagang" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Sumber Penghasilan</label>
                <input type="text" required name="sumber_penghasilan" value="{{ old('sumber_penghasilan', $assessment?->sumber_penghasilan) }}"
                       placeholder="Misal: Gaji sendiri, Bantuan keluarga, Tidak ada" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kepemilikan Kartu Bantuan Sosial</label>
                <input type="text" required name="kepemilikan_bansos" value="{{ old('kepemilikan_bansos', $assessment?->kepemilikan_bansos) }}"
                       placeholder="Misal: PKH, KIS, tidak ada" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Kondisi Tempat Tinggal</label>
                <input type="text" required name="kondisi_tempat_tinggal" value="{{ old('kondisi_tempat_tinggal', $assessment?->kondisi_tempat_tinggal) }}"
                       placeholder="Misal: Rumah sendiri, Mengontrak, Tidak ada tempat tinggal tetap" class="form-control-custom">
            </div>
        </div>
    </div>

    <!-- C. Kondisi Psikososial -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-people me-2"></i>C. Kondisi Psikososial</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-custom">Latar Belakang Masalah / Riwayat Kasus</label>
                <textarea required name="riwayat_masalah" rows="3" class="form-control-custom @error('riwayat_masalah') is-invalid-custom @enderror"
                          placeholder="Ceritakan kronologi dan latar belakang masalah yang membawa klien ke RPS">{{ old('riwayat_masalah', $assessment?->riwayat_masalah) }}</textarea>
                @error('riwayat_masalah') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label class="form-label-custom">Relasi dengan Keluarga</label>
                <select required name="relasi_keluarga" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="-" @selected($strip('relasi_keluarga'))>- Tidak diketahui -</option>
                    <option value="baik" @selected(old('relasi_keluarga', $assessment?->relasi_keluarga) == 'baik')>Baik</option>
                    <option value="kurang_baik" @selected(old('relasi_keluarga', $assessment?->relasi_keluarga) == 'kurang_baik')>Kurang Baik</option>
                    <option value="terputus" @selected(old('relasi_keluarga', $assessment?->relasi_keluarga) == 'terputus')>Terputus</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label-custom">Dukungan Sosial yang Tersedia</label>
                <input type="text" required name="dukungan_sosial" value="{{ old('dukungan_sosial', $assessment?->dukungan_sosial) }}"
                       placeholder="Misal: Keluarga besar, Tetangga, Lembaga sosial setempat" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Riwayat Kekerasan / Trauma (jika ada)</label>
                <textarea required name="riwayat_trauma" rows="2" class="form-control-custom"
                          placeholder="Uraikan kekerasan/trauma yang pernah dialami, atau isi - jika tidak ada">{{ old('riwayat_trauma', $assessment?->riwayat_trauma) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Permasalahan pada Lingkup Keluarga (2 Bulan Terakhir)</label>
                <textarea required name="masalah_keluarga" rows="2" class="form-control-custom"
                          placeholder="Masalah dalam rumah tangga/keluarga 2 bulan terakhir, atau isi - jika tidak ada">{{ old('masalah_keluarga', $assessment?->masalah_keluarga) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Permasalahan pada Lingkup Sekolah (2 Bulan Terakhir)</label>
                <textarea required name="masalah_sekolah" rows="2" class="form-control-custom"
                          placeholder="Masalah terkait sekolah/pendidikan 2 bulan terakhir, atau isi - jika tidak relevan">{{ old('masalah_sekolah', $assessment?->masalah_sekolah) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Permasalahan pada Lingkup Sosial Masyarakat (2 Bulan Terakhir)</label>
                <textarea required name="masalah_sosial" rows="2" class="form-control-custom"
                          placeholder="Masalah dengan tetangga/lingkungan masyarakat 2 bulan terakhir, atau isi - jika tidak ada">{{ old('masalah_sosial', $assessment?->masalah_sosial) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Cara Pandang Klien terhadap Masalah yang Sedang Dihadapi</label>
                <textarea required name="cara_pandang_klien" rows="2" class="form-control-custom"
                          placeholder="Bagaimana klien melihat/memaknai masalahnya sendiri">{{ old('cara_pandang_klien', $assessment?->cara_pandang_klien) }}</textarea>
            </div>
            <div class="col-12">
                <label class="form-label-custom">Rencana Kedepan</label>
                <textarea required name="rencana_kedepan" rows="2" class="form-control-custom"
                          placeholder="Harapan/rencana klien setelah penanganan selesai">{{ old('rencana_kedepan', $assessment?->rencana_kedepan) }}</textarea>
            </div>
        </div>
    </div>

    <!-- D. Observasi Kondisi Mental -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-emoji-neutral me-2"></i>D. Observasi Kondisi Mental</h5>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label-custom">Alam Perasaan (Kondisi Emosi Saat Assessment)</label>
                <input type="text" required name="kondisi_emosi" value="{{ old('kondisi_emosi', $assessment?->kondisi_emosi) }}"
                       placeholder="Misal: Tenang, Cemas, Mudah tersinggung" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Orientasi Realita</label>
                <select required name="orientasi_realita" class="form-control-custom">
                    <option value="">- Pilih -</option>
                    <option value="-" @selected($strip('orientasi_realita'))>- Tidak diketahui -</option>
                    <option value="baik" @selected(old('orientasi_realita', $assessment?->orientasi_realita) == 'baik')>Baik</option>
                    <option value="terganggu" @selected(old('orientasi_realita', $assessment?->orientasi_realita) == 'terganggu')>Terganggu</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Cara Komunikasi</label>
                <input type="text" required name="cara_komunikasi" value="{{ old('cara_komunikasi', $assessment?->cara_komunikasi) }}"
                       placeholder="Misal: Kooperatif, Pasif, Menghindari kontak mata" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Arus Percakapan</label>
                <input type="text" required name="arus_percakapan" value="{{ old('arus_percakapan', $assessment?->arus_percakapan) }}"
                       placeholder="Misal: Lancar, Terhambat, Berpindah-pindah topik" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Ingatan</label>
                <input type="text" required name="ingatan" value="{{ old('ingatan', $assessment?->ingatan) }}"
                       placeholder="Misal: Baik, Lupa kejadian tertentu" class="form-control-custom">
            </div>
            <div class="col-md-6">
                <label class="form-label-custom">Arah Perilaku</label>
                <input type="text" required name="arah_perilaku" value="{{ old('arah_perilaku', $assessment?->arah_perilaku) }}"
                       placeholder="Misal: Terarah, Gelisah, Agresif" class="form-control-custom">
            </div>
            <div class="col-12">
                <label class="form-label-custom">Catatan Observasi Tambahan</label>
                <textarea required name="catatan_observasi" rows="2" class="form-control-custom"
                          placeholder="Hal lain yang teramati saat assessment, di luar poin-poin di atas">{{ old('catatan_observasi', $assessment?->catatan_observasi) }}</textarea>
            </div>
        </div>
    </div>

    <!-- E. Simpulan & Rekomendasi -->
    <div class="card border-light shadow-sm p-4 mb-4">
        <h5 class="card-title mb-4"><i class="bi bi-clipboard2-check me-2"></i>E. Simpulan & Rekomendasi</h5>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label-custom">Simpulan Awal <span class="text-danger">*</span></label>
                <textarea name="simpulan_awal" rows="3" class="form-control-custom @error('simpulan_awal') is-invalid-custom @enderror" required
                          placeholder="Ringkasan hasil assessment: akar masalah dan kondisi klien saat ini">{{ old('simpulan_awal', $assessment?->simpulan_awal) }}</textarea>
                @error('simpulan_awal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label class="form-label-custom">Rekomendasi Penanganan</label>
                <textarea required name="rekomendasi_penanganan" rows="3" class="form-control-custom @error('rekomendasi_penanganan') is-invalid-custom @enderror"
                          placeholder="Saran tindak lanjut: rujukan, bimbingan, reunifikasi keluarga, dsb.">{{ old('rekomendasi_penanganan', $assessment?->rekomendasi_penanganan) }}</textarea>
                @error('rekomendasi_penanganan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <div class="assessment-form-actions">
        <div class="assessment-save-hint"><i class="bi bi-shield-check me-1"></i> Pastikan data sudah benar sebelum disimpan.</div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn-custom btn-custom-primary">
                <i class="bi bi-save me-1"></i> {{ $assessment ? 'Perbarui Assessment' : 'Simpan Assessment' }}
            </button>
            <a href="{{ route('clients.show', $client->id) }}" class="btn-custom btn-custom-secondary">Batal</a>
        </div>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Tanggal lahir: centang "Tidak diketahui" menonaktifkan kolom tanggal dan mengirim "-".
    const tglLahir = document.getElementById('tanggal_lahir');
    const tglStrip = document.getElementById('tanggal_lahir_strip');
    if (tglLahir && tglStrip) {
        const syncTgl = () => { tglLahir.disabled = tglStrip.checked; };
        tglStrip.addEventListener('change', syncTgl);
        syncTgl();
    }

    const umur = document.getElementById('umur');
    const kategori = document.querySelector('select[name="kategori_umur"]');

    // Menghitung usia dari Tanggal Lahir, memperhitungkan tahun SEKALIGUS bulan/tanggal
    // (bukan cuma selisih tahun), supaya hasilnya akurat sampai hari ini.
    function hitungUsiaDari(tanggalLahirValue) {
        if (!tanggalLahirValue) return null;
        const lahir = new Date(tanggalLahirValue);
        const sekarang = new Date();
        let usia = sekarang.getFullYear() - lahir.getFullYear();
        const belumUlangTahun =
            sekarang.getMonth() < lahir.getMonth() ||
            (sekarang.getMonth() === lahir.getMonth() && sekarang.getDate() < lahir.getDate());
        if (belumUlangTahun) usia--;
        return (usia >= 0 && usia <= 150) ? usia : null;
    }

    function kategoriDariUsia(n) {
        return n <= 5 ? 'balita' : n <= 12 ? 'anak' : n <= 17 ? 'remaja' : n <= 59 ? 'dewasa' : 'lansia';
    }

    // Umur dihitung otomatis begitu Tanggal Lahir dipilih, tanpa perlu diketik ulang.
    // Peksos tetap bisa mengoreksi manual setelahnya kalau memang perlu (mis. umur
    // perkiraan klien berbeda dari hitungan pasti kalender).
    if (tglLahir && umur) {
        tglLahir.addEventListener('change', function () {
            const usia = hitungUsiaDari(tglLahir.value);
            if (usia !== null) {
                umur.value = usia;
                umur.dispatchEvent(new Event('input')); // ikut memicu auto-isi Kategori Umur di bawah
            }
        });
    }

    if (!umur || !kategori) return;

    // Kategori Umur SELALU mengikuti Umur setiap kali berubah (bukan cuma sekali saat
    // kosong) -- supaya mengganti Tanggal Lahir / Umur langsung terlihat efeknya ke
    // Kategori Umur tanpa harus dipilih ulang manual.
    umur.addEventListener('input', function () {
        if (umur.value === '') return;
        const n = parseInt(umur.value, 10);
        if (isNaN(n)) return;
        kategori.value = kategoriDariUsia(n);
    });

    // Saat halaman baru dibuka (mis. form Edit): kalau Tanggal Lahir sudah terisi dari
    // data lama tapi Umur/Kategori Umur masih kosong, langsung hitung otomatis tanpa
    // menunggu pengguna mengubah apa pun -- tidak menimpa nilai yang sudah ada.
    if (tglLahir && tglLahir.value && !tglStrip?.checked) {
        const usiaAwal = hitungUsiaDari(tglLahir.value);
        if (usiaAwal !== null) {
            // Hanya mengisi kalau masih kosong -- kalau sudah ada nilai tersimpan
            // (termasuk hasil penilaian manual Peksos), biarkan apa adanya saat halaman
            // baru dibuka. Begitu field ini diubah secara aktif, listener di atas yang
            // akan menjaga keduanya tetap sinkron.
            if (umur && umur.value === '') {
                umur.value = usiaAwal;
            }
            if (kategori && kategori.value === '') {
                kategori.value = kategoriDariUsia(usiaAwal);
            }
        }
    }
});
</script>
@endpush