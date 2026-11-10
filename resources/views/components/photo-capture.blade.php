<div class="mb-4" data-photo-capture>
    <label for="{{ $prefix }}_file" class="form-label-custom">
        Foto Penerima Layanan
        @if ($required ?? false)<span class="text-danger">*</span>@endif
    </label>
    @if (! empty($existingPhoto))
        <div class="text-muted small mb-2">Foto saat ini sudah tersimpan. Pilih file atau ambil foto baru hanya jika ingin menggantinya.</div>
    @endif
    <input type="file" name="foto_klien" id="{{ $prefix }}_file" accept="image/*"
           class="form-control-custom @error('foto_klien') is-invalid-custom @enderror"
           @required($required ?? false) data-photo-file>
    @error('foto_klien')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror

    <div class="d-flex flex-wrap gap-2 mt-2">
        <button type="button" class="btn btn-sm btn-outline-primary" data-camera-start>
            <i class="bi bi-camera me-1"></i>Ambil foto sekarang
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-camera-stop hidden>
            Tutup kamera
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-camera-capture hidden>
            <i class="bi bi-camera-fill me-1"></i>Jepret foto
        </button>
    </div>
    <div class="mt-2" data-camera-container hidden>
        <label for="{{ $prefix }}_camera" class="form-label small">Pilih kamera</label>
        <select id="{{ $prefix }}_camera" class="form-select form-select-sm mb-2" data-camera-select hidden></select>
        <video class="w-100 rounded border bg-dark" style="max-height: 360px; transform: none;" autoplay playsinline data-camera-video></video>
        <div class="form-check mt-2" data-camera-correction-container hidden>
            <input class="form-check-input" type="checkbox" id="{{ $prefix }}_correct_mirror" data-camera-correction>
            <label class="form-check-label small" for="{{ $prefix }}_correct_mirror">
                Koreksi kamera depan yang tampil terbalik
            </label>
        </div>
        <img class="img-fluid rounded border mt-2" style="transform: none;" alt="Pratinjau foto yang diambil" data-camera-preview hidden>
    </div>
    <div class="text-muted small mt-2" aria-live="polite" data-camera-status></div>
</div>

