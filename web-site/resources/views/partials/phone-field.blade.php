@php
    $dialCodes = $dialCodes ?? [];
    $countryMeta = $countryMeta ?? app(\App\Services\CountryMetaService::class);
    $selectedCode = old('phone_country_code', $defaultCode ?? '+90');
    $localPhone = old('phone_local', $phoneLocal ?? '');
    $selectedIso = $countryMeta->isoForDialCode($selectedCode);
    $optional = ! empty($optional);
@endphp

<div class="form-group auth-field phone-field" data-phone-field>
    <label for="phone_local">
        @if(!empty($labelIcon))
            <span class="auth-field-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => $labelIcon])</span>
        @endif
        <span>{{ $label ?? __('app.auth.register.phone_private') }}@if($optional) <small>(isteğe bağlı)</small>@endif</span>
    </label>
    <div class="phone-field-row">
        <div class="flagged-select-wrap flagged-select-wrap--phone">
            <span class="flagged-select-flag" data-flagged-select-flag aria-hidden="true">
                <img src="{{ $countryMeta->flagUrl($selectedIso) }}" alt="" width="20" height="15" loading="lazy">
            </span>
            <select name="phone_country_code" class="flagged-select phone-country-select" @unless($optional) required @endunless aria-label="{{ __('app.auth.register.phone_code_label') }}">
                @foreach ($dialCodes as $entry)
                    <option
                        value="{{ $entry['dial'] }}"
                        data-iso="{{ $entry['iso'] }}"
                        {{ $selectedCode === $entry['dial'] ? 'selected' : '' }}
                    >{{ $entry['dial'] }} {{ $entry['name'] }}</option>
                @endforeach
            </select>
        </div>
        <input
            type="tel"
            id="phone_local"
            name="phone_local"
            class="phone-local-input"
            value="{{ $localPhone }}"
            placeholder="{{ __('app.auth.register.phone_placeholder') }}"
            autocomplete="tel-national"
            inputmode="numeric"
            @unless($optional) required @endunless
        >
    </div>
    @error('phone') <small class="form-error">{{ $message }}</small> @enderror
    @error('phone_local') <small class="form-error">{{ $message }}</small> @enderror
    @error('phone_country_code') <small class="form-error">{{ $message }}</small> @enderror
</div>
