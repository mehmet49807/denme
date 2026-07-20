@php
    $groups = [
        [
            'id' => 'overview',
            'label' => 'Özet',
            'items' => [
                ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'grid', 'theme' => 'gold'],
            ],
        ],
        [
            'id' => 'moderation',
            'label' => 'Denetim',
            'items' => [
                ['route' => 'admin.moderation', 'label' => 'Denetim Kuyruğu', 'icon' => 'shield', 'theme' => 'coral'],
                ['route' => 'admin.profile-approvals', 'label' => 'Profil Onay', 'icon' => 'shield', 'theme' => 'emerald'],
                ['route' => 'admin.messages', 'label' => 'Mesajlar', 'icon' => 'messages', 'theme' => 'sky'],
                ['route' => 'admin.gallery', 'label' => 'Galeri', 'icon' => 'image', 'theme' => 'violet'],
                ['route' => 'admin.content', 'label' => 'İçerik', 'icon' => 'image', 'theme' => 'violet'],
                ['route' => 'admin.ai', 'label' => 'AI Denetim', 'icon' => 'sparkles', 'theme' => 'emerald'],
                ['route' => 'admin.github', 'label' => 'GitHub', 'icon' => 'external', 'theme' => 'indigo', 'partial' => 'github'],
                ['route' => 'admin.auto-rules', 'label' => 'Otomatik Kurallar', 'icon' => 'flag', 'theme' => 'coral'],
                ['route' => 'admin.reports', 'label' => 'Şikayetler', 'icon' => 'flag', 'theme' => 'coral'],
            ],
        ],
        [
            'id' => 'members',
            'label' => 'Üyeler',
            'items' => [
                ['route' => 'admin.users', 'label' => 'Kullanıcılar', 'icon' => 'users', 'theme' => 'indigo'],
                ['route' => 'admin.premium', 'label' => 'Premium', 'icon' => 'crown', 'theme' => 'amber'],
                ['route' => 'admin.staff', 'label' => 'Personel', 'icon' => 'users', 'theme' => 'rose'],
            ],
        ],
        [
            'id' => 'growth',
            'label' => 'Büyüme',
            'items' => [
                ['route' => 'admin.packages', 'label' => 'Paketler', 'icon' => 'gift', 'theme' => 'gold'],
                ['route' => 'admin.app-links', 'label' => 'Uygulama', 'icon' => 'external', 'theme' => 'sky'],
                ['route' => 'admin.marketing', 'label' => 'Pazarlama', 'icon' => 'chart', 'theme' => 'violet'],
                ['route' => 'admin.broadcasts', 'label' => 'Duyurular', 'icon' => 'broadcast', 'theme' => 'emerald'],
                ['route' => 'admin.referrals', 'label' => 'Davet / Referans', 'icon' => 'gift', 'theme' => 'pink'],
                ['route' => 'admin.seo', 'label' => 'SEO & Google', 'icon' => 'search', 'theme' => 'teal'],
            ],
        ],
        [
            'id' => 'support',
            'label' => 'Destek',
            'items' => [
                ['route' => 'admin.support', 'label' => '7/24 Destek', 'icon' => 'headset', 'theme' => 'lime'],
                ['route' => 'admin.emails', 'label' => 'E-posta', 'icon' => 'mail', 'theme' => 'cyan'],
            ],
        ],
        [
            'id' => 'system',
            'label' => 'Sistem',
            'items' => [
                ['route' => 'admin.audit', 'label' => 'Denetim Kayıtları', 'icon' => 'search', 'theme' => 'indigo'],
                ['route' => 'admin.system-health', 'label' => 'Sistem Sağlığı', 'icon' => 'chart', 'theme' => 'emerald'],
                ['route' => 'admin.updates', 'label' => 'Güncelleme', 'icon' => 'refresh', 'theme' => 'amber'],
                ['route' => 'admin.profile', 'label' => 'Profilim', 'icon' => 'user', 'theme' => 'rose'],
            ],
        ],
    ];

    $activeGroupId = null;
    foreach ($groups as $group) {
        foreach ($group['items'] as $item) {
            if (request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*')) {
                $activeGroupId = $group['id'];
                break 2;
            }
        }
    }
    if ($activeGroupId === null) {
        $activeGroupId = 'overview';
    }
@endphp

<nav class="admin-sidebar-nav" aria-label="Admin menü" data-admin-nav>
    @foreach($groups as $group)
        @php
            $isOpen = $group['id'] === $activeGroupId;
            $panelId = 'admin-nav-'.$group['id'];
        @endphp
        <details class="admin-nav-group" data-group="{{ $group['id'] }}" @if($isOpen) open @endif>
            <summary class="admin-nav-group__summary">
                <span class="admin-nav-group__label">{{ $group['label'] }}</span>
                <span class="admin-nav-group__count" aria-hidden="true">{{ count($group['items']) }}</span>
                <span class="admin-nav-group__chevron" aria-hidden="true"></span>
            </summary>
            <div class="admin-nav-group__items" id="{{ $panelId }}">
                @foreach($group['items'] as $item)
                    @if(($item['partial'] ?? null) === 'github')
                        @include('partials.admin-nav-github-link')
                    @else
                        @php
                            $isActive = request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*');
                        @endphp
                        <a href="{{ route($item['route']) }}" class="admin-sidebar-link admin-sidebar-link--{{ $item['theme'] }} {{ $isActive ? 'is-active' : '' }}">
                            <span class="admin-sidebar-icon" aria-hidden="true">
                                @include('partials.admin-icon', ['icon' => $item['icon']])
                            </span>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        </details>
    @endforeach

    <a href="{{ config('app.frontend_url', 'https://www.gonulkoprusu.com') }}" class="admin-sidebar-link admin-sidebar-link--muted">
        <span class="admin-sidebar-icon" aria-hidden="true">
            @include('partials.admin-icon', ['icon' => 'external'])
        </span>
        <span>Siteye Dön</span>
    </a>
</nav>

<script>
(function () {
    var root = document.querySelector('[data-admin-nav]');
    if (!root) return;
    var key = 'gk_admin_nav_open';
    var groups = Array.prototype.slice.call(root.querySelectorAll('.admin-nav-group'));

    try {
        var saved = JSON.parse(localStorage.getItem(key) || '[]');
        if (Array.isArray(saved) && saved.length) {
            groups.forEach(function (el) {
                var id = el.getAttribute('data-group');
                if (saved.indexOf(id) !== -1) el.open = true;
            });
        }
    } catch (e) {}

    // Aktif grubu her zaman açık tut
    var active = root.querySelector('.admin-sidebar-link.is-active');
    if (active) {
        var parent = active.closest('.admin-nav-group');
        if (parent) parent.open = true;
    }

    function persist() {
        var openIds = groups.filter(function (el) { return el.open; }).map(function (el) {
            return el.getAttribute('data-group');
        });
        try { localStorage.setItem(key, JSON.stringify(openIds)); } catch (e) {}
    }

    groups.forEach(function (el) {
        el.addEventListener('toggle', persist);
    });
})();
</script>
