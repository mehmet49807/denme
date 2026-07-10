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
            <a href="{{ route('feed') }}" class="{{ $active === 'feed' ? 'active' : '' }}">
                @include('partials.sidebar-icon', ['icon' => 'feed'])
                <span class="sidebar-nav-label">{{ __('app.nav.feed') }}</span>
            </a>
        </li>
        <li>
            <a href="{{ route('users.index') }}" class="{{ $active === 'users' ? 'active' : '' }}">
                @include('partials.sidebar-icon', ['icon' => 'users'])
                <span class="sidebar-nav-label">{{ __('app.nav.users') }}</span>
            </a>
        </li>
        <li class="sidebar-nav-item--center">
            <a href="{{ route('profile') }}" class="sidebar-nav-fab {{ $active === 'profile' ? 'active' : '' }}">
                @include('partials.sidebar-icon', ['icon' => 'profile'])
                <span class="sidebar-nav-label">{{ __('app.nav.profile') }}</span>
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
        @if($sidebarUser->isAdmin() && \Illuminate\Support\Facades\Route::has('admin.dashboard'))
            <li class="sidebar-nav-item--extra">
                <a href="{{ route('admin.dashboard') }}">
                    @include('partials.sidebar-icon', ['icon' => 'admin'])
                    <span class="sidebar-nav-label">{{ __('app.nav.admin') }}</span>
                </a>
            </li>
        @endif
    </ul>
</nav>
