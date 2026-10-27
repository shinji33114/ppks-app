@extends('layouts.spark')

@section('page-title', 'Data Penerima Layanan')
@section('page-subtitle', $client->nama_lengkap)

@section('content')

@if ($isClosedCase)
    <div class="alert alert-secondary d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-archive-fill"></i>
        <div>
            <strong>Data ini sudah ditutup dan disimpan</strong> pada
            {{ optional($client->archived_at)->translatedFormat('d F Y, H:i') }}
            oleh {{ optional($client->archivedBy)->name ?? 'sistem' }}.
            Data hanya dapat dilihat dan tidak dapat diubah.
        </div>
    </div>
@endif

@php
    $isFinal = in_array($client->status, [\App\Models\Client::STATUS_SELESAI, \App\Models\Client::STATUS_DIRUJUK], true);
    $currentStep = match ($client->status) {
        \App\Models\Client::STATUS_DRAFT => 0,
        \App\Models\Client::STATUS_ASSESSMENT => 1,
        \App\Models\Client::STATUS_PENANGANAN => 2,
        default => 3,
    };
    $steps = [
        ['Registrasi', 'Tahap 1'],
        ['Penilaian', 'Tahap 2'],
        ['Pendampingan', 'Tahap 3'],
        [$isFinal ? $client->status_label : 'Selesai / Dirujuk', 'Hasil akhir'],
    ];
@endphp

