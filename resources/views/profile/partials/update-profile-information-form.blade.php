<section>
    <header class="d-flex align-items-start gap-3 mb-4">
        <span class="account-section-icon"><i class="bi bi-person-gear"></i></span>
        <div>
            <h2 class="h5 fw-bold mb-1">Data Akun</h2>
            <p class="text-muted small mb-0">Ubah nama atau alamat email akun Anda.</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nama lengkap</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="form-label fw-semibold">Alamat email</label>
            <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="alert alert-warning d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mt-3 mb-0">
                    <span class="small">Alamat email Anda belum diperiksa.</span>
                    <button form="send-verification" class="btn btn-sm btn-outline-warning flex-shrink-0">
                        Kirim ulang email pemeriksaan
                    </button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="small text-success mt-2 mb-0">Email pemeriksaan baru sudah dikirim.</p>
                @endif
            @endif
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3">
            <button type="submit" class="btn btn-success btn-account">
                <i class="bi bi-check2-circle me-1"></i> Simpan perubahan
            </button>
        </div>
    </form>
</section>
