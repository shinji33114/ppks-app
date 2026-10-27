@extends('layouts.spark')

@section('page-title', 'Pendampingan Penerima Layanan')
@section('page-subtitle', $client->nama_lengkap)

@section('content')
<div class="workflow-page handling-workflow">
    <section class="workflow-hero workflow-hero-handling mb-4">
        <div class="workflow-hero-copy">
            <span class="workflow-eyebrow"><i class="bi bi-heart-pulse me-1"></i> Tahap 3 · Pendampingan</span>
            <h2>Catatan dan langkah selanjutnya</h2>
            <p>Catat perkembangan penerima layanan. Setelah pendampingan selesai, buat surat keluar atau surat rujukan.</p>
        </div>
        <div class="handling-client-summary">
            <span class="workflow-avatar workflow-avatar-handling"><i class="bi bi-person-fill"></i></span>
            <span><small>Penerima layanan</small><strong>{{ $client->nama_lengkap }}</strong></span>
            <span class="workflow-status {{ $client->status === \App\Models\Client::STATUS_PENANGANAN ? 'is-active' : 'is-ready' }}">
                <span></span>{{ $client->status_label }}
            </span>
        </div>
    </section>

    <div class="handling-meta-strip mb-4">
        <span><i class="bi bi-calendar-check"></i><span>Tanggal masuk<strong>{{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}</strong></span></span>
        <span><i class="bi bi-journal-check"></i><span>Catatan kegiatan<strong>{{ $followups->count() }} catatan</strong></span></span>
        <a href="{{ route('clients.show', $client->id) }}"><i class="bi bi-person-lines-fill"></i> Lihat data penerima layanan</a>
    </div>

<div class="row g-4">

    <div class="col-lg-7">
        <div class="card border-light shadow-sm p-4 mb-4 handling-card handling-entry-card">
            <div class="handling-card-heading">
                <span class="handling-card-icon"><i class="bi bi-journal-plus"></i></span>
                <div><h5>Tambah catatan kegiatan</h5><p>Catat kegiatan atau bantuan yang sudah diberikan.</p></div>
            </div>

            <form method="POST" action="{{ route('penanganan.store', $client->id) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label-custom">Tanggal kegiatan</label>
                        <input type="date" name="tanggal_tindakan" value="{{ old('tanggal_tindakan', now()->format('Y-m-d')) }}"
                               class="form-control-custom @error('tanggal_tindakan') is-invalid-custom @enderror">
                        @error('tanggal_tindakan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label-custom">Kegiatan atau bantuan</label>
                        <textarea name="catatan_tindakan" rows="1"
                                  class="form-control-custom @error('catatan_tindakan') is-invalid-custom @enderror"
                                  placeholder="Contoh: Memberi bimbingan pribadi atau membantu mengurus dokumen">{{ old('catatan_tindakan') }}</textarea>
                        @error('catatan_tindakan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
                <button type="submit" class="btn-custom btn-custom-primary mt-3">
                    <i class="bi bi-plus-lg me-1"></i> Simpan catatan
                </button>
            </form>
        </div>

        <div class="card border-light shadow-sm p-4 handling-card">
            <div class="handling-card-heading">
                <span class="handling-card-icon handling-history-icon"><i class="bi bi-clock-history"></i></span>
                <div><h5>Riwayat kegiatan</h5><p>Daftar kegiatan yang sudah dilakukan.</p></div>
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
                    <strong>Belum ada catatan kegiatan</strong>
                    <p>Catatan yang ditambahkan akan tersimpan dan tampil di sini.</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-light shadow-sm p-4 handling-card exit-document-card">
            <div class="handling-card-heading">
                <span class="handling-card-icon exit-document-icon"><i class="bi bi-file-earmark-arrow-up"></i></span>
                <div><h5>Surat keluar atau rujukan</h5><p>Buat surat jika penerima layanan pulang atau dirujuk.</p></div>
            </div>
            <div class="exit-document-note">
                Setelah surat dibuat, layanan akan ditutup. Surat dapat langsung dilihat, dicetak, dan diunduh.
            </div>

            <form method="POST" action="{{ route('exit-documents.store', $client->id) }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label-custom">Jenis surat</label>
                    <select name="type" class="form-control-custom @error('type') is-invalid-custom @enderror">
                        <option value="">- Pilih jenis surat -</option>
                        <option value="surat_keluar" @selected(old('type') == 'surat_keluar')>Surat keluar atau pemulangan</option>
                        <option value="rujukan" @selected(old('type') == 'rujukan')>Surat rujukan</option>
                    </select>
                    @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Nomor surat (jika ada)</label>
                    <input type="text" name="nomor_surat" value="{{ old('nomor_surat') }}"
                           class="form-control-custom @error('nomor_surat') is-invalid-custom @enderror">
                    @error('nomor_surat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Tujuan surat</label>
                    <input type="text" name="tujuan" value="{{ old('tujuan') }}"
                           placeholder="Contoh: Keluarga penerima layanan atau nama lembaga tujuan"
                           class="form-control-custom @error('tujuan') is-invalid-custom @enderror">
                    @error('tujuan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Keterangan tambahan (jika ada)</label>
                    <textarea name="keterangan" rows="2" class="form-control-custom"
                              placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Tanggal surat</label>
                    <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', now()->format('Y-m-d')) }}"
                           class="form-control-custom @error('tanggal_surat') is-invalid-custom @enderror">
                    @error('tanggal_surat') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-custom">Tanggal layanan berakhir</label>
                    <input type="date" name="tanggal_keluar" value="{{ old('tanggal_keluar') }}"
                           min="{{ \Carbon\Carbon::parse($client->tanggal_masuk)->format('Y-m-d') }}"
                           class="form-control-custom @error('tanggal_keluar') is-invalid-custom @enderror">
                    @error('tanggal_keluar') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    <div class="text-muted small mt-1">
                        Pilih tanggal yang sama atau setelah tanggal masuk: {{ \Carbon\Carbon::parse($client->tanggal_masuk)->translatedFormat('d M Y') }}
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label-custom">Nomor HP penanggung jawab</label>
                    <input type="text" name="no_hp_penanggung_jawab" value="{{ old('no_hp_penanggung_jawab') }}"
                           placeholder="08xxxxxxxxxx"
                           class="form-control-custom @error('no_hp_penanggung_jawab') is-invalid-custom @enderror">
                    @error('no_hp_penanggung_jawab') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn-custom btn-custom-primary w-100"
                        onclick="return confirm('Buat surat ini? Layanan penerima akan ditutup.')">
                    <i class="bi bi-send me-1"></i> Buat surat
                </button>
            </form>
        </div>
    </div>

</div>

<div class="mt-4">
    <a href="{{ route('clients.show', $client->id) }}" class="btn-custom btn-custom-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke data penerima layanan
    </a>
</div>
</div>
@endsection