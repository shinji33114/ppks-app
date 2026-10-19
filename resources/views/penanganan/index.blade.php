@extends('layouts.spark')

@section('page-title', 'Penanganan Klien')
@section('page-subtitle', $client->nama_lengkap)

@section('content')
<div class="workflow-page handling-workflow">
    <section class="workflow-hero workflow-hero-handling mb-4">
        <div class="workflow-hero-copy">
            <span class="workflow-eyebrow"><i class="bi bi-heart-pulse me-1"></i> Tahap 3 · Pendampingan</span>
            <h2>Rencana tindak lanjut klien</h2>
            <p>Catat perkembangan secara berkala dan siapkan surat keluar atau rujukan saat proses selesai.</p>
        </div>
        <div class="handling-client-summary">
            <span class="workflow-avatar workflow-avatar-handling"><i class="bi bi-person-fill"></i></span>
            <span><small>Klien</small><strong>{{ $client->nama_lengkap }}</strong></span>
            <span class="workflow-status {{ $client->status === \App\Models\Client::STATUS_PENANGANAN ? 'is-active' : 'is-ready' }}">
                <span></span>{{ $client->status_label }}
            </span>
        </div>
    </section>

    <div class="handling-meta-strip mb-4">
        <span><i class="bi bi-calendar-check"></i><span>Tanggal masuk<strong>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}</strong></span></span>
        <span><i class="bi bi-journal-check"></i><span>Catatan tindakan<strong>{{ $followups->count() }} catatan</strong></span></span>
        <a href="{{ route('clients.show', $client->id) }}"><i class="bi bi-person-lines-fill"></i> Lihat profil klien</a>
    </div>

<div class="row g-4">

    <div class="col-lg-7">
        <div class="card border-light shadow-sm p-4 mb-4 handling-card handling-entry-card">
            <div class="handling-card-heading">
                <span class="handling-card-icon"><i class="bi bi-journal-plus"></i></span>
                <div><h5>Catat tindakan</h5><p>Tambahkan bimbingan atau intervensi terbaru.</p></div>
            </div>

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

        <div class="card border-light shadow-sm p-4 handling-card">
            <div class="handling-card-heading">
                <span class="handling-card-icon handling-history-icon"><i class="bi bi-clock-history"></i></span>
                <div><h5>Riwayat tindakan</h5><p>Catatan pendampingan yang telah dilakukan.</p></div>
                <span class="workflow-count ms-auto">{{ $followups->count() }}</span>
            </div>

            @forelse ($followups as $log)
                <div class="handling-timeline-entry">
                    <span class="handling-timeline-marker"><i class="bi bi-check2"></i></span>
                    <div class="handling-timeline-content">
                        <div class="handling-timeline-top">
                            <strong>{{ \Carbon\Carbon::parse($log->tanggal_tindakan)->translatedFormat('d F Y') }}</strong>
                            <span class="text-muted small"><i class="bi bi-person me-1"></i>{{ $log->user->name ?? 'Tidak diketahui' }}</span>
                        </div>
                        <p>{{ $log->catatan_tindakan }}</p>
                    </div>
                </div>
            @empty
                <div class="handling-history-empty">
                    <span><i class="bi bi-journal-text"></i></span>
                    <strong>Belum ada catatan tindakan</strong>
                    <p>Catatan yang ditambahkan akan tersimpan dan tampil di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-light shadow-sm p-4 handling-card exit-document-card">
            <div class="handling-card-heading">
                <span class="handling-card-icon exit-document-icon"><i class="bi bi-file-earmark-arrow-up"></i></span>
                <div><h5>Surat keluar / rujukan</h5><p>Terbitkan surat untuk menyelesaikan penanganan.</p></div>
            </div>
            <div class="exit-document-note">
                Setelah surat diterbitkan, status klien berubah menjadi selesai atau dirujuk. Surat dapat langsung dilihat, dicetak, dan diunduh tanpa tahap persetujuan.
            </div>

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
                        Tidak boleh sebelum tanggal masuk: {{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}
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
                        onclick="return confirm('Terbitkan surat dan selesaikan penanganan klien ini?')">
                    <i class="bi bi-send me-1"></i> Terbitkan Surat
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
</div>
@endsection