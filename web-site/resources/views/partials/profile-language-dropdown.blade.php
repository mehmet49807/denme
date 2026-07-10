@php
    $locales = [
        'tr' => ['label' => 'Türkçe', 'flag' => 'tr', 'emoji' => '🇹🇷'],
        'en' => ['label' => 'English', 'flag' => 'gb', 'emoji' => '🇬🇧'],
        'de' => ['label' => 'Deutsch', 'flag' => 'de', 'emoji' => '🇩🇪'],
        'fr' => ['label' => 'Français', 'flag' => 'fr', 'emoji' => '🇫🇷'],
        'hi' => ['label' => 'हिन्दी', 'flag' => 'in', 'emoji' => '🇮🇳'],
    ];
    $current = app()->getLocale();
    $currentMeta = $locales[$current] ?? $locales['tr'];
@endphp
<details class="profile-language-dropdown" @if($errors->has('locale')) open @endif>
    <summary class="profile-language-trigger" aria-label="{{ __('Dil seç') }}">
        <span class="profile-language-trigger-flag" aria-hidden="true">
            <img class="profile-language-trigger-flag-img" src="https://flagcdn.com/w40/{{ $currentMeta['flag'] }}.png" alt="" width="20" height="14" loading="lazy" decoding="async">
            <span class="profile-language-trigger-flag-emoji">{{ $currentMeta['emoji'] }}</span>
        </span>
        <span class="profile-language-trigger-label">{{ __('Dil seç') }}</span>
        <span class="profile-language-trigger-chevron" aria-hidden="true">▾</span>
    </summary>
    <div class="profile-language-menu">
        <p class="profile-language-menu-hint">{{ __('Profil ve uygulama dili') }}</p>
        @error('locale') <small class="form-error profile-language-menu-error">{{ $message }}</small> @enderror
        <ul class="profile-language-menu-list">
            @foreach($locales as $code => $meta)
                <li>
                    <a
                        href="{{ route('profile.locale', $code) }}"
                        class="profile-language-menu-item {{ $current === $code ? 'profile-language-menu-item--active' : '' }}"
                        @if($current === $code) aria-current="true" @endif
                    >
                        <span class="profile-language-menu-item-flag" aria-hidden="true">
                            <img src="https://flagcdn.com/w40/{{ $meta['flag'] }}.png" alt="" width="20" height="14" loading="lazy" decoding="async">
                            <span class="profile-language-menu-item-emoji">{{ $meta['emoji'] }}</span>
                        </span>
                        <span class="profile-language-menu-item-label">{{ $meta['label'] }}</span>
                        @if($current === $code)
                            <span class="profile-language-menu-item-check" aria-hidden="true">✓</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</details>
