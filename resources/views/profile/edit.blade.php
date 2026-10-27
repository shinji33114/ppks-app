@extends('layouts.spark')

@section('page-title', 'Akun Saya')
@section('page-subtitle', 'Atur informasi dan keamanan akun Anda')

@push('styles')
    <style>
        .account-page .account-hero {
            position: relative;
            overflow: hidden;
            border: 0;
            border-radius: 1.25rem;
            background: linear-gradient(120deg, #14532d 0%, #198754 58%, #42a879 100%);
            color: #fff;
        }

        .account-page .account-hero::after {
            position: absolute;
            top: -5rem;
            right: -2rem;
            width: 17rem;
            height: 17rem;
            border: 1px solid rgb(255 255 255 / 18%);
            border-radius: 50%;
            content: "";
        }

        .account-page .account-avatar {
            display: inline-flex;
            width: 5rem;
            height: 5rem;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border: 3px solid rgb(255 255 255 / 55%);
            border-radius: 50%;
            background: rgb(255 255 255 / 18%);
            color: #fff;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .account-page .account-hero-content {
            position: relative;
            z-index: 1;
        }

        .account-page .account-section-card {
            border: 1px solid #e9eeec;
            border-radius: 1rem;
            box-shadow: 0 8px 24px rgb(22 53 38 / 5%);
        }

        .account-page .account-section-icon {
            display: inline-flex;
            width: 2.6rem;
            height: 2.6rem;
            align-items: center;
            justify-content: center;
            border-radius: .8rem;
            background: #eaf5ee;
            color: #198754;
            font-size: 1.15rem;
        }

        .account-page .account-section-icon.is-danger {
            background: #fff0f0;
            color: #dc3545;
        }

        .account-page .account-meta-row + .account-meta-row {
            border-top: 1px solid #edf0ee;
        }

        .account-page .form-control {
            min-height: 2.9rem;
            border-color: #dfe6e2;
            border-radius: .7rem;
        }

        .account-page .form-control:focus {
            border-color: #55a878;
            box-shadow: 0 0 0 .2rem rgb(25 135 84 / 12%);
        }

        .account-page .btn-account {
            min-height: 2.75rem;
            padding-inline: 1.15rem;
            border-radius: .7rem;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    @php
        $initials = collect(preg_split('/\s+/', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    @endphp

    <div class="account-page">
        @if (session('status') === 'profile-updated')
            <div class="alert alert-success d-flex align-items-center gap-2 mb-4" role="status">
                <i class="bi bi-check-circle-fill"></i>
                <span>Informasi akun berhasil diperbarui.</span>
            </div>
        @endif

        <section class="account-hero shadow-sm mb-4">
            <div class="account-hero-content d-flex flex-column flex-sm-row align-items-sm-center gap-3 p-4 p-lg-5">
                <div class="account-avatar" aria-hidden="true">{{ $initials ?: 'A' }}</div>
                <div class="flex-grow-1">
                    <div class="small text-white-50 mb-1">Akun Anda</div>
                    <h2 class="h3 fw-bold mb-1">{{ $user->name }}</h2>
                    <div class="text-white-50">{{ $user->email }}</div>
                </div>
                <span class="badge rounded-pill bg-white text-success px-3 py-2 align-self-start align-self-sm-center">
                    <i class="bi bi-shield-check me-1"></i> {{ $user->getRoleNames()->map(fn ($role) => $role === 'super-admin' ? 'Pengelola utama' : 'Petugas')->join(', ') ?: 'Petugas' }}
                </span>
            </div>
        </section>

        <div class="row g-4 align-items-start">
            <div class="col-xl-8">
                <div class="card account-section-card mb-4">
                    <div class="card-body p-4 p-lg-5">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="card account-section-card mb-4">
                    <div class="card-body p-4 p-lg-5">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="card account-section-card border-danger-subtle">
                    <div class="card-body p-4 p-lg-5">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>

            <aside class="col-xl-4">
                <div class="card account-section-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <span class="account-section-icon"><i class="bi bi-person-vcard"></i></span>
                            <div>
                                <h3 class="h6 fw-bold mb-1">Informasi akun</h3>
                                <div class="text-muted small">Ringkasan akun Anda</div>
                            </div>
                        </div>

                        <div class="account-meta-row py-3">
                            <div class="text-muted small mb-1">Status akun</div>
                            <span class="badge rounded-pill {{ $user->is_active ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                <i class="bi {{ $user->is_active ? 'bi-check-circle-fill' : 'bi-pause-circle-fill' }} me-1"></i>
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="account-meta-row py-3">
                            <div class="text-muted small mb-1">Email</div>
                            <div class="fw-medium text-break">{{ $user->email }}</div>
                            @if ($user->hasVerifiedEmail())
                                <div class="small text-success mt-1"><i class="bi bi-patch-check-fill me-1"></i>Email terverifikasi</div>
                            @else
                                <div class="small text-warning-emphasis mt-1"><i class="bi bi-exclamation-circle-fill me-1"></i>Email belum terverifikasi</div>
                            @endif
                        </div>

                        <div class="account-meta-row py-3">
                            <div class="text-muted small mb-1">Akun dibuat pada</div>
                            <div class="fw-medium">{{ $user->created_at?->translatedFormat('d F Y') ?? '—' }}</div>
                        </div>

                        <div class="rounded-3 bg-light p-3 mt-2">
                            <div class="d-flex align-items-start gap-2">
                                <i class="bi bi-lock-fill text-success mt-1"></i>
                                <p class="small text-muted mb-0">
                                    Rahasiakan kata sandi Anda. Jangan berikan kata sandi kepada orang lain.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>
@endsection
