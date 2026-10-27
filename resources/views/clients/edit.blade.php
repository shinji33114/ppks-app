@extends('layouts.spark')

@section('page-title', 'Ubah Data Pendaftaran')
@section('page-subtitle', 'Data dapat diubah paling lama 24 jam setelah didaftarkan')

@section('content')
    <div class="card border-light shadow-sm p-4">
        <form method="POST" action="{{ route('clients.update', $client) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div class="alert alert-info small mb-4">
                Isi semua kolom. Jika informasi tidak diketahui atau tidak ada, isi dengan tanda <strong>-</strong>.
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Tanggal Masuk</label>
                <input type="date" name="tanggal_masuk"
                       value="{{ old('tanggal_masuk', $client->tanggal_masuk->format('Y-m-d')) }}"
                       class="form-control-custom" required>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Sumber Aduan</label>
                <input name="sumber_aduan" value="{{ old('sumber_aduan', $client->sumber_aduan) }}"
                       class="form-control-custom" required>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Lokasi dan cerita kejadian</label>
                <textarea name="tkp_kronologi" rows="5" class="form-control-custom"
                          required>{{ old('tkp_kronologi', $client->tkp_kronologi) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Nama Lengkap</label>
                <input name="nama_lengkap" value="{{ old('nama_lengkap', $client->nama_lengkap) }}"
                       class="form-control-custom" required>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Nama Alias</label>
                <input type="text" name="nama_alias" value="{{ old('nama_alias', $client->nama_alias) }}"
                       class="form-control-custom" required placeholder="Nama panggilan lain. Isi - jika tidak ada">
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Jenis kebutuhan layanan (PPKS)</label>
                @php($kategoriTerpilih = old('kategori_ppks', $client->kategori_ppks))
                <select name="kategori_ppks" class="form-control-custom" required>
                    <option value="">- Pilih -</option>
                    <option value="-" @selected($kategoriTerpilih === '-' || $kategoriTerpilih === null)>- Tidak diketahui -</option>
                    @foreach (\App\Models\Client::KATEGORI_PPKS as $kategori)
                        <option value="{{ $kategori }}" @selected($kategoriTerpilih === $kategori)>{{ $kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Masalah atau kebutuhan awal</label>
                <textarea name="problem_awal" rows="2" class="form-control-custom" required
                          placeholder="Tuliskan masalah atau bantuan yang dibutuhkan. Isi - jika tidak ada">{{ old('problem_awal', $client->problem_awal) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Kondisi Fisik & Penampilan</label>
                <textarea name="kondisi_fisik" rows="2" class="form-control-custom" required
                          placeholder="Contoh: Tampak kurus, ada luka lecet di lengan. Isi - jika tidak ada">{{ old('kondisi_fisik', $client->kondisi_fisik) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Foto Penerima Layanan{{ $client->foto_klien ? ' (unggah hanya jika ingin mengganti)' : '' }}</label>
                <input type="file" name="foto_klien" accept="image/*" class="form-control-custom" @required(! $client->foto_klien)>
                @if ($client->foto_klien)
                    <div class="text-muted small mt-1">Foto saat ini sudah tersimpan. Unggah foto baru hanya jika ingin menggantinya.</div>
                @endif
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Dokumen Pendukung{{ $client->data_dukung ? ' (unggah hanya jika ingin mengganti)' : '' }}</label>
                <input type="file" name="data_dukung" id="data_dukung" accept=".pdf,.jpg,.jpeg,.png" class="form-control-custom">
                @if (! $client->data_dukung)
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="tanpa_data_dukung" id="tanpa_data_dukung" value="1"
                               @checked(old('tanpa_data_dukung', '1'))>
                        <label class="form-check-label small" for="tanpa_data_dukung">Tidak ada data dukung (-)</label>
                    </div>
                @endif
                @if ($client->data_dukung)
                    <div class="text-muted small mt-1">
                        Dokumen saat ini sudah tersimpan.
                        <a href="{{ route('clients.document', $client) }}" target="_blank">Lihat dokumen</a>.
                        Unggah dokumen baru hanya jika ingin menggantinya.
                    </div>
                @endif
            </div>

            <a href="{{ route('clients.show', $client) }}" class="btn btn-light">Batal</a>
            <button class="btn-custom btn-custom-primary">Simpan Perubahan</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const cb = document.getElementById('tanpa_data_dukung');
    const file = document.getElementById('data_dukung');
    if (!cb || !file) return; // sudah ada dokumen tersimpan: unggah ulang bersifat opsional
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
