@php
    $currentSelection = old('kategori_ppks', $selected ?? []);
    if (! is_array($currentSelection)) {
        $currentSelection = \App\Models\Client::parseKategoriPpks($currentSelection);
    }
@endphp

@push('styles')
<style>
    .ppks-category-options { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .55rem; }
    .ppks-category-option {
        display: flex; min-height: 46px; align-items: center; gap: .6rem; padding: .65rem .75rem;
        border: 1px solid #e0e9e3; border-radius: .7rem; background: #fff; color: #43574c;
        font-size: .82rem; cursor: pointer; transition: background .16s ease, border-color .16s ease;
    }
    .ppks-category-option:has(input:checked) { border-color: #80b998; background: #eef8f1; color: #175c39; }
    .ppks-category-option input { width: 1rem; height: 1rem; flex: 0 0 auto; accent-color: #198754; }
    .ppks-category-option span { line-height: 1.35; }
    .ppks-category-option-unknown { grid-column: 1 / -1; border-style: dashed; }
    @media (max-width: 575.98px) { .ppks-category-options { grid-template-columns: 1fr; } }
</style>
@endpush

<div class="mb-3">
    <label class="form-label-custom">Jenis kebutuhan layanan (PPKS)</label>
    <div class="text-muted small mb-2">Pilih satu atau beberapa kategori yang sesuai.</div>
    <div class="ppks-category-options">
        @foreach (\App\Models\Client::KATEGORI_PPKS as $kategori)
            <label class="ppks-category-option">
                <input type="checkbox" name="kategori_ppks[]" value="{{ $kategori }}"
                       @checked(in_array($kategori, $currentSelection, true))>
                <span>{{ $kategori }}</span>
            </label>
        @endforeach
        <label class="ppks-category-option ppks-category-option-unknown">
            <input type="checkbox" name="kategori_ppks[]" value="-"
                   @checked(in_array('-', $currentSelection, true))>
            <span>Tidak diketahui</span>
        </label>
    </div>
    @error('kategori_ppks')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
    @error('kategori_ppks.*')
        <div class="text-danger small mt-2">{{ $message }}</div>
    @enderror
</div>
