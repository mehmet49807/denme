@php
    $sidebarUser = auth()->user();
    $unreadNotifications = $unreadNotifications ?? 0;
    $unreadMessages = $unreadMessages ?? 0;
    $active = $active ?? '';
@endphp

<nav class="app-sidebar-nav app-sidebar-nav--smart" aria-label="{{ __('app.nav.menu') }}">
    <div class="app-sidebar-nav-notch" aria-hidden="true"></div>
    <ul>
        <li>
            <a href="{{ route('users.index') }}" class="{{ $active === 'users' ? 'active' : '' }}">
                @include('partials.sidebar-icon', ['icon' => 'users'])
                <span class="sidebar-nav-label">{{ __('app.nav.users') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('profile') }}" class="{{ $active === 'profile' ? 'active' : '' }}">
                @include('partials.sidebar-icon', ['icon' => 'profile'])
                <span class="sidebar-nav-label">{{ __('app.nav.profile') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('feed') }}" class="{{ $active === 'feed' ? 'active' : '' }}">
                @include('partials.sidebar-icon', ['icon' => 'feed'])
                <span class="sidebar-nav-label">{{ __('app.nav.feed') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('messages.index') }}" class="{{ $active === 'messages' ? 'active' : '' }}" data-nav-badge="messages">
                @include('partials.sidebar-icon', ['icon' => 'messages'])
                <span class="sidebar-nav-label">{{ __('app.nav.messages') }}</span>
                @if($unreadMessages > 0)
                    <span class="sidebar-nav-badge">{{ $unreadMessages > 99 ? '99+' : $unreadMessages }}</span>
                @endif
            </a>
        </li>
        <li>
            <a href="{{ route('notifications.index') }}" class="{{ $active === 'notifications' ? 'active' : '' }}" data-nav-badge="notifications">
                @include('partials.sidebar-icon', ['icon' => 'notifications'])
                <span class="sidebar-nav-label">{{ __('app.nav.notifications') }}</span>
                @if($unreadNotifications > 0)
                    <span class="sidebar-nav-badge">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                @endif
            </a>
        </li>
        @if($sidebarUser?->isAdmin() && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
            <li class="sidebar-nav-item--extra">
                <a href="{{ route('admin.dashboard') }}" class="{{ $active === 'admin' ? 'active' : '' }}">
                    @include('partials.sidebar-icon', ['icon' => 'admin'])
                    <span class="sidebar-nav-label">{{ __('app.nav.admin') }}</span>
                </a>
            </li>
        @endif
    </ul>
</nav>

@once
    @push('page-scripts')
    <script>
    (function () {
        var MOBILE_MAX = 767;
        function isMobileNav() { return window.innerWidth <= MOBILE_MAX; }
        function updateSmartNavFab() {
            var nav = document.querySelector('.app-sidebar-nav--smart');
            var sidebar = document.querySelector('.app-sidebar');
            if (!nav || !sidebar || !isMobileNav()) return;
            var active = nav.querySelector('a.active');
            var notch = nav.querySelector('.app-sidebar-nav-notch');
            if (!active) return;
            var navRect = nav.getBoundingClientRect();
            var activeRect = active.getBoundingClientRect();
            var centerX = activeRect.left + activeRect.width / 2 - navRect.left;
            var pct = Math.max(8, Math.min(92, (centerX / navRect.width) * 100));
            sidebar.style.setProperty('--smart-nav-notch-left', pct + '%');
            if (notch) notch.style.left = pct + '%';
        }
        function bindSmartNav() {
            var nav = document.querySelector('.app-sidebar-nav--smart');
            if (!nav) return;
            nav.querySelectorAll('a[href]').forEach(function (link) {
                link.addEventListener('click', function () {
                    if (!isMobileNav()) return;
                    nav.querySelectorAll('a.active').forEach(function (el) { el.classList.remove('active'); });
                    link.classList.add('active');
                    requestAnimationFrame(updateSmartNavFab);
                });
            });
            updateSmartNavFab();
            window.addEventListener('resize', updateSmartNavFab);
            window.addEventListener('orientationchange', function () { setTimeout(updateSmartNavFab, 120); });
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindSmartNav);
        } else {
            bindSmartNav();
        }
    })();
    </script>
    @endpush
@endonce

