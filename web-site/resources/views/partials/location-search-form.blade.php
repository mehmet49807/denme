@php
    $country = $country ?? '';
    $city = $city ?? '';
    $district = $district ?? '';
    $showDistrict = $showDistrict ?? true;
    $countryMeta = $countryMeta ?? app(\App\Services\CountryMetaService::class);
    $initialIso = $country !== '' ? $countryMeta->isoForCountry($country) : 'tr';
@endphp

<form method="GET" action="{{ route('locations.find') }}" class="location-search-form">
    <div class="location-search-form-grid" data-location-picker
         data-country="{{ $country }}"
         data-city="{{ $city }}"
         data-district="{{ $district }}"
         data-show-district="{{ $showDistrict ? '1' : '0' }}">
        <div class="location-search-field">
            <label for="location-search-country">Ülke</label>
            <div class="flagged-select-wrap flagged-select-wrap--country">
                <span class="flagged-select-flag" data-flagged-select-flag aria-hidden="true">
                    <img src="{{ $countryMeta->flagUrl($initialIso) }}" alt="" width="20" height="15" loading="lazy">
                </span>
                <select id="location-search-country" name="country" class="loc-country flagged-select" required aria-label="Ülke">
                    <option value="">Ülke seçin</option>
                </select>
            </div>
        </div>

        <div class="location-search-field">
            <label for="location-search-city">Şehir</label>
            <select id="location-search-city" name="city" class="loc-city" required disabled aria-label="Şehir">
                <option value="">Şehir seçin</option>
            </select>
        </div>

        @if($showDistrict)
        <div class="location-search-field loc-district-wrap" hidden>
            <label for="location-search-district">İlçe</label>
            <select id="location-search-district" name="district" class="loc-district" aria-label="İlçe">
                <option value="">İlçe seçin (isteğe bağlı)</option>
            </select>
        </div>
        @endif
    </div>

    <div class="location-search-actions">
        <button type="submit" class="btn btn-primary">Üyeleri Ara</button>
        @if($country !== '' || $city !== '' || $district !== '')
            <a href="{{ route('locations.search') }}" class="btn btn-outline">Temizle</a>
        @endif
    </div>
</form>

@error('location') <small class="form-error">{{ $message }}</small> @enderror
