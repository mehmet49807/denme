@php
    $primary = array_values(array_filter([
        ['key' => 'about', 'label' => 'Hakkımızda', 'url' => route('about'), 'icon' => 'heart', 'tone' => 'rose'],
        ['key' => 'safe-meeting', 'label' => 'Güvenli Tanışma', 'url' => route('safe-meeting'), 'icon' => 'shield', 'tone' => 'teal'],
        ['key' => 'blog', 'label' => 'Blog', 'url' => url('/blog'), 'icon' => 'post', 'tone' => 'amber'],
        Route::has('stories')
            ? ['key' => 'stories', 'label' => 'Başarı Hikâyeleri', 'url' => route('stories'), 'icon' => 'sparkles', 'tone' => 'coral']
            : null,
        ['key' => 'sss', 'label' => 'SSS', 'url' => url('/sss'), 'icon' => 'messages', 'tone' => 'sky'],
        ['key' => 'support', 'label' => 'Destek', 'url' => route('support'), 'icon' => 'support', 'tone' => 'orange'],
    ]));
    $legal = [
        ['key' => 'complaints', 'label' => 'Şikayet & Engelleme', 'url' => route('complaints'), 'icon' => 'bell', 'tone' => 'violet'],
        ['key' => 'privacy', 'label' => 'Gizlilik', 'url' => route('privacy'), 'icon' => 'eye', 'tone' => 'indigo'],
        ['key' => 'kvkk', 'label' => 'KVKK', 'url' => route('kvkk'), 'icon' => 'lock', 'tone' => 'emerald'],
        ['key' => 'terms', 'label' => 'Kullanım Koşulları', 'url' => route('terms'), 'icon' => 'star', 'tone' => 'gold'],
    ];
    $activeKey = $active ?? '';
    $activeLabel = 'Keşfet';
    $legalOpen = false;
    foreach (array_merge($primary, $legal) as $link) {
        if ($activeKey === $link['key']) {
            $activeLabel = $link['label'];
            break;
        }
    }
    foreach ($legal as $link) {
        if ($activeKey === $link['key']) {
            $legalOpen = true;
            break;
        }
    }
@endphp

<nav class="info-nav" aria-label="Bilgi Merkezi">
    <div class="info-nav__card">
        <header class="info-nav__head">
            <span class="info-nav__head-ico" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'sparkles'])</span>
            <div class="info-nav__head-text">
                <strong>Bilgi Merkezi</strong>
                <small>{{ $activeLabel }}</small>
            </div>
        </header>

        <p class="info-nav__group">Keşfet</p>
        <ul class="info-nav__list">
            @foreach($primary as $link)
                <li>
                    <a
                        href="{{ $link['url'] }}"
                        class="info-nav__link{{ $activeKey === $link['key'] ? ' is-active' : '' }}"
                        @if($activeKey === $link['key']) aria-current="page" @endif
                    >
                        <span class="info-nav__icon info-nav__icon--{{ $link['tone'] }}" aria-hidden="true">
                            @include('partials.theme-icon', ['icon' => $link['icon']])
                        </span>
                        <span class="info-nav__label">{{ $link['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        <details class="info-nav__legal" @if($legalOpen) open @endif>
            <summary class="info-nav__legal-summary">
                <span>Yasal bilgiler</span>
                <span class="info-nav__chevron" aria-hidden="true"></span>
            </summary>
            <ul class="info-nav__list info-nav__list--legal">
                @foreach($legal as $link)
                    <li>
                        <a
                            href="{{ $link['url'] }}"
                            class="info-nav__link{{ $activeKey === $link['key'] ? ' is-active' : '' }}"
                            @if($activeKey === $link['key']) aria-current="page" @endif
                        >
                            <span class="info-nav__icon info-nav__icon--{{ $link['tone'] }}" aria-hidden="true">
                                @include('partials.theme-icon', ['icon' => $link['icon']])
                            </span>
                            <span class="info-nav__label">{{ $link['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </details>
    </div>
</nav>
