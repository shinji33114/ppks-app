<div class="mb-3" data-location-picker>
    <label class="form-label-custom">Titik Lokasi Ditemukan</label>
    <p class="text-muted small mb-2">Klik titik pada peta atau geser penandanya. Koordinat akan terisi otomatis; Anda juga dapat mengubahnya secara manual.</p>

    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
        <button type="button" class="btn btn-sm btn-outline-primary" data-use-gps>
            <i class="bi bi-crosshair me-1"></i>Gunakan lokasi GPS
        </button>
        <a class="small" href="#" target="_blank" rel="noopener noreferrer" data-open-google-maps hidden>
            Buka titik di Google Maps
        </a>
    </div>
    <div class="border rounded-2 overflow-hidden" style="height: 300px;" data-location-map></div>
    <div class="text-muted small mt-2" aria-live="polite" data-location-status>
        Memuat peta Google Maps...
    </div>

    <div class="row g-2 mt-1">
        <div class="col-sm-6">
            <label for="{{ $prefix }}_latitude" class="form-label small mb-1">Latitude</label>
            <input type="number" step="0.0000001" min="-90" max="90" inputmode="decimal"
                   name="latitude" id="{{ $prefix }}_latitude"
                   value="{{ old('latitude', $latitude ?? '') }}"
                   class="form-control-custom @error('latitude') is-invalid-custom @enderror"
                   placeholder="Contoh: -6.8970" data-location-latitude>
            @error('latitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
        <div class="col-sm-6">
            <label for="{{ $prefix }}_longitude" class="form-label small mb-1">Longitude</label>
            <input type="number" step="0.0000001" min="-180" max="180" inputmode="decimal"
                   name="longitude" id="{{ $prefix }}_longitude"
                   value="{{ old('longitude', $longitude ?? '') }}"
                   class="form-control-custom @error('longitude') is-invalid-custom @enderror"
                   placeholder="Contoh: 112.0640" data-location-longitude>
            @error('longitude') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const pickers = Array.from(document.querySelectorAll('[data-location-picker]'));
            if (!pickers.length) return;

            const mapsApiKey = @json(config('services.google_maps.key'));
            const defaultCenter = { lat: -6.897, lng: 112.064 };
            const leafletCdn = 'https://unpkg.com/leaflet@1.9.4/dist/';

            function setStatus(picker, message) {
                picker.querySelector('[data-location-status]').textContent = message;
            }

            function readCoordinates(picker) {
                const latitudeValue = picker.querySelector('[data-location-latitude]').value.trim();
                const longitudeValue = picker.querySelector('[data-location-longitude]').value.trim();
                if (!latitudeValue || !longitudeValue) return null;
                const latitude = Number(latitudeValue);
                const longitude = Number(longitudeValue);
                if (!Number.isFinite(latitude) || !Number.isFinite(longitude)
                    || latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
                    return null;
                }
                return { lat: latitude, lng: longitude };
            }

            function initializeMaps() {
                if (!window.google || !window.google.maps) {
                    pickers.forEach((picker) => setStatus(picker, 'Peta Google Maps gagal dimuat. Koordinat manual dan GPS tetap dapat digunakan.'));
                    return;
                }

                pickers.forEach((picker) => {
                    const map = new google.maps.Map(picker.querySelector('[data-location-map]'), {
                        center: defaultCenter,
                        zoom: 12,
                        mapTypeControl: false,
                        streetViewControl: false,
                        fullscreenControl: true,
                    });
                    const marker = new google.maps.Marker({ map, draggable: true });
                    const latitudeInput = picker.querySelector('[data-location-latitude]');
                    const longitudeInput = picker.querySelector('[data-location-longitude]');
                    const googleMapsLink = picker.querySelector('[data-open-google-maps]');

                    function applyCoordinates(coordinates, updateInputs) {
                        if (!coordinates) {
                            marker.setMap(null);
                            googleMapsLink.hidden = true;
                            return;
                        }

                        if (updateInputs) {
                            latitudeInput.value = coordinates.lat.toFixed(7);
                            longitudeInput.value = coordinates.lng.toFixed(7);
                        }
                        marker.setMap(map);
                        marker.setPosition(coordinates);
                        map.setZoom(Math.max(map.getZoom() || 0, 16));
                        map.panTo(coordinates);
                        googleMapsLink.href = 'https://www.google.com/maps/search/?api=1&query='
                            + encodeURIComponent(coordinates.lat + ',' + coordinates.lng);
                        googleMapsLink.hidden = false;
                    }

                    picker._locationMapReady = true;
                    picker._applyLocationCoordinates = (coordinates) => applyCoordinates(coordinates, false);
                    const initialCoordinates = readCoordinates(picker);
                    if (initialCoordinates) applyCoordinates(initialCoordinates, false);

                    map.addListener('click', (event) => {
                        applyCoordinates({ lat: event.latLng.lat(), lng: event.latLng.lng() }, true);
                        setStatus(picker, 'Titik lokasi berhasil dipilih pada peta.');
                    });
                    marker.addListener('dragend', (event) => {
                        applyCoordinates({ lat: event.latLng.lat(), lng: event.latLng.lng() }, true);
                        setStatus(picker, 'Titik lokasi berhasil diperbarui.');
                    });

                    [latitudeInput, longitudeInput].forEach((input) => {
                        input.addEventListener('input', () => {
                            const coordinates = readCoordinates(picker);
                            if (coordinates) {
                                applyCoordinates(coordinates, false);
                                setStatus(picker, 'Koordinat siap disimpan.');
                            } else {
                                marker.setMap(null);
                                googleMapsLink.hidden = true;
                            }
                        });
                    });
                    setStatus(picker, 'Klik peta untuk memilih titik, atau geser penanda untuk mengubahnya.');
                });
            }

            function initializeOpenStreetMap() {
                if (!window.L) {
                    pickers.forEach((picker) => setStatus(picker, 'Peta gagal dimuat. Periksa koneksi internet, atau gunakan koordinat manual dan GPS.'));
                    return;
                }

                pickers.forEach((picker) => {
                    const map = L.map(picker.querySelector('[data-location-map]')).setView(
                        [defaultCenter.lat, defaultCenter.lng],
                        13
                    );
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                    }).addTo(map);

                    const latitudeInput = picker.querySelector('[data-location-latitude]');
                    const longitudeInput = picker.querySelector('[data-location-longitude]');
                    const googleMapsLink = picker.querySelector('[data-open-google-maps]');
                    let marker = null;

                    function applyCoordinates(coordinates, updateInputs) {
                        if (!coordinates) {
                            if (marker) {
                                marker.remove();
                                marker = null;
                            }
                            googleMapsLink.hidden = true;
                            return;
                        }

                        if (updateInputs) {
                            latitudeInput.value = coordinates.lat.toFixed(7);
                            longitudeInput.value = coordinates.lng.toFixed(7);
                        }

                        if (!marker) {
                            marker = L.marker([coordinates.lat, coordinates.lng], { draggable: true }).addTo(map);
                            marker.on('dragend', () => {
                                const position = marker.getLatLng();
                                applyCoordinates({ lat: position.lat, lng: position.lng }, true);
                                setStatus(picker, 'Titik lokasi berhasil diperbarui.');
                            });
                        } else {
                            marker.setLatLng([coordinates.lat, coordinates.lng]);
                        }

                        map.setView([coordinates.lat, coordinates.lng], Math.max(map.getZoom(), 16));
                        googleMapsLink.href = 'https://www.google.com/maps/search/?api=1&query='
                            + encodeURIComponent(coordinates.lat + ',' + coordinates.lng);
                        googleMapsLink.hidden = false;
                    }

                    picker._locationMapReady = true;
                    picker._applyLocationCoordinates = (coordinates) => applyCoordinates(coordinates, false);

                    const initialCoordinates = readCoordinates(picker);
                    if (initialCoordinates) applyCoordinates(initialCoordinates, false);

                    map.on('click', (event) => {
                        applyCoordinates({ lat: event.latlng.lat, lng: event.latlng.lng }, true);
                        setStatus(picker, 'Titik lokasi berhasil dipilih pada peta.');
                    });

                    [latitudeInput, longitudeInput].forEach((input) => {
                        input.addEventListener('input', () => {
                            const coordinates = readCoordinates(picker);
                            if (coordinates) {
                                applyCoordinates(coordinates, false);
                                setStatus(picker, 'Koordinat siap disimpan.');
                            } else {
                                applyCoordinates(null, false);
                            }
                        });
                    });

                    setStatus(picker, 'Peta OpenStreetMap siap. Klik peta untuk memilih titik atau geser penanda.');
                });
            }

            pickers.forEach((picker) => {
                picker.querySelector('[data-use-gps]').addEventListener('click', () => {
                    if (!navigator.geolocation) {
                        setStatus(picker, 'Perangkat atau browser ini tidak mendukung GPS.');
                        return;
                    }
                    setStatus(picker, 'Meminta izin lokasi GPS...');
                    navigator.geolocation.getCurrentPosition((position) => {
                        const coordinates = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        };
                        picker.querySelector('[data-location-latitude]').value = coordinates.lat.toFixed(7);
                        picker.querySelector('[data-location-longitude]').value = coordinates.lng.toFixed(7);
                        if (window.google && window.google.maps && picker._locationMapReady) {
                            picker._applyLocationCoordinates(coordinates);
                            setStatus(picker, 'Lokasi GPS berhasil diambil (akurasi sekitar '
                                + Math.round(position.coords.accuracy) + ' m). Periksa titik pada peta sebelum menyimpan.');
                        } else {
                            setStatus(picker, 'Lokasi GPS berhasil diambil (akurasi sekitar '
                                + Math.round(position.coords.accuracy) + ' m). Peta Google Maps belum tersedia.');
                        }
                    }, (error) => {
                        const messages = {
                            1: 'Izin lokasi ditolak. Aktifkan izin lokasi untuk situs ini.',
                            2: 'Lokasi tidak dapat ditentukan. Coba lagi di area dengan sinyal GPS yang lebih baik.',
                            3: 'Permintaan lokasi GPS melewati batas waktu. Silakan coba lagi.',
                        };
                        setStatus(picker, messages[error.code] || 'Lokasi GPS gagal diambil.');
                    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
                });
            });

            if (!mapsApiKey) {
                pickers.forEach((picker) => setStatus(picker, 'Memuat peta interaktif...'));
                const stylesheet = document.createElement('link');
                stylesheet.rel = 'stylesheet';
                stylesheet.href = leafletCdn + 'leaflet.css';
                document.head.appendChild(stylesheet);

                const script = document.createElement('script');
                script.src = leafletCdn + 'leaflet.js';
                script.onload = initializeOpenStreetMap;
                script.onerror = () => pickers.forEach((picker) => setStatus(picker, 'Peta gagal dimuat. Periksa koneksi internet, atau gunakan koordinat manual dan GPS.'));
                stylesheet.onload = () => document.head.appendChild(script);
                stylesheet.onerror = () => pickers.forEach((picker) => setStatus(picker, 'Tampilan peta gagal dimuat. Periksa koneksi internet, atau gunakan koordinat manual dan GPS.'));
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(mapsApiKey)
                + '&callback=initializePpksLocationMaps&loading=async&v=weekly';
            script.async = true;
            script.defer = true;
            script.onerror = () => pickers.forEach((picker) => setStatus(picker, 'Peta Google Maps gagal dimuat. Periksa API key dan koneksi internet.'));
            window.initializePpksLocationMaps = initializeMaps;
            document.head.appendChild(script);
        });
        </script>
    @endpush
@endonce
