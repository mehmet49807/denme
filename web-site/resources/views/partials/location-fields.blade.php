@php
    $required = $required ?? true;
    $country = old('country', $country ?? ($required ? 'Türkiye' : ''));
    $city = old('city', $city ?? '');
    $district = old('district', $district ?? '');
    $showDistrict = $showDistrict ?? true;
    $countryMeta = $countryMeta ?? app(\App\Services\CountryMetaService::class);
    $initialIso = $country !== '' ? $countryMeta->isoForCountry($country) : 'tr';
@endphp

<div data-location-picker
     data-country="{{ $country }}"
     data-city="{{ $city }}"
     data-district="{{ $district }}"
     data-show-district="{{ $showDistrict ? '1' : '0' }}">
    <div class="flagged-select-wrap flagged-select-wrap--country">
        <span class="flagged-select-flag" data-flagged-select-flag aria-hidden="true">
            <img src="{{ $countryMeta->flagUrl($initialIso ?: 'tr') }}" alt="" width="20" height="15" loading="lazy">
        </span>
        <select name="country" class="loc-country flagged-select" @if($required) required @endif aria-label="Ülke">
            <option value="">Ülke</option>
        </select>
    </div>
    <select name="city" class="loc-city" @if($required) required @endif disabled>
        <option value="">Şehir</option>
    </select>
    @if($showDistrict)
    <div class="loc-district-wrap" hidden>
        <select name="district" class="loc-district">
            <option value="">İlçe</option>
        </select>
    </div>
    @endif
</div>
@error('country') <small class="form-error">{{ $message }}</small> @enderror
@error('city') <small class="form-error">{{ $message }}</small> @enderror
@if($showDistrict)
@error('district') <small class="form-error">{{ $message }}</small> @enderror
@endif
