<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Sistem PPKS</title>

    <link rel="icon" type="image/png" href="{{ asset('spark-admin/assets/images/favicon.ico') }}">

    <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('spark-admin/assets/libs/bootstrap-icons/bootstrap-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('spark-admin/assets/css/main.css') }}">
</head>
<body>

    <div class="login-wrapper">
        <div class="login-bg-shape login-bg-shape-1"></div>
        <div class="login-bg-shape login-bg-shape-2"></div>

        <div class="login-card">

            <a href="{{ url('/') }}" class="login-brand text-decoration-none">
                <i class="bi bi-asterisk"></i>
                <span>Sistem PPKS</span>
            </a>

            <p class="login-subtitle">Sistem Pelayanan Penanganan Pemerlu Pelayanan Kesejahteraan Sosial. Silakan masuk untuk melanjutkan.</p>

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
                Belum memiliki akun? Hubungi Kepala RPS untuk dibuatkan akun.
            </p>

        </div>
    </div>

    <script src="{{ asset('spark-admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('spark-admin/assets/js/auth.js') }}"></script>
</body>
</html>