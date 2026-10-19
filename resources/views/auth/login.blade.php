<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Sistem PPKS</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}">

    <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('spark-admin/assets/css/main.css') }}">
    <style>
   
    .login-wrapper { position: relative; overflow: hidden; }

    .login-bg-photo {
    position: absolute; inset: 0; z-index: 0;
    background: #0A1E14 url('{{ asset('images/login-bg-alun.jpg') }}') center / cover no-repeat;
    filter: brightness(1.05) saturate(1.1);
}

    .login-bg-overlay {
        position: absolute; inset: 0; z-index: 1;
        /* tengah terang, tepi gelap (vinyet) */
        background: radial-gradient(ellipse at center,
                    rgba(5, 28, 18, 0.03) 0%, rgba(5, 28, 18, 0.34) 100%);
    }

    /* ===== 2. Kotak login: kaca hijau tembus pandang ===== */
    .login-card {
        position: relative; z-index: 2;
        padding: 2rem 2.5rem;
        background: rgba(7, 47, 31, 0.42);
        -webkit-backdrop-filter: blur(18px) saturate(140%);
        backdrop-filter: blur(18px) saturate(140%);
        border: 1px solid rgba(255, 255, 255, 0.18);
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }
    .login-card:hover {                            /* matikan efek "terangkat" bawaan tema */
        transform: none;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.15);
    }
    @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
        .login-card { background: rgba(7, 47, 31, 0.9); }   /* browser lama tanpa blur */
    }

    /* ===== 3. Teks ===== */
    .login-brand { color: #fff; }
    .login-brand:hover { color: var(--brand-lime); }
    .login-brand img { filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.45)); }
    .login-subtitle { color: rgba(255, 255, 255, 0.78); margin-bottom: 1.5rem; }
    .login-form-label { color: #fff; }
    .custom-control-label,
    .login-footer-text { color: rgba(255, 255, 255, 0.72); }

    /* ===== 4. Kolom input ===== */
    .login-input {
        background-color: rgba(255, 255, 255, 0.10);
        border-color: rgba(255, 255, 255, 0.22);
        color: #fff;
    }
    .login-input::placeholder { color: rgba(255, 255, 255, 0.6); }
    .login-input:focus {
        background-color: rgba(255, 255, 255, 0.16);
        border-color: var(--brand-lime);
        box-shadow: 0 0 0 4px rgba(180, 241, 5, 0.18);
    }
    /* isi otomatis Chrome biasanya memutihkan kolom: tunda perubahannya */
    .login-input:-webkit-autofill {
        -webkit-text-fill-color: #fff;
        caret-color: #fff;
        transition: background-color 9999s ease-in-out 0s;
    }
    .login-input-group i.input-icon,
    .password-toggle-btn { color: rgba(255, 255, 255, 0.7); }
    .password-toggle-btn:hover { color: var(--brand-lime); }

    /* ===== 5. Centang "Ingat saya" dan tombol ===== */
    .custom-checkbox-input { border-color: rgba(255, 255, 255, 0.6); }
    .custom-checkbox-input:checked { background-color: var(--brand-lime); border-color: var(--brand-lime); }
    .custom-checkbox-input:checked::before { color: var(--brand-forest-dark); }

    .btn-login {
        background-color: var(--brand-lime);
        color: var(--brand-forest-dark);
        box-shadow: 0 8px 20px rgba(180, 241, 5, 0.22);
    }
    .btn-login:hover {
        background-color: var(--brand-lime-hover);
        color: var(--brand-forest-dark);
        box-shadow: 0 10px 26px rgba(180, 241, 5, 0.32);
    }

        /* ===== 6. Kotak lebih ringkas ===== */
    .login-card { max-width: 400px; padding: 1.75rem 2rem; }          /* sebelumnya 450px */
    .login-brand { font-size: 1.35rem; margin-bottom: 1rem; }
    .login-subtitle { font-size: 0.8rem; margin-bottom: 1.25rem; }
    .login-form-group { margin-bottom: 1rem; }
    .login-input { padding-top: 0.65rem; padding-bottom: 0.65rem; font-size: 0.85rem; }
    .login-options { margin-bottom: 1.25rem; }
    .btn-login { padding: 0.7rem; font-size: 0.9rem; }
    .login-footer-text { font-size: 0.8rem; }

    /* ===== 7. Copyright ===== */
    .login-copyright {
        color: rgba(255, 255, 255, 0.45);
        font-size: 0.75rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 0.85rem;
    }
    .login-copyright .small-text {
        font-size: 0.65rem;
        color: rgba(255, 255, 255, 0.35);
        margin-top: 2px;
    }


</style>
</head>
<body>

    <div class="login-wrapper">
    <div class="login-bg-photo"></div>
<div class="login-bg-overlay"></div>

    <div class="login-card">

            <a href="{{ url('/') }}" class="login-brand text-decoration-none d-flex flex-column align-items-center text-center">
    <img src="{{ asset('images/logo-tuban.png') }}" alt="Lambang Kabupaten Tuban" style="height: 60px; margin-bottom: 6px;">
    <span>Sistem PPKS</span>
</a>

<p class="login-subtitle text-center">
    Dinas Sosial Kabupaten Tuban<br>
    Sistem Pelayanan Penanganan Pemerlu Pelayanan Kesejahteraan Sosial
</p>

            {{-- Pesan status dari Laravel (misalnya setelah reset password) --}}
            @if (session('status'))
                <div class="alert alert-success py-2 small">{{ session('status') }}</div>
            @endif

            {{-- Pesan error login (email/password salah, atau akun nonaktif) --}}
            @if ($errors->any())
                <div class="alert alert-danger py-2 small">
                    {{ $errors->first('email') ?: $errors->first('password') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm" class="needs-validation" novalidate>
                @csrf

                <div class="login-form-group">
                    <label for="email" class="login-form-label">Email</label>
                    <div class="login-input-group">
                        <i class="bi bi-envelope input-icon"></i>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="login-input" placeholder="nama@instansi.go.id"
                               required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="login-form-group">
                    <label for="password" class="login-form-label">Kata Sandi</label>
                    <div class="login-input-group">
                        <i class="bi bi-shield-lock input-icon"></i>
                        <input type="password" id="password" name="password"
                               class="login-input login-input-password" placeholder="••••••••"
                               required autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" id="toggle-password"
                                aria-label="Tampilkan kata sandi">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="login-options">
                    <label class="custom-control-label">
                        <input type="checkbox" class="custom-checkbox-input" id="rememberMe" name="remember">
                        <span>Ingat saya</span>
                    </label>
                </div>

                <button type="submit" class="btn-login" id="btn-submit">
                    <span>Masuk ke Sistem</span>
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>

                   <p class="login-footer-text">
                Belum memiliki akun? Hubungi Super Admin untuk dibuatkan akun.
            </p>

            <div class="login-copyright text-center mt-3">
                &copy; {{ date('Y') }} Dinas Kominfo Kabupaten Tuban<br>
            </div>

        </div>   {{-- penutup .login-card --}}
    </div>       {{-- penutup .login-wrapper --}}

    <script src="{{ asset('spark-admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('spark-admin/assets/js/auth.js') }}"></script>
</body>
</html>