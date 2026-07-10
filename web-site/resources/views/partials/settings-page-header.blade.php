@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current()) {
        $backUrl = route('feed');
    }
@endphp

<header class="profile-settings-page-header">
    <a href="{{ $backUrl }}" class="profile-settings-page-back" aria-label="Geri">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
    </a>
    <h1 class="profile-settings-page-title">{{ $title }}</h1>
    <span class="profile-settings-page-spacer" aria-hidden="true"></span>
</header>