@once
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-photo-capture]').forEach((container) => {
                const fileInput = container.querySelector('[data-photo-file]');
                const startButton = container.querySelector('[data-camera-start]');
                const stopButton = container.querySelector('[data-camera-stop]');
                const captureButton = container.querySelector('[data-camera-capture]');
                const cameraContainer = container.querySelector('[data-camera-container]');
                const cameraSelect = container.querySelector('[data-camera-select]');
                const video = container.querySelector('[data-camera-video]');
                const correctionContainer = container.querySelector('[data-camera-correction-container]');
                const correctionCheckbox = container.querySelector('[data-camera-correction]');
                const preview = container.querySelector('[data-camera-preview]');
                const status = container.querySelector('[data-camera-status]');
                let stream = null;
                let selectedCameraId = '';
                let previewUrl = '';
                let unmirrorCamera = false;

                function stopCamera() {
                    if (stream) {
                        stream.getTracks().forEach((track) => track.stop());
                        stream = null;
                    }
                    video.srcObject = null;
                    stopButton.hidden = true;
                    captureButton.hidden = true;
                }

                async function startCamera(deviceId) {
                    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                        status.textContent = 'Kamera tidak didukung browser ini. Silakan pilih foto dari perangkat.';
                        return;
                    }

                    stopCamera();
                    status.textContent = 'Meminta izin untuk membuka kamera...';
                    cameraContainer.hidden = false;
                    preview.hidden = true;
                    video.hidden = false;

                    try {
                        stream = await navigator.mediaDevices.getUserMedia({
                            audio: false,
                            video: deviceId
                                ? { deviceId: { exact: deviceId } }
                                : { facingMode: { ideal: 'environment' } },
                        });
                        video.srcObject = stream;
                        await video.play();
                        const track = stream.getVideoTracks()[0];
                        unmirrorCamera = track?.getSettings().facingMode === 'user';
                        correctionCheckbox.checked = unmirrorCamera;
                        correctionContainer.hidden = false;
                        video.style.transform = unmirrorCamera ? 'scaleX(-1)' : 'none';

                        const devices = await navigator.mediaDevices.enumerateDevices();
                        const cameras = devices.filter((device) => device.kind === 'videoinput');
                        cameraSelect.replaceChildren();
                        cameras.forEach((camera, index) => {
                            const option = document.createElement('option');
                            option.value = camera.deviceId;
                            option.textContent = camera.label || 'Kamera ' + (index + 1);
                            cameraSelect.appendChild(option);
                        });
                        cameraSelect.hidden = cameras.length < 2;
                        if (stream.getVideoTracks()[0]) {
                            selectedCameraId = stream.getVideoTracks()[0].getSettings().deviceId || deviceId || '';
                            cameraSelect.value = selectedCameraId;
                        }

                        stopButton.hidden = false;
                        captureButton.hidden = false;
                        status.textContent = cameras.length > 1
                            ? 'Kamera siap. Pilih kamera yang ingin digunakan, lalu jepret foto. Jika tampil terbalik, gunakan opsi koreksi.'
                            : 'Kamera siap. Posisikan penerima layanan, lalu jepret foto. Jika tampil terbalik, gunakan opsi koreksi.';
                    } catch (error) {
                        stopCamera();
                        cameraContainer.hidden = true;
                        const messages = {
                            NotAllowedError: 'Izin kamera ditolak. Aktifkan izin kamera untuk situs ini.',
                            NotFoundError: 'Kamera tidak ditemukan pada perangkat ini.',
                            NotReadableError: 'Kamera sedang digunakan aplikasi lain.',
                            SecurityError: 'Akses kamera memerlukan HTTPS atau localhost.',
                        };
                        status.textContent = messages[error.name] || 'Kamera gagal dibuka. Silakan pilih foto dari perangkat.';
                    }
                }

                startButton.addEventListener('click', () => startCamera(selectedCameraId));
                stopButton.addEventListener('click', () => {
                    stopCamera();
                    cameraContainer.hidden = true;
                    status.textContent = 'Kamera ditutup.';
                });
                cameraSelect.addEventListener('change', () => {
                    selectedCameraId = cameraSelect.value;
                    startCamera(selectedCameraId);
                });
                correctionCheckbox.addEventListener('change', () => {
                    unmirrorCamera = correctionCheckbox.checked;
                    video.style.transform = unmirrorCamera ? 'scaleX(-1)' : 'none';
                });
                captureButton.addEventListener('click', () => {
                    if (!video.videoWidth || !video.videoHeight) {
                        status.textContent = 'Kamera belum siap. Tunggu sebentar lalu coba lagi.';
                        return;
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    const context = canvas.getContext('2d');
                    if (!context) {
                        status.textContent = 'Foto gagal diproses pada browser ini.';
                        return;
                    }
                    context.setTransform(1, 0, 0, 1, 0, 0);
                    if (unmirrorCamera) {
                        context.translate(canvas.width, 0);
                        context.scale(-1, 1);
                    }
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            status.textContent = 'Foto gagal diambil. Silakan coba lagi.';
                            return;
                        }

                        const file = new File([blob], 'foto-penerima-' + Date.now() + '.jpg', { type: 'image/jpeg' });
                        const transfer = new DataTransfer();
                        transfer.items.add(file);
                        fileInput.files = transfer.files;
                        if (previewUrl) URL.revokeObjectURL(previewUrl);
                        previewUrl = URL.createObjectURL(file);
                        preview.src = previewUrl;
                        preview.hidden = false;
                        stopCamera();
                        video.hidden = true;
                        cameraContainer.hidden = false;
                        status.textContent = 'Foto berhasil diambil dan siap disimpan. Ambil foto lagi atau pilih file jika ingin menggantinya.';
                    }, 'image/jpeg', 0.9);
                });
                fileInput.addEventListener('change', () => {
                    if (fileInput.files.length) {
                        stopCamera();
                        if (previewUrl) URL.revokeObjectURL(previewUrl);
                        previewUrl = URL.createObjectURL(fileInput.files[0]);
                        preview.src = previewUrl;
                        preview.hidden = false;
                        video.hidden = true;
                        cameraContainer.hidden = false;
                        status.textContent = 'Foto siap disimpan.';
                    }
                });
                container.closest('form').addEventListener('submit', () => {
                    stopCamera();
                    if (previewUrl) URL.revokeObjectURL(previewUrl);
                });
            });
        });
        </script>
    @endpush
@endonce
