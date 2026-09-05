<span class="sidebar-nav-icon" aria-hidden="true">
@switch($icon)
    @case('feed')
        {{-- Home / Akış — filled house --}}
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M4.5 10.8 12 4.5l7.5 6.3V19a1.5 1.5 0 0 1-1.5 1.5h-3.2v-5.2h-5.6v5.2H6A1.5 1.5 0 0 1 4.5 19v-8.2Z" fill="currentColor" opacity="0.92"/>
            <path d="M9.5 20.5h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" opacity="0.35"/>
        </svg>
        @break
    @case('users')
        {{-- Keşfet / Üyeler — compass spark --}}
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.75"/>
            <path d="m10.1 10.1 5.3-1.7-1.7 5.3-5.3 1.7 1.7-5.3Z" fill="currentColor"/>
            <circle cx="12" cy="12" r="1.15" fill="#fff" opacity="0.9"/>
        </svg>
        @break
    @case('profile')
        {{-- Profil — person in circle --}}
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.75"/>
            <circle cx="12" cy="10" r="2.6" fill="currentColor"/>
            <path d="M7.4 17.2c1.2-2 2.7-3 4.6-3s3.4 1 4.6 3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
        </svg>
        @break
    @case('messages')
        {{-- Mesajlar — chat bubble --}}
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M6.2 17.6 4.5 20.2c-.35.55.1 1.25.75 1.1l3.4-.75A8.7 8.7 0 0 0 12 21.2c4.7 0 8.5-3.5 8.5-7.85S16.7 5.5 12 5.5 3.5 9 3.5 13.35c0 1.55.5 3 1.4 4.2" fill="currentColor" opacity="0.92"/>
            <circle cx="9" cy="13.2" r="0.95" fill="#fff"/>
            <circle cx="12" cy="13.2" r="0.95" fill="#fff"/>
            <circle cx="15" cy="13.2" r="0.95" fill="#fff"/>
        </svg>
        @break
    @case('notifications')
        {{-- Bildirimler — bell --}}
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 3.6c-3.2 0-5.7 2.4-5.7 5.4v2.1c0 .9-.35 1.75-.95 2.4l-.7.75c-.55.6-.15 1.6.7 1.6h13.3c.85 0 1.25-1 .7-1.6l-.7-.75a3.5 3.5 0 0 1-.95-2.4V9c0-3-2.5-5.4-5.7-5.4Z" fill="currentColor" opacity="0.92"/>
            <path d="M10.2 19.2a1.9 1.9 0 0 0 3.6 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        @break
    @case('premium')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M5 17.5 7.2 8.8c.2-.75 1.25-.9 1.7-.25L12 12.2l3.1-3.65c.45-.65 1.5-.5 1.7.25L19 17.5H5Z" fill="currentColor" opacity="0.9"/>
            <path d="M4.5 19.2h15" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
        </svg>
        @break
    @case('gift')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <rect x="4" y="9" width="16" height="11" rx="2" fill="currentColor" opacity="0.9"/>
            <path d="M12 9v11M4 13h16" stroke="#fff" stroke-width="1.5" opacity="0.85"/>
            <path d="M12 9c-1.8-2.4-3.8-2.8-5.2-1.3S5.5 11 8 11h4M12 9c1.8-2.4 3.8-2.8 5.2-1.3S18.5 11 16 11h-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        @break
    @case('heart')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 20.4s-7.2-4.4-7.2-9.3A4.4 4.4 0 0 1 12 7.2a4.4 4.4 0 0 1 7.2 3.9c0 4.9-7.2 9.3-7.2 9.3Z" fill="currentColor" opacity="0.92"/>
        </svg>
        @break
    @case('admin')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M12 3.2 19.2 6v5.2c0 4.6-3 8.4-7.2 9.8-4.2-1.4-7.2-5.2-7.2-9.8V6L12 3.2Z" fill="currentColor" opacity="0.9"/>
            <path d="m9.2 12.1 1.9 1.9 3.8-3.9" stroke="#fff" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
    @case('logout')
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M10 4.5H7A2.5 2.5 0 0 0 4.5 7v10A2.5 2.5 0 0 0 7 19.5h3" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
            <path d="M13 12h7.5M17.5 8.5 21 12l-3.5 3.5" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break
@endswitch
</span>
