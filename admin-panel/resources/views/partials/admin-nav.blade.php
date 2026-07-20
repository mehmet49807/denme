@php
    $items = [
        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'grid', 'theme' => 'gold'],
        ['route' => 'admin.moderation', 'label' => 'Denetim Kuyruğu', 'icon' => 'shield', 'theme' => 'coral'],
        ['route' => 'admin.users', 'label' => 'Kullanıcılar', 'icon' => 'users', 'theme' => 'indigo'],
        ['route' => 'admin.profile-approvals', 'label' => 'Profil Onay', 'icon' => 'shield', 'theme' => 'emerald'],
        ['route' => 'admin.messages', 'label' => 'Mesajlar', 'icon' => 'messages', 'theme' => 'sky'],
        ['route' => 'admin.gallery', 'label' => 'Galeri', 'icon' => 'image', 'theme' => 'violet'],
        ['route' => 'admin.content', 'label' => 'İçerik Denetimi', 'icon' => 'image', 'theme' => 'violet'],
        ['route' => 'admin.ai', 'label' => 'AI Denetim', 'icon' => 'sparkles', 'theme' => 'emerald'],
        ['route' => 'admin.auto-rules', 'label' => 'Otomatik Kurallar', 'icon' => 'flag', 'theme' => 'coral'],
        ['route' => 'admin.reports', 'label' => 'Şikayetler', 'icon' => 'flag', 'theme' => 'coral'],
        ['route' => 'admin.premium', 'label' => 'Premium', 'icon' => 'crown', 'theme' => 'amber'],
        ['route' => 'admin.packages', 'label' => 'Paketler', 'icon' => 'gift', 'theme' => 'gold'],
        ['route' => 'admin.app-links', 'label' => 'Uygulama', 'icon' => 'external', 'theme' => 'sky'],
        ['route' => 'admin.marketing', 'label' => 'Pazarlama', 'icon' => 'chart', 'theme' => 'violet'],
        ['route' => 'admin.broadcasts', 'label' => 'Duyurular', 'icon' => 'broadcast', 'theme' => 'emerald'],
        ['route' => 'admin.referrals', 'label' => 'Davet / Referans', 'icon' => 'gift', 'theme' => 'pink'],
        ['route' => 'admin.support', 'label' => '7/24 Destek', 'icon' => 'headset', 'theme' => 'lime'],
        ['route' => 'admin.emails', 'label' => 'E-posta', 'icon' => 'mail', 'theme' => 'cyan'],
        ['route' => 'admin.seo', 'label' => 'SEO & Google', 'icon' => 'search', 'theme' => 'teal'],
        ['route' => 'admin.audit', 'label' => 'Denetim Kayıtları', 'icon' => 'search', 'theme' => 'indigo'],
        ['route' => 'admin.system-health', 'label' => 'Sistem Sağlığı', 'icon' => 'chart', 'theme' => 'emerald'],
        ['route' => 'admin.staff', 'label' => 'Personel Rolleri', 'icon' => 'users', 'theme' => 'rose'],
        ['route' => 'admin.profile', 'label' => 'Profilim', 'icon' => 'user', 'theme' => 'rose'],
    ];
@endphp

<nav class="admin-sidebar-nav" aria-label="Admin menü">
    @foreach($items as $item)
        @php
            $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*');
        @endphp
        <a href="{{ route($item['route']) }}" class="admin-sidebar-link admin-sidebar-link--{{ $item['theme'] }} {{ $isActive ? 'is-active' : '' }}">
            <span class="admin-sidebar-icon" aria-hidden="true">
                @include('partials.admin-icon', ['icon' => $item['icon']])
            </span>
            <span>{{ $item['label'] }}</span>
        </a>
        @if($item['route'] === 'admin.ai')
            @include('partials.admin-nav-github-link')
        @endif
    @endforeach
    <a href="{{ config('app.frontend_url', 'https://www.gonulkoprusu.com') }}" class="admin-sidebar-link admin-sidebar-link--muted">
        <span class="admin-sidebar-icon" aria-hidden="true">
            @include('partials.admin-icon', ['icon' => 'external'])
        </span>
        <span>Siteye Dön</span>
    </a>
</nav>
