<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 18mm 20mm; }
        body { color: #161616; font-family: DejaVu Sans, sans-serif; font-size: 10pt; line-height: 1.35; }
        .header { border-bottom: 3px double #161616; padding-bottom: 8px; text-align: center; }
        .header h1, .header h2, .header p { margin: 0; }
        .header h1 { font-size: 15pt; }
        .header h2 { font-size: 13pt; }
        .header p { font-size: 9pt; }
        .status { border: 1px solid #555; font-size: 9pt; font-weight: bold; margin: 10px auto; padding: 5px; text-align: center; width: 70%; }
        h3 { font-size: 12pt; margin: 12px 0; text-align: center; text-decoration: underline; }
        table { border-collapse: collapse; width: 100%; }
        .meta-layout { margin: 0 0 12px; page-break-inside: avoid; }
        .meta-layout > tbody > tr > td { border: 0; padding: 0; vertical-align: middle; }
        .meta-cell { width: 76%; }
        .meta { margin: 0; }
        .meta td { padding: 2px 10px 2px 0; vertical-align: top; }
        .identity td { border: 1px solid #555; padding: 4px 6px; vertical-align: top; }
        .identity td:first-child { width: 34%; }
        .photo-cell { text-align: right; width: 24%; }
        .photo-frame { border: 1px solid #777; display: inline-block; height: 120px; padding: 3px; text-align: center; width: 85px; }
        .client-photo { max-height: 118px; max-width: 83px; }
        .photo-placeholder { color: #666; font-size: 7pt; height: 110px; padding-top: 8px; }
        .signatures { margin-top: 18px; }
        .signatures td { text-align: center; vertical-align: top; }
        .signature-space { height: 45px; }
        .name { font-weight: bold; text-decoration: underline; }
    </style>
</head>
<body>
    @php($assessment = $document->client->assessment)

    <header class="header">
        <h1>PEMERINTAH DAERAH</h1>
        <h2>RUMAH PERLINDUNGAN SOSIAL PPKS</h2>
        <p>Sistem Pelayanan Penanganan Pemerlu Pelayanan Kesejahteraan Sosial</p>
    </header>

    <div class="status">SURAT TELAH DITERBITKAN</div>

    <h3>{{ $document->type === 'rujukan' ? 'SURAT RUJUKAN' : 'SURAT KELUAR / PEMULANGAN' }}</h3>

    <table class="meta-layout">
        <tr>
            <td class="meta-cell">
                <table class="meta">
                    <tr><td>Nomor</td><td>: {{ $document->nomor_surat ?: '-' }}</td></tr>
                    <tr><td>Tanggal</td><td>: {{ $document->tanggal_surat->format('d-m-Y') }}</td></tr>
                    <tr><td>Kepada</td><td>: Yth. {{ $document->tujuan }}</td></tr>
                    <tr><td>Tujuan surat</td><td>: {{ $document->tujuan }}</td></tr>
                </table>
            </td>
            <td class="photo-cell">
                <div class="photo-frame">
                    @if ($photoFileUri)
                        <img class="client-photo" src="{{ $photoFileUri }}" alt="Foto Klien">
                    @else
                        <div class="photo-placeholder">Foto tidak tersedia</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <p>Dengan hormat,</p>
    <p>Melalui surat ini kami menerbitkan keterangan {{ $document->type === 'rujukan' ? 'rujukan' : 'keluar/pemulangan' }} untuk klien berikut:</p>

    <table class="identity">
        <tr><td>Nama Lengkap</td><td>{{ $document->client->nama_lengkap }}</td></tr>
        <tr><td>NIK</td><td>{{ $assessment?->nik ?: '-' }}</td></tr>
        <tr><td>Jenis Kelamin</td><td>{{ $assessment?->jenis_kelamin ?: '-' }}</td></tr>
        <tr><td>Alamat</td><td>{{ $assessment?->alamat_domisili ?: '-' }}</td></tr>
        <tr><td>Tanggal Masuk</td><td>{{ $document->client->tanggal_masuk->format('d-m-Y') }}</td></tr>
        <tr><td>Tanggal Keluar</td><td>{{ $document->tanggal_keluar->format('d-m-Y') }}</td></tr>
        <tr><td>Penanggung Jawab</td><td>{{ $document->no_hp_penanggung_jawab }}</td></tr>
    </table>

    @if ($document->keterangan)
        <p>{{ $document->keterangan }}</p>
    @endif

    <p>Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.</p>

    <table class="signatures">
        <tr>
            <td colspan="2">
                {{ $document->tanggal_surat->translatedFormat('d F Y') }}<br>
                Admin
                <div class="signature-space"></div>
                <div class="name">{{ $document->creator->name ?? 'Admin' }}</div>
            </td>
        </tr>
    </table>
</body>
</html>