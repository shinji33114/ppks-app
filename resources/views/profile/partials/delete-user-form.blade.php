<section>
    <header class="d-flex align-items-start gap-3 mb-4">
        <span class="account-section-icon is-danger"><i class="bi bi-person-x"></i></span>
        <div>
            <h2 class="h5 fw-bold text-danger mb-1">Hapus akun</h2>
            <p class="text-muted small mb-0">
                Setelah akun dihapus, Anda tidak dapat masuk lagi ke sistem.
            </p>
        </div>
    </header>

    <button type="button" class="btn btn-outline-danger btn-account"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
        <i class="bi bi-trash3 me-1"></i> Hapus akun saya
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-4 p-md-5">
            @csrf
            @method('delete')

            <div class="d-flex align-items-center gap-3 mb-3">
                <span class="account-section-icon is-danger"><i class="bi bi-exclamation-triangle"></i></span>
                <h2 class="h5 fw-bold mb-0">Konfirmasi penghapusan akun</h2>
            </div>

            <p class="text-muted">
                Untuk melanjutkan, masukkan kata sandi Anda. Akun yang sudah dihapus tidak dapat dipulihkan.
            </p>

            <div class="mb-4">
                <label for="delete_account_password" class="form-label fw-semibold">Kata sandi</label>
                <input id="delete_account_password" name="password" type="password"
                    class="form-control @error('password', 'userDeletion') is-invalid @enderror"
                    autocomplete="current-password" required>
                @error('password', 'userDeletion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-end gap-2">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Batal
                </x-secondary-button>
                <button type="submit" class="btn btn-danger">
                    Hapus akun secara permanen
                </button>
            </div>
        </form>
    </x-modal>
</section>