@push('styles')
<style>
    .stepper-ppks { display: flex; align-items: flex-start; margin-bottom: 1.5rem; }
    .stepper-ppks .step { flex: 1; text-align: center; position: relative; }
    .stepper-ppks .step:not(:last-child)::after {
        content: ''; position: absolute; top: 17px; left: 50%; width: 100%; height: 3px;
        background: #dee2e6; z-index: 0;
    }
    .stepper-ppks .step.done:not(:last-child)::after { background: #198754; }
    .stepper-ppks .dot {
        position: relative; z-index: 1; width: 36px; height: 36px; margin: 0 auto 6px;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-weight: 700; background: #dee2e6; color: #6c757d;
    }
    .stepper-ppks .step.done .dot { background: #198754; color: #fff; }
    .stepper-ppks .step.current .dot { background: #0a2e1e; color: #fff; box-shadow: 0 0 0 4px rgba(25, 135, 84, .25); }
    .stepper-ppks .step-label { font-weight: 600; font-size: .9rem; }
    .stepper-ppks .step.current .step-label { color: #0a2e1e; }
    .stepper-ppks .step-sub { font-size: .75rem; color: #6c757d; }
</style>
@endpush

<div class="card border-light shadow-sm p-4 mb-4">
    <div class="stepper-ppks" aria-label="Tahap layanan">
        @foreach ($steps as $i => $step)
            @php
                $state = ($isFinal || $i < $currentStep) ? 'done' : (($i === $currentStep) ? 'current' : 'pending');
            @endphp
            <div class="step {{ $state }}">
                <div class="dot">
                    @if ($state === 'done')
                        <i class="bi bi-check-lg"></i>
                    @else
                        {{ $i + 1 }}
                    @endif
                </div>
                <div class="step-label">{{ $step[0] }}</div>
                <div class="step-sub">{{ $step[1] }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="row g-4">

    {{-- ================= KARTU RINGKASAN & AKSI ================= --}}
    <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4 text-center">
            @if ($client->foto_klien)
                <img src="{{ route('clients.photo', $client) }}" alt="Foto Penerima Layanan"
                     class="rounded-3 mb-3" style="width: 100%; max-height: 280px; object-fit: cover;">
            @else
                <div class="d-flex align-items-center justify-content-center bg-light rounded-3 mb-3"
                     style="height: 200px;">
                    <i class="bi bi-person-fill" style="font-size: 3rem; color: #ccc;"></i>
                </div>
            @endif

            @hasanyrole('admin|super-admin')
                @if (! $isClosedCase && $client->status === \App\Models\Client::STATUS_DRAFT)
                    <a href="{{ route('assessment.create', $client) }}" class="btn-custom btn-custom-primary w-100 mb-3">
                    <i class="bi bi-clipboard2-pulse me-1"></i> Mulai Penilaian
                    </a>
                @endif
            @endhasanyrole

            <h5 class="mb-1">{{ $client->nama_lengkap }}</h5>
            @if ($client->nama_alias)
                <div class="text-muted small mb-2">Nama panggilan: {{ $client->nama_alias }}</div>
            @endif

            <span class="badge-table {{ $client->status_badge_class }} d-inline-flex mb-3">{{ $client->status_label }}</span>

            @unless ($isClosedCase)
                @hasanyrole('admin|super-admin')
                    @if ($client->status === 'assessment')
                        <a href="{{ route('assessment.edit', $client->id) }}" class="btn-custom btn-custom-primary w-100 mb-2">
                            <i class="bi bi-pencil-square me-1"></i> Ubah Hasil Penilaian
                        </a>
                    @endif
                @endhasanyrole

                @hasanyrole('admin|super-admin')
                    @if ($client->isStageOneEditable())
                        <a href="{{ route('clients.edit', $client) }}" class="btn-custom btn-custom-secondary w-100 mb-2">
                            <i class="bi bi-pencil me-1"></i> Ubah Data Pendaftaran
                        </a>
                    @endif
                @endhasanyrole

                @hasanyrole('admin|super-admin')
                    @if (in_array($client->status, ['assessment', 'penanganan']))
                        <a href="{{ route('penanganan.index', $client->id) }}" class="btn-custom btn-custom-primary w-100 mb-2">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Buka Pendampingan
                        </a>
                    @endif
                @endhasanyrole
            @endunless

            @hasanyrole('admin|super-admin')
                @if ($isClosedCase)
        <a href="{{ route('clients.revisi.edit', $client->id) }}" class="btn-custom btn-custom-secondary w-100 mb-2">
            <i class="bi bi-pencil-square me-1"></i> Perbaiki Data
        </a>
    @endif
@endhasanyrole
        </div>
    </div>

    <div class="col-lg-8">

        {{-- ================= TAHAP 1: REGISTRASI AWAL ================= --}}
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-4"><i class="bi bi-1-circle me-2"></i>Tahap 1 — Registrasi Awal</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Tanggal Masuk</div>
                    <div class="fw-medium">{{ $client->tanggal_masuk->translatedFormat('d F Y') }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Sumber Aduan</div>
                    <div class="fw-medium">{{ $client->sumber_aduan }}</div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Lokasi dan cerita kejadian</div>
                    <div class="fw-medium">{{ $client->tkp_kronologi }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Jenis kebutuhan layanan</div>
                    <div class="fw-medium">{{ $client->kategori_ppks ?: '—' }}</div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Masalah atau kebutuhan awal</div>
                    <div class="fw-medium">{{ $client->problem_awal ?: '—' }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Dokumen pendukung</div>
                    <div class="fw-medium">
                        @if ($client->data_dukung)
                            <a href="{{ route('clients.document', $client) }}" target="_blank">Lihat dokumen</a>
                        @else
                            —
                        @endif
                    </div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">Kondisi Fisik & Penampilan</div>
                    <div class="fw-medium">{{ $client->kondisi_fisik ?: '—' }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Dicatat pada</div>
                    <div class="fw-medium">{{ $client->created_at->translatedFormat('d M Y, H:i') }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Terakhir diperbarui</div>
                    <div class="fw-medium">{{ $client->updated_at->diffForHumans() }}</div>
                </div>
            </div>

            @if ($client->status === 'draft')
                <div class="mt-3 p-3 rounded-3" style="background-color: #fffbea;">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill text-warning mt-1"></i>
                        <p class="mb-0 text-muted small">
                            Data ini dapat diubah oleh petugas sebelum penilaian dimulai, atau paling lama 24 jam setelah didaftarkan.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ================= TAHAP 2: PENILAIAN ================= --}}
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-4"><i class="bi bi-2-circle me-2"></i>Tahap 2 — Penilaian Kondisi dan Kebutuhan</h5>

            @if (! $client->assessment)
                <p class="text-muted mb-0">Belum ada hasil penilaian.</p>
            @else
                @php($a = $client->assessment)
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">Nomor Induk Kependudukan (NIK)</div><div class="fw-medium">{{ $a->nik ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Tempat, Tanggal Lahir</div>
                        <div class="fw-medium">{{ $a->tempat_lahir ?: '—' }}{{ $a->tanggal_lahir ? ', ' . $a->tanggal_lahir->translatedFormat('d M Y') : '' }}</div>
                    </div>
                    <div class="col-md-4"><div class="text-muted small">Umur / Kategori</div>
                        <div class="fw-medium">{{ $a->umur ? $a->umur . ' th' : '—' }} / {{ \App\Models\ClientAssessment::KATEGORI_UMUR[$a->kategori_umur] ?? '—' }}</div>
                    </div>
                    <div class="col-md-4"><div class="text-muted small">Jenis Kelamin</div><div class="fw-medium">{{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : ($a->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Status Perkawinan</div><div class="fw-medium">{{ $a->status_perkawinan ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Pendidikan</div><div class="fw-medium">{{ $a->status_pendidikan ?: $a->pendidikan_terakhir ?: '—' }}</div>@if ($a->status_pendidikan && $a->pendidikan_terakhir)<div class="text-muted small">Pendidikan terakhir: {{ $a->pendidikan_terakhir }}</div>@endif</div>
                    <div class="col-12"><div class="text-muted small">Alamat (sesuai KTP)</div><div class="fw-medium">{{ $a->alamat_domisili ?: '—' }}</div></div>
                    @if ($a->detail_alamat_pengakuan)
                        <div class="col-12"><div class="text-muted small">Alamat menurut keterangan penerima layanan</div><div class="fw-medium">{{ $a->detail_alamat_pengakuan }}</div></div>
                    @endif

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-6">                    <div class="text-muted small">Keadaan ekonomi (2 bulan terakhir)</div><div class="fw-medium">{{ $a->kondisi_ekonomi ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Pekerjaan / sumber penghasilan</div><div class="fw-medium">{{ $a->pekerjaan ?: '—' }} / {{ $a->sumber_penghasilan ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Bantuan sosial yang diterima</div><div class="fw-medium">{{ $a->kepemilikan_bansos ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Kondisi Tempat Tinggal</div><div class="fw-medium">{{ $a->kondisi_tempat_tinggal ?: '—' }}</div></div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-12"><div class="text-muted small">Latar belakang masalah</div><div class="fw-medium">{{ $a->riwayat_masalah ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Hubungan dengan keluarga</div><div class="fw-medium">{{ ['baik'=>'Baik','kurang_baik'=>'Kurang Baik','terputus'=>'Terputus'][$a->relasi_keluarga] ?? '—' }}</div></div>
                    <div class="col-md-8"><div class="text-muted small">Orang atau kelompok yang membantu</div><div class="fw-medium">{{ $a->dukungan_sosial ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Pengalaman kekerasan atau kejadian buruk</div><div class="fw-medium">{{ $a->riwayat_trauma ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Masalah dalam keluarga</div><div class="fw-medium">{{ $a->masalah_keluarga ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Masalah di sekolah</div><div class="fw-medium">{{ $a->masalah_sekolah ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Masalah dengan tetangga atau lingkungan</div><div class="fw-medium">{{ $a->masalah_sosial ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Pandangan penerima layanan tentang masalah</div><div class="fw-medium">{{ $a->cara_pandang_klien ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Rencana ke Depan</div><div class="fw-medium">{{ $a->rencana_kedepan ?: '—' }}</div></div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-4"><div class="text-muted small">Kondisi Emosi</div><div class="fw-medium">{{ $a->kondisi_emosi ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Cara berkomunikasi</div><div class="fw-medium">{{ $a->cara_komunikasi ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Cara berbicara</div><div class="fw-medium">{{ $a->arus_percakapan ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Ingatan</div><div class="fw-medium">{{ $a->ingatan ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Perilaku yang terlihat</div><div class="fw-medium">{{ $a->arah_perilaku ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Pemahaman terhadap keadaan sekitar</div><div class="fw-medium">{{ ['baik'=>'Baik','terganggu'=>'Terganggu'][$a->orientasi_realita] ?? '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Catatan pengamatan tambahan</div><div class="fw-medium">{{ $a->catatan_observasi ?: '—' }}</div></div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-12"><div class="text-muted small">Ringkasan hasil penilaian</div><div class="fw-medium">{{ $a->simpulan_awal }}</div></div>
                    <div class="col-12"><div class="text-muted small">Saran bantuan selanjutnya</div><div class="fw-medium">{{ $a->rekomendasi_penanganan ?: '—' }}</div></div>
                </div>
            @endif
        </div>

        {{-- ================= TAHAP 3: PENANGANAN & SURAT KELUAR ================= --}}
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-4"><i class="bi bi-3-circle me-2"></i>Tahap 3 — Pendampingan</h5>

            @if ($client->followups->isEmpty())
                <p class="text-muted mb-3">Belum ada catatan kegiatan.</p>
            @else
                @foreach ($client->followups as $log)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ \Carbon\Carbon::parse($log->tanggal_tindakan)->translatedFormat('d F Y') }}</strong>
                            <span class="text-muted small">oleh {{ $log->user->name ?? '—' }}</span>
                        </div>
                        <p class="mb-0 mt-1">{{ $log->catatan_tindakan }}</p>
                    </div>
                @endforeach
            @endif

            @php($exitDoc = $client->exitDocuments->sortByDesc('created_at')->first())
            @if ($exitDoc)
                @php($statusMap = ['selesai' => ['Selesai', 'success'], 'menunggu_persetujuan' => ['Menunggu persetujuan', 'pending'], 'disetujui' => ['Selesai', 'success'], 'ditolak' => ['Perlu diperbaiki', 'failed']])
                <hr>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Surat {{ $exitDoc->type === 'rujukan' ? 'Rujukan' : 'Keluar' }} — Tujuan: {{ $exitDoc->tujuan }}</div>
                        <div class="fw-medium">Tanggal layanan berakhir: {{ optional($exitDoc->tanggal_keluar)->translatedFormat('d F Y') ?? '—' }}</div>
                        <div class="fw-medium">Nomor HP yang dapat dihubungi: {{ $exitDoc->no_hp_penanggung_jawab ?: '—' }}</div>
                        @if ($exitDoc->status === 'ditolak' && $exitDoc->catatan_penolakan)
                            <div class="text-danger small mt-1">Alasan surat perlu diperbaiki: {{ $exitDoc->catatan_penolakan }}</div>
                        @endif
                    </div>
                    <span class="badge-table {{ $statusMap[$exitDoc->status][1] ?? 'pending' }}">{{ $statusMap[$exitDoc->status][0] ?? $exitDoc->status }}</span>
                </div>
            @endif
        </div>
    </div>

</div>

@hasanyrole('admin|super-admin')
    @if ($client->revisions->isNotEmpty())
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-3"><i class="bi bi-clock-history me-2"></i>Riwayat Perubahan Data</h5>
            @foreach ($client->revisions as $rev)
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $rev->created_at->translatedFormat('d F Y, H:i') }}</strong>
                        <span class="text-muted small">oleh {{ $rev->user->name ?? '—' }}</span>
                    </div>
                    <div class="small mt-1"><span class="text-muted">Alasan:</span> {{ $rev->alasan }}</div>
                    <ul class="small mb-0 mt-1">
                            @foreach (($rev->changes ?? []) as $field => $chg)
                                <li>{{ $chg['label'] ?? $field }}: {{ $chg['from'] ?? $chg['dari'] ?? '—' }} → {{ $chg['to'] ?? $chg['ke'] ?? '—' }}</li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    @endif
@endhasanyrole

<div class="mt-2">
    <a href="{{ route('clients.index') }}" class="btn-custom btn-custom-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penerima Layanan
    </a>
</div>
@endsection