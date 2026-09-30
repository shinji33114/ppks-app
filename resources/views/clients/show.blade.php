@extends('layouts.spark')

@section('page-title', 'Detail Klien')
@section('page-subtitle', $client->nama_lengkap)

@section('content')

@if ($client->is_archived)
    <div class="alert alert-secondary d-flex align-items-center gap-2 mb-4">
        <i class="bi bi-archive-fill"></i>
        <div>
            <strong>Data ini sudah diarsipkan</strong> pada
            {{ optional($client->archived_at)->format('d F Y, H:i') }}
            oleh {{ optional($client->archivedBy)->name ?? 'sistem' }}.
            Halaman ini ditampilkan sebagai READ-ONLY.
        </div>
    </div>
@endif

<div class="row g-4">

    {{-- ================= KARTU RINGKASAN & AKSI ================= --}}
    <div class="col-lg-4">
        <div class="card border-light shadow-sm p-4 text-center">
            @if ($client->foto_klien)
                <img src="{{ asset('storage/' . $client->foto_klien) }}" alt="Foto Klien"
                     class="rounded-3 mb-3" style="width: 100%; max-height: 280px; object-fit: cover;">
            @else
                <div class="d-flex align-items-center justify-content-center bg-light rounded-3 mb-3"
                     style="height: 200px;">
                    <i class="bi bi-person-fill" style="font-size: 3rem; color: #ccc;"></i>
                </div>
            @endif

            <h5 class="mb-1">{{ $client->nama_lengkap }}</h5>
            @if ($client->nama_alias)
                <div class="text-muted small mb-2">alias {{ $client->nama_alias }}</div>
            @endif

            <span class="badge-table {{ $client->status_badge_class }} d-inline-block mb-3">{{ $client->status_label }}</span>

            @unless ($isClosedCase)
                @role('peksos')
                    @if ($client->status === 'assessment')
                        <a href="{{ route('assessment.edit', $client->id) }}" class="btn-custom btn-custom-primary w-100 mb-2">
                            <i class="bi bi-pencil-square me-1"></i> Edit Assessment
                        </a>
                    @endif
                @endrole

                @role('petugas-piket')
                    @if ($client->isStageOneEditable())
                        <a href="{{ route('clients.edit', $client) }}" class="btn-custom btn-custom-secondary w-100 mb-2">
                            <i class="bi bi-pencil me-1"></i> Edit Data Tahap 1
                        </a>
                    @endif
                @endrole

                @role('admin-rps')
                    @if (in_array($client->status, ['assessment', 'penanganan']))
                        <a href="{{ route('penanganan.index', $client->id) }}" class="btn-custom btn-custom-primary w-100 mb-2">
                            <i class="bi bi-clipboard2-pulse me-1"></i> Kelola Penanganan
                        </a>
                    @endif
                @endrole
            @endunless
        </div>
    </div>

    <div class="col-lg-8">

        {{-- ================= TAHAP 1: REGISTRASI AWAL ================= --}}
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-4"><i class="bi bi-1-circle me-2"></i>Tahap 1 — Registrasi Awal</h5>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="text-muted small">Tanggal Masuk</div>
                    <div class="fw-medium">{{ $client->tanggal_masuk->format('d F Y') }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Sumber Aduan</div>
                    <div class="fw-medium">{{ $client->sumber_aduan }}</div>
                </div>

                <div class="col-12">
                    <div class="text-muted small">TKP / Kronologi</div>
                    <div class="fw-medium">{{ $client->tkp_kronologi }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Kategori PPKS</div>
                    <div class="fw-medium">{{ $client->problem_awal ?: '—' }}</div>
                </div>

                <div class="col-md-6">
                    <div class="text-muted small">Data Dukung</div>
                    <div class="fw-medium">
                        @if ($client->data_dukung)
                            <a href="{{ asset('storage/' . $client->data_dukung) }}" target="_blank">Lihat dokumen</a>
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
                    <div class="fw-medium">{{ $client->created_at->format('d M Y, H:i') }}</div>
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
                            Data ini masih bisa diedit oleh Petugas Piket sampai Peksos mulai mengisi Tahap 2,
                            atau maksimal 1x24 jam sejak dicatat.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ================= TAHAP 2: ASSESSMENT PSIKOSOSIAL ================= --}}
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-4"><i class="bi bi-2-circle me-2"></i>Tahap 2 — Assessment Psikososial</h5>

            @if (! $client->assessment)
                <p class="text-muted mb-0">Belum ada assessment untuk klien ini.</p>
            @else
                @php($a = $client->assessment)
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">NIK</div><div class="fw-medium">{{ $a->nik ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Tempat, Tanggal Lahir</div>
                        <div class="fw-medium">{{ $a->tempat_lahir ?: '—' }}{{ $a->tanggal_lahir ? ', ' . $a->tanggal_lahir->format('d M Y') : '' }}</div>
                    </div>
                    <div class="col-md-4"><div class="text-muted small">Umur / Kategori</div>
                        <div class="fw-medium">{{ $a->umur ? $a->umur . ' th' : '—' }} / {{ \App\Models\ClientAssessment::KATEGORI_UMUR[$a->kategori_umur] ?? '—' }}</div>
                    </div>
                    <div class="col-md-4"><div class="text-muted small">Jenis Kelamin</div><div class="fw-medium">{{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : ($a->jenis_kelamin === 'P' ? 'Perempuan' : '—') }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Status Perkawinan</div><div class="fw-medium">{{ $a->status_perkawinan ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Pendidikan</div><div class="fw-medium">{{ $a->status_pendidikan ?: $a->pendidikan_terakhir ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Alamat (sesuai KTP)</div><div class="fw-medium">{{ $a->alamat_domisili ?: '—' }}</div></div>
                    @if ($a->detail_alamat_pengakuan)
                        <div class="col-12"><div class="text-muted small">Detail Alamat Pengakuan</div><div class="fw-medium">{{ $a->detail_alamat_pengakuan }}</div></div>
                    @endif

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-6"><div class="text-muted small">Kondisi Ekonomi (2 bulan terakhir)</div><div class="fw-medium">{{ $a->kondisi_ekonomi ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Pekerjaan / Sumber Penghasilan</div><div class="fw-medium">{{ $a->pekerjaan ?: '—' }} / {{ $a->sumber_penghasilan ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Kepemilikan Bansos</div><div class="fw-medium">{{ $a->kepemilikan_bansos ?: '—' }}</div></div>
                    <div class="col-md-6"><div class="text-muted small">Kondisi Tempat Tinggal</div><div class="fw-medium">{{ $a->kondisi_tempat_tinggal ?: '—' }}</div></div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-12"><div class="text-muted small">Latar Belakang Masalah / Riwayat Kasus</div><div class="fw-medium">{{ $a->riwayat_masalah ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Relasi Keluarga</div><div class="fw-medium">{{ ['baik'=>'Baik','kurang_baik'=>'Kurang Baik','terputus'=>'Terputus'][$a->relasi_keluarga] ?? '—' }}</div></div>
                    <div class="col-md-8"><div class="text-muted small">Dukungan Sosial</div><div class="fw-medium">{{ $a->dukungan_sosial ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Riwayat Trauma</div><div class="fw-medium">{{ $a->riwayat_trauma ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Masalah Lingkup Keluarga</div><div class="fw-medium">{{ $a->masalah_keluarga ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Masalah Lingkup Sekolah</div><div class="fw-medium">{{ $a->masalah_sekolah ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Masalah Lingkup Sosial Masyarakat</div><div class="fw-medium">{{ $a->masalah_sosial ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Cara Pandang Klien terhadap Masalah</div><div class="fw-medium">{{ $a->cara_pandang_klien ?: '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Rencana ke Depan</div><div class="fw-medium">{{ $a->rencana_kedepan ?: '—' }}</div></div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-md-4"><div class="text-muted small">Kondisi Emosi</div><div class="fw-medium">{{ $a->kondisi_emosi ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Cara Komunikasi</div><div class="fw-medium">{{ $a->cara_komunikasi ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Arus Percakapan</div><div class="fw-medium">{{ $a->arus_percakapan ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Ingatan</div><div class="fw-medium">{{ $a->ingatan ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Arah Perilaku</div><div class="fw-medium">{{ $a->arah_perilaku ?: '—' }}</div></div>
                    <div class="col-md-4"><div class="text-muted small">Orientasi Realita</div><div class="fw-medium">{{ ['baik'=>'Baik','terganggu'=>'Terganggu'][$a->orientasi_realita] ?? '—' }}</div></div>
                    <div class="col-12"><div class="text-muted small">Catatan Observasi Tambahan</div><div class="fw-medium">{{ $a->catatan_observasi ?: '—' }}</div></div>

                    <div class="col-12"><hr class="my-1"></div>

                    <div class="col-12"><div class="text-muted small">Simpulan Awal</div><div class="fw-medium">{{ $a->simpulan_awal }}</div></div>
                    <div class="col-12"><div class="text-muted small">Rekomendasi Penanganan</div><div class="fw-medium">{{ $a->rekomendasi_penanganan ?: '—' }}</div></div>
                </div>
            @endif
        </div>

        {{-- ================= TAHAP 3: PENANGANAN & SURAT KELUAR ================= --}}
        <div class="card border-light shadow-sm p-4 mb-4">
            <h5 class="card-title mb-4"><i class="bi bi-3-circle me-2"></i>Tahap 3 — Penanganan</h5>

            @if ($client->followups->isEmpty())
                <p class="text-muted mb-3">Belum ada catatan tindakan.</p>
            @else
                @foreach ($client->followups as $log)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between">
                            <strong>{{ \Carbon\Carbon::parse($log->tanggal_tindakan)->format('d F Y') }}</strong>
                            <span class="text-muted small">oleh {{ $log->user->name ?? '—' }}</span>
                        </div>
                        <p class="mb-0 mt-1">{{ $log->catatan_tindakan }}</p>
                    </div>
                @endforeach
            @endif

            @php($exitDoc = $client->exitDocuments->sortByDesc('created_at')->first())
            @if ($exitDoc)
                @php($statusMap = ['menunggu_persetujuan' => ['Menunggu Persetujuan Kepala RPS', 'pending'], 'disetujui' => ['Disetujui', 'success'], 'ditolak' => ['Ditolak, menunggu perbaikan Admin RPS', 'failed']])
                <hr>
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small">Surat {{ $exitDoc->type === 'rujukan' ? 'Rujukan' : 'Keluar' }} — Tujuan: {{ $exitDoc->tujuan }}</div>
                        <div class="fw-medium">Tanggal Keluar: {{ optional($exitDoc->tanggal_keluar)->format('d F Y') ?? '—' }}</div>
                        <div class="fw-medium">No. HP Penanggung Jawab: {{ $exitDoc->no_hp_penanggung_jawab ?: '—' }}</div>
                        @if ($exitDoc->status === 'ditolak' && $exitDoc->catatan_penolakan)
                            <div class="text-danger small mt-1">Catatan penolakan: {{ $exitDoc->catatan_penolakan }}</div>
                        @endif
                    </div>
                    <span class="badge-table {{ $statusMap[$exitDoc->status][1] ?? 'pending' }}">{{ $statusMap[$exitDoc->status][0] ?? $exitDoc->status }}</span>
                </div>
            @endif
        </div>
    </div>

</div>

<div class="mt-2">
    <a href="{{ route('clients.index') }}" class="btn-custom btn-custom-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Klien
    </a>
</div>
@endsection