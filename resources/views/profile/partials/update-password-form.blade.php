<section>
    <header class="d-flex align-items-start gap-3 mb-4">
        <span class="account-section-icon"><i class="bi bi-shield-lock"></i></span>
        <div>
            <h2 class="h5 fw-bold mb-1">Ubah Kata Sandi</h2>
            <p class="text-muted small mb-0">Buat kata sandi yang sulit ditebak dan tidak sama dengan kata sandi akun lain.</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label fw-semibold">Kata sandi saat ini</label>
            <input id="update_password_current_password" name="current_password" type="password"
                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                autocomplete="current-password" required>
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label for="update_password_password" class="form-label fw-semibold">Kata sandi baru</label>
                <input id="update_password_password" name="password" type="password"
                    class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                    autocomplete="new-password" required>
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6">
                <label for="update_password_password_confirmation" class="form-label fw-semibold">Ulangi kata sandi baru</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                    class="form-control" autocomplete="new-password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success btn-account">
            <i class="bi bi-key me-1"></i> Simpan kata sandi baru
        </button>
    </form>
</section>
