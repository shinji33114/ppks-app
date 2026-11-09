@php
    $finalStatus = $client && in_array($client->status, [
        \App\Models\Client::STATUS_SELESAI,
        \App\Models\Client::STATUS_DIRUJUK,
    ], true);
    $steps = [
        ['Pendaftaran', 'Data layanan baru', 'bi-person-plus'],
        ['Penilaian', 'Informasi klien', 'bi-clipboard2-pulse'],
        ['Pendampingan', 'Catatan kegiatan', 'bi-heart-pulse'],
        ['Surat keluar / rujukan', 'Penutupan layanan', 'bi-file-earmark-check'],
    ];
    $stepLinks = [
        $client ? route('clients.show', $client) : null,
        $client && ! $client->is_archived && $client->status === \App\Models\Client::STATUS_DRAFT
            ? route('assessment.create', $client)
            : ($client && ! $client->is_archived && $client->status === \App\Models\Client::STATUS_ASSESSMENT ? route('assessment.edit', $client) : null),
        $client && ! $client->is_archived && in_array($client->status, [\App\Models\Client::STATUS_ASSESSMENT, \App\Models\Client::STATUS_PENANGANAN], true)
            ? route('penanganan.index', $client)
            : null,
        $client && ! $client->is_archived && in_array($client->status, [\App\Models\Client::STATUS_ASSESSMENT, \App\Models\Client::STATUS_PENANGANAN], true)
            ? route('penanganan.index', $client) . '#exit-document-form'
            : null,
    ];
    $progress = $finalStatus ? 100 : min(100, ($currentStep / count($steps)) * 100);
@endphp

@push('styles')
<style>
    .stepper-card {
        overflow: hidden;
        border: 1px solid #e3eee7;
        border-radius: 1rem;
        background: linear-gradient(135deg, #fff 0%, #f7fbf8 100%);
        box-shadow: 0 10px 28px rgb(22 61 39 / 6%);
    }
    .stepper-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .stepper-kicker {
        display: block;
        margin-bottom: .2rem;
        color: #198754;
        font-size: .68rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .stepper-title { margin: 0; color: #183629; font-size: 1rem; font-weight: 750; }
    .stepper-count {
        flex: 0 0 auto;
        padding: .4rem .7rem;
        border: 1px solid #dcebe1;
        border-radius: 2rem;
        background: #fff;
        color: #34704e;
        font-size: .75rem;
        font-weight: 700;
    }
    .stepper-progress {
        height: 5px;
        overflow: hidden;
        margin-bottom: 1.35rem;
        border-radius: 5px;
        background: #e7eee9;
    }
    .stepper-progress-value {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #198754, #55bd7b);
        transition: width .35s ease;
    }
    .stepper-ppks { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .65rem; }
    .stepper-ppks .step {
        position: relative;
        min-width: 0;
        padding: .75rem .65rem;
        border: 1px solid transparent;
        border-radius: .8rem;
        text-align: center;
        transition: background .2s ease, border-color .2s ease, transform .2s ease;
    }
    .stepper-ppks .step.current {
        transform: translateY(-2px);
        border-color: #d6ebdd;
        background: #eef8f1;
        box-shadow: 0 5px 14px rgb(25 135 84 / 8%);
    }
    .stepper-ppks .step-dot {
        display: flex;
        width: 42px;
        height: 42px;
        align-items: center;
        justify-content: center;
        margin: 0 auto .65rem;
        border: 1px solid #e0e8e2;
        border-radius: 50%;
        background: #fff;
        color: #829087;
        font-size: 1rem;
        font-weight: 750;
        box-shadow: 0 3px 8px rgb(24 54 41 / 5%);
    }
    .stepper-ppks .step.done .step-dot {
        border-color: #198754;
        background: #198754;
        color: #fff;
    }
    .stepper-ppks .step.current .step-dot {
        border: 3px solid #ccebd6;
        background: #125c37;
        color: #fff;
        box-shadow: 0 0 0 3px #e1f3e7;
    }
    .stepper-ppks .step-label {
        display: block;
        color: #65756b;
        font-size: .8rem;
        font-weight: 700;
        line-height: 1.3;
        text-decoration: none;
    }
    .stepper-ppks .step.done .step-label { color: #34704e; }
    .stepper-ppks .step.current .step-label { color: #125c37; }
    .stepper-ppks .step-sub { margin-top: .25rem; color: #8a9790; font-size: .68rem; line-height: 1.35; }
    .stepper-ppks a.step-label:hover { color: #198754; }
    @media (max-width: 575.98px) {
        .stepper-card { padding: 1rem !important; }
        .stepper-heading { align-items: flex-start; margin-bottom: .9rem; }
        .stepper-title { font-size: .92rem; }
        .stepper-count { padding: .35rem .55rem; font-size: .68rem; }
        .stepper-ppks { gap: .15rem; }
        .stepper-ppks .step { padding: .55rem .2rem; }
        .stepper-ppks .step-dot { width: 34px; height: 34px; margin-bottom: .45rem; font-size: .85rem; }
        .stepper-ppks .step-label { font-size: .67rem; }
        .stepper-ppks .step-sub { display: none; }
    }
</style>
@endpush

<section class="stepper-card p-4 mb-4" aria-label="Progres layanan">
    <div class="stepper-heading">
        <div>
            <span class="stepper-kicker">Alur layanan</span>
            <h2 class="stepper-title">{{ $finalStatus ? 'Layanan telah selesai' : 'Ikuti tahapan layanan secara berurutan' }}</h2>
        </div>
        <span class="stepper-count">{{ $finalStatus ? 'Selesai' : 'Tahap ' . $currentStep . ' dari ' . count($steps) }}</span>
    </div>
    <div class="stepper-progress" role="progressbar" aria-label="Progres layanan" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ (int) $progress }}">
        <div class="stepper-progress-value" style="width: {{ $progress }}%"></div>
    </div>
    <div class="stepper-ppks" role="list">
        @foreach ($steps as $index => $step)
            @php
                $number = $index + 1;
                $done = $finalStatus || $number < $currentStep;
                $current = ! $finalStatus && $number === $currentStep;
                $state = $done ? 'done' : ($current ? 'current' : 'pending');
            @endphp
            <div class="step {{ $state }}" role="listitem">
                <div class="step-dot">
                    @if ($done)
                        <i class="bi bi-check-lg"></i>
                    @else
                        <i class="bi {{ $step[2] }}" aria-hidden="true"></i>
                    @endif
                </div>
                @if ($stepLinks[$index])
                    <a class="step-label" href="{{ $stepLinks[$index] }}" @if ($current) aria-current="step" @endif>{{ $step[0] }}</a>
                @else
                    <div class="step-label">{{ $step[0] }}</div>
                @endif
                <div class="step-sub">{{ $step[1] }}</div>
            </div>
        @endforeach
    </div>
</section>
