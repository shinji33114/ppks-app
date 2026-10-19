<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $document->type === 'rujukan' ? 'Surat Rujukan' : 'Surat Keluar' }} - {{ $document->client->nama_lengkap }}</title>
    <style>
        @page { size: A4; margin: 20mm 22mm; }
        body { color: #111; font-family: Arial, sans-serif; font-size: 12pt; line-height: 1.5; margin: 0; }
        .toolbar { display: flex; gap: 8px; margin: 0 auto 20px; max-width: 780px; }
        .toolbar button, .toolbar a { border: 0; border-radius: 4px; color: #fff; cursor: pointer; padding: 8px 14px; text-decoration: none; }
        .toolbar button { background: #0d6efd; }
        .toolbar a { background: #6c757d; }
        .letter { margin: 0 auto; max-width: 780px; }
        .letterhead { border-bottom: 3px double #111; padding-bottom: 10px; text-align: center; }
        .letterhead h1, .letterhead h2, .letterhead p { margin: 0; }
        .letterhead h1 { font-size: 16pt; }
        .letterhead h2 { font-size: 14pt; }
        .letterhead p { font-size: 10pt; }
        .letter-title { font-weight: bold; margin: 28px 0 20px; text-align: center; text-decoration: underline; }
        .meta { margin-bottom: 24px; }
        .meta td { padding: 2px 10px 2px 0; vertical-align: top; }
        .content { text-align: justify; }
        .identity { border-collapse: collapse; margin: 16px 0; width: 100%; }
        .identity td { border: 1px solid #555; padding: 7px 9px; vertical-align: top; }
        .identity td:first-child { width: 34%; }
        .signature { float: right; margin-top: 42px; text-align: center; width: 230px; }
        .signature-space { height: 76px; }
        .signature strong { text-decoration: underline; }
        @media print {
            .toolbar { display: none; }
            .letter { max-width: none; }
        }
    </style>
</head>

<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak Surat</button>
        <a href="{{ route('exit-documents.show', $document) }}">Kembali</a>
    </div>

    <main class="letter">
        <header class="letterhead">
            <h1>PEMERINTAH DAERAH</h1>
            <h2>RUMAH PERLINDUNGAN SOSIAL PPKS</h2>
            <p>Sistem Pelayanan Penanganan Pemerlu Pelayanan Kesejahteraan Sosial</p>
            <p>Alamat instansi dapat disesuaikan dengan kop surat resmi</p>
        </header>

        <h3 class="letter-title">
            {{ $document->type === 'rujukan' ? 'SURAT RUJUKAN' : 'SURAT KELUAR / PEMULANGAN' }}
        </h3>

        <table class="meta">
            <tr><td>Nomor</td><td>: {{ $document->nomor_surat ?: '-' }}</td></tr>
            <tr><td>Tanggal</td><td>: {{ $document->tanggal_surat->format('d-m-Y') }}</td></tr>
            <tr><td>Tujuan</td><td>: {{ $document->tujuan }}</td></tr>
        </table>

        <div class="content">
            <p>Dengan hormat,</p>
            <p>
                Dengan ini menerangkan bahwa klien berikut telah mendapatkan pelayanan di
                Rumah Perlindungan Sosial PPKS dan telah diproses untuk
                {{ $document->type === 'rujukan' ? 'dirujuk ke instansi tujuan' : 'keluar atau kembali kepada keluarga' }}.
            </p>

            <table class="identity">
                <tr><td>Nama Lengkap</td><td>{{ $document->client->nama_lengkap }}</td></tr>
                <tr><td>Jenis Kelamin</td><td>{{ $document->client->assessment?->jenis_kelamin ?: ($document->client->jenis_kelamin ?: '-') }}</td></tr>
                <tr><td>NIK</td><td>{{ $document->client->assessment?->nik ?: '-' }}</td></tr>
                <tr><td>Alamat</td><td>{{ $document->client->assessment?->alamat_domisili ?: ($document->client->alamat ?: '-') }}</td></tr>
                <tr><td>Tanggal Masuk</td><td>{{ $document->client->tanggal_masuk->format('d-m-Y') }}</td></tr>
                <tr><td>Tanggal Keluar</td><td>{{ $document->tanggal_keluar->format('d-m-Y') }}</td></tr>
                <tr><td>Penanggung Jawab</td><td>{{ $document->no_hp_penanggung_jawab }}</td></tr>
            </table>

            @if ($document->keterangan)
                <p>{{ $document->keterangan }}</p>
            @endif

            <p>
                Demikian surat ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
            </p>
        </div>

        <div class="signature">
            <div>{{ $document->tanggal_surat->translatedFormat('d F Y') }}</div>
            <div>Admin</div>
            <div class="signature-space"></div>
            <strong>{{ $document->creator?->name ?: 'Admin' }}</strong>
        </div>
    </main>
</body>

</html>
