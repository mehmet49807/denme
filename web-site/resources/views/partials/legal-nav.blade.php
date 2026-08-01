@php
    $items = [
        [
            'group' => 'Keşfet',
            'links' => array_values(array_filter([
                ['key' => 'about', 'label' => 'Hakkımızda', 'url' => route('about'), 'icon' => 'heart', 'tone' => 'rose'],
                ['key' => 'safe-meeting', 'label' => 'Güvenli Tanışma', 'url' => route('safe-meeting'), 'icon' => 'shield', 'tone' => 'teal'],
                ['key' => 'blog', 'label' => 'Blog', 'url' => url('/blog'), 'icon' => 'post', 'tone' => 'amber'],
                ['key' => 'sss', 'label' => 'SSS', 'url' => url('/sss'), 'icon' => 'messages', 'tone' => 'sky'],
                Route::has('stories')
                    ? ['key' => 'stories', 'label' => 'Başarı Hikâyeleri', 'url' => route('stories'), 'icon' => 'sparkles', 'tone' => 'coral']
                    : null,
                ['key' => 'support', 'label' => '7/24 Destek', 'url' => route('support'), 'icon' => 'support', 'tone' => 'orange'],
            ])),
        ],
        [
            'group' => 'Yasal',
            'links' => [
                ['key' => 'complaints', 'label' => 'Şikayet & Engelleme', 'url' => route('complaints'), 'icon' => 'bell', 'tone' => 'violet'],
                ['key' => 'privacy', 'label' => 'Gizlilik', 'url' => route('privacy'), 'icon' => 'eye', 'tone' => 'indigo'],
                ['key' => 'kvkk', 'label' => 'KVKK', 'url' => route('kvkk'), 'icon' => 'lock', 'tone' => 'emerald'],
                ['key' => 'terms', 'label' => 'Kullanım Koşulları', 'url' => route('terms'), 'icon' => 'star', 'tone' => 'gold'],
            ],
        ],
    ];
    $activeKey = $active ?? '';
    $activeLabel = 'Sayfa seç';
    foreach ($items as $section) {
        foreach ($section['links'] as $link) {
            if ($activeKey === $link['key']) {
                $activeLabel = $link['label'];
                break 2;
            }
        }
    }
@endphp

<nav class="info-nav" aria-label="Bilgi Merkezi">
    <details class="info-nav__panel">
        <summary class="info-nav__summary">
            <span class="info-nav__summary-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'sparkles'])</span>
            <span class="info-nav__summary-text">
                <strong>Bilgi Merkezi</strong>
                <small>{{ $activeLabel }}</small>
            </span>
            <span class="info-nav__chevron" aria-hidden="true"></span>
        </summary>

        <div class="info-nav__body">
            @foreach($items as $section)
                <p class="info-nav__group">{{ $section['group'] }}</p>
                <ul class="info-nav__list">
                    @foreach($section['links'] as $link)
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
            @endforeach
        </div>
    </details>
</nav>
