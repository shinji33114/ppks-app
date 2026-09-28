@extends('layouts.spark')

@section('page-title', 'Edit Registrasi Awal')
@section('page-subtitle', 'Tahap 1 hanya dapat diedit maksimal 1 x 24 jam')

@section('content')
    <div class="card border-light shadow-sm p-4">
        <form method="POST" action="{{ route('clients.update', $client) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

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
                <label class="form-label-custom">TKP / Kronologi Singkat</label>
                <textarea name="tkp_kronologi" rows="5" class="form-control-custom"
                          required>{{ old('tkp_kronologi', $client->tkp_kronologi) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Nama Lengkap</label>
                <input name="nama_lengkap" value="{{ old('nama_lengkap', $client->nama_lengkap) }}"
                       class="form-control-custom" required>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Nama Alias (opsional)</label>
                <input type="text" name="nama_alias" value="{{ old('nama_alias', $client->nama_alias) }}"
                       class="form-control-custom" placeholder="Nama panggilan lain, jika ada">
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Kategori PPKS (opsional)</label>
                @php($problemAwal = old('problem_awal', $client->problem_awal))
                <select name="problem_awal" class="form-control-custom">
                    <option value="">- Belum diketahui / isi nanti -</option>
                    @foreach (['Lansia Terlantar', 'Anak Jalanan', 'Anak Terlantar', 'Disabilitas Terlantar', 'Korban Kekerasan', 'Gelandangan / Pengemis', 'ODGJ Terlantar', 'Lainnya'] as $kategori)
                        <option value="{{ $kategori }}" @selected($problemAwal === $kategori)>{{ $kategori }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Kondisi Fisik & Penampilan (opsional)</label>
                <textarea name="kondisi_fisik" rows="2" class="form-control-custom"
                          placeholder="Contoh: Tampak kurus, ada luka lecet di lengan">{{ old('kondisi_fisik', $client->kondisi_fisik) }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label-custom">Foto Klien (opsional jika tidak diganti)</label>
                <input type="file" name="foto_klien" accept="image/*" class="form-control-custom">
                @if ($client->foto_klien)
                    <div class="text-muted small mt-1">Foto saat ini sudah tersimpan. Upload baru hanya jika ingin menggantinya.</div>
                @endif
            </div>

            <div class="mb-4">
                <label class="form-label-custom">Data Dukung (opsional jika tidak diganti)</label>
                <input type="file" name="data_dukung" accept=".pdf,.jpg,.jpeg,.png" class="form-control-custom">
                @if ($client->data_dukung)
                    <div class="text-muted small mt-1">
                        Dokumen saat ini sudah tersimpan.
                        <a href="{{ asset('storage/' . $client->data_dukung) }}" target="_blank">Lihat dokumen</a>.
                        Upload baru hanya jika ingin menggantinya.
                    </div>
                @endif
            </div>

            <a href="{{ route('clients.show', $client) }}" class="btn btn-light">Batal</a>
            <button class="btn-custom btn-custom-primary">Simpan Perubahan</button>
        </form>
    </div>
@endsection