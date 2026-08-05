@extends('layouts.app-with-sidebar')

@section('title', $user->username . ' — Gönül Köprüsü')

@push('head')
@include('partials.asset', ['path' => 'css/user-profile.min.css'])
@endpush

@section('app-content')
@php
    $allStoryGroups = $targetStoryGroup ? collect([$targetStoryGroup]) : collect();
@endphp

<div class="profile-page feed-container">
    <header class="profile-header">
        @php
            $pkgType = $user->activePackageType();
        @endphp
        <div class="profile-photo-wrap {{ $targetStoryGroup ? 'profile-photo-wrap--has-story' : '' }}{{ in_array($pkgType, ['pro','gold','platinum']) ? ' profile-photo-wrap--premium-'.$pkgType : '' }}">
            @if($targetStoryGroup)
            <button
                type="button"
                class="profile-photo profile-photo--story story-item"
                data-story-index="0"
                data-user-id="{{ $user->id }}"
                aria-label="{{ $user->username }} hikayesi"
            >
                <span class="story-ring story-ring--unseen story-ring--profile{{ in_array($pkgType ?? null, ['pro','gold','platinum']) ? ' story-ring--premium-'.($pkgType ?? '') : '' }}">
                    <span class="story-avatar">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="73" height="73" loading="eager" decoding="async">
                        @else
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        @endif
                    </span>
                </span>
            </button>
            @else
            <div class="profile-photo">
                @if($user->profile_photo_url)
                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="77" height="77" loading="eager" decoding="async">
                @else
                    <span class="profile-photo-initial">{{ strtoupper(substr($user->username, 0, 1)) }}</span>
                @endif
                @include('partials.online-status-badge', ['user' => $user, 'size' => 'lg'])
            </div>
            @endif
        </div>
        @if(in_array($pkgType, ['pro','gold','platinum']))
        @php
            $_pkg = app(App\Services\PremiumPackagesService::class)->package($pkgType);
        @endphp
        <span class="profile-pkg-badge profile-pkg-badge--{{ $pkgType }}">
            {{ $_pkg['badge_label'] ?? ucfirst($pkgType) }}
        </span>
        @endif
        <div class="profile-header-meta">
            @php $profileAge = $user->age(); @endphp
            <div class="profile-header-topbar">
                <h1 class="profile-username profile-identity-name profile-header-topbar__name">
                    <span class="profile-username-text">{{ $user->username }}</span>
                    @if($profileAge)
                        <span class="profile-identity-age" title="Yaş">{{ $profileAge }}</span>
                    @endif
                    @include('partials.profile-verified-tick', ['user' => $user, 'size' => 'md'])
                    @include('partials.trust-badge', ['user' => $user, 'size' => 'md'])
                    @if($user->showsReferralBadge())
                        <span class="profile-referral-badge" title="Davetçi">Davetçi</span>
                    @endif
                    @include('partials.profile-online-label', ['user' => $user])
                </h1>

                @if(empty($viewerHasBlocked))
                <form
                    method="POST"
                    action="{{ route('users.follow', $user->username) }}"
                    class="profile-follow-form profile-header-follow"
                    data-profile-follow
                    data-following="{{ !empty($viewerFollowing) ? '1' : '0' }}"
                >
                    @csrf
                    <button
                        type="submit"
                        class="profile-action-btn profile-action-btn--follow profile-header-follow__btn {{ !empty($viewerFollowing) ? 'is-following' : '' }}"
                        data-follow-btn
                        aria-pressed="{{ !empty($viewerFollowing) ? 'true' : 'false' }}"
                        title="{{ !empty($viewerFollowing) ? __('app.profile.following') : __('app.profile.follow') }}"
                        aria-label="{{ !empty($viewerFollowing) ? __('app.profile.following') : __('app.profile.follow') }}"
                    >
                        <span class="profile-action-icon profile-action-icon--follow" aria-hidden="true">
                            @if(!empty($viewerFollowing))
                                @include('partials.theme-icon', ['icon' => 'user-check'])
                            @else
                                @include('partials.theme-icon', ['icon' => 'user-plus'])
                            @endif
                        </span>
                        <span class="profile-action-label" data-follow-label>
                            {{ !empty($viewerFollowing) ? __('app.profile.following') : __('app.profile.follow') }}
                        </span>
                    </button>
                </form>
                @endif
            </div>

            @include('partials.profile-identity', [
                'user' => $user,
                'postsCount' => $posts->count(),
                'tickSize' => 'md',
                'locationAsLinks' => true,
                'hideNameRow' => true,
            ])
            @include('partials.profile-member-badges', ['user' => $user])
            @include('partials.hobbies-display', ['user' => $user])

            @if($targetStoryGroup)
                <p class="profile-story-hint">Profil fotoğrafına veya hikayeye dokunarak görüntüleyin.</p>
            @endif

            @if(!empty($viewerHasBlocked))
                <p class="profile-blocked-notice">{{ __('app.profile.blocked_notice', ['name' => $user->username]) }}</p>
                <div class="user-profile-actions">
                    <form
                        method="POST"
                        action="{{ route('users.unblock', $user->username) }}"
                        class="profile-unblock-form"
                        data-unblock-confirm="{{ __('app.profile.unblock_confirm', ['name' => $user->username]) }}"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="profile-action-btn profile-action-btn--unblock" title="{{ __('app.profile.unblock_title', ['name' => $user->username]) }}">
                            <span class="profile-action-icon profile-action-icon--unblock" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/>
                                    <path d="M7.5 12h9" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                </svg>
                            </span>
                            <span class="profile-action-label">{{ __('app.profile.unblock') }}</span>
                        </button>
                    </form>
                </div>
            @else
            <div class="user-profile-actions user-profile-actions--secondary">
                @if($viewer->canSendMessages())
                <a
                    href="{{ route('messages.show', $user->username) }}"
                    class="profile-action-btn profile-action-btn--message"
                    title="{{ __('app.profile.send_message') }}"
                >
                    <span class="profile-action-icon profile-action-icon--messages" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 6.5A3.5 3.5 0 018.5 3h7A3.5 3.5 0 0119 6.5v7A3.5 3.5 0 0115.5 17H10l-4.5 3.5V17H8.5A3.5 3.5 0 015 13.5v-7z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="profile-action-label">{{ __('app.profile.send_message') }}</span>
                </a>
                @elseif($viewer->gender === 'male')
                <a
                    href="{{ route('premium') }}"
                    class="profile-action-btn profile-action-btn--premium-msg"
                    title="{{ __('app.profile.message_locked_title') }}"
                    aria-label="{{ __('app.profile.message_locked_title') }}"
                >
                    <span class="profile-action-icon profile-action-icon--premium-msg" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 6.5A3.5 3.5 0 018.5 3h7A3.5 3.5 0 0119 6.5v7A3.5 3.5 0 0115.5 17H10l-4.5 3.5V17H8.5A3.5 3.5 0 015 13.5v-7z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            <path d="M16.4 4.2l.7 1.3 1.4.2-1 1 .2 1.4-1.3-.7-1.3.7.2-1.4-1-1 1.4-.2.7-1.3z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="profile-action-label">{{ __('app.profile.message_locked_cta') }}</span>
                </a>
                @endif

                <form
                    method="POST"
                    action="{{ route('users.like', $user->username) }}"
                    class="profile-like-form"
                    data-profile-like
                    data-liked="{{ !empty($viewerLiked) ? '1' : '0' }}"
                    data-matched="{{ !empty($isMatched) ? '1' : '0' }}"
                >
                    @csrf
                    <button
                        type="submit"
                        class="profile-action-btn profile-action-btn--like {{ !empty($viewerLiked) ? 'is-liked' : '' }} {{ !empty($isMatched) ? 'is-matched' : '' }}"
                        data-like-btn
                        aria-pressed="{{ !empty($viewerLiked) ? 'true' : 'false' }}"
                    >
                        <span class="profile-action-icon profile-action-icon--like" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 20.5s-7.2-4.7-9.2-8.8C1.2 8.2 3.4 5 6.8 5c1.8 0 3.2.9 4 2.1.8-1.2 2.2-2.1 4-2.1 3.4 0 5.6 3.2 4 6.7-2 4.1-9.2 8.8-9.2 8.8z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="profile-action-label" data-like-label>
                            @if(!empty($isMatched))
                                {{ __('app.profile.matched') }}
                            @elseif(!empty($viewerLiked))
                                {{ __('app.profile.liked') }}
                            @else
                                {{ __('app.profile.like') }}
                            @endif
                        </span>
                    </button>
                </form>

                <details class="profile-more">
                    <summary
                        class="profile-action-btn profile-action-btn--more"
                        title="{{ __('app.profile.more_actions') }}"
                        aria-label="{{ __('app.profile.more_actions') }}"
                    >
                        <span class="profile-action-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/>
                            </svg>
                        </span>
                    </summary>
                    <div class="profile-more__menu" role="menu">
                        <button type="button" class="profile-more__item" id="openReportDialog" role="menuitem">
                            <span class="profile-action-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 9v4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                    <path d="M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    <path d="M10.3 3.6L2.6 17a2 2 0 001.7 3h15.4a2 2 0 001.7-3L13.7 3.6a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            {{ __('app.profile.report') }}
                        </button>
                        <form
                            method="POST"
                            action="{{ route('users.block', $user->username) }}"
                            class="profile-block-form"
                            data-block-confirm="{{ __('app.messages.block_confirm', ['name' => $user->username]) }}"
                            role="none"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="profile-more__item profile-more__item--danger"
                                role="menuitem"
                                title="{{ __('app.messages.block_title', ['name' => $user->username]) }}"
                            >
                                <span class="profile-action-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/>
                                        <path d="M5.5 5.5l13 13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                                    </svg>
                                </span>
                                {{ __('app.messages.block') }}
                            </button>
                        </form>
                    </div>
                </details>
            </div>
            @endif
        </div>
    </header>

    @if($targetStoryGroup)
    <section class="profile-stories-section stories-section" aria-label="Hikayeler">
        <div class="stories-strip profile-stories-strip">
            <button
                type="button"
                class="story-item story-item--profile"
                data-story-index="0"
                data-user-id="{{ $user->id }}"
                aria-label="{{ $user->username }} hikayeleri"
            >
                <span class="story-ring story-ring--unseen story-ring--profile{{ in_array($pkgType ?? null, ['pro','gold','platinum']) ? ' story-ring--premium-'.($pkgType ?? '') : '' }}">
                    <span class="story-avatar">
                        @if($user->profile_photo_url)
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="62" height="62" loading="lazy" decoding="async">
                        @else
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        @endif
                    </span>
                </span>
                <span class="story-username">{{ count($targetStoryGroup['items']) > 1 ? count($targetStoryGroup['items']).' hikaye' : 'Hikaye' }}</span>
            </button>
        </div>
    </section>
    @endif

    @if(session('success'))
        <p class="profile-success">{{ session('success') }}</p>
    @endif

    @include('partials.profile-gallery', ['user' => $user, 'viewer' => $viewer])

    @include('partials.profile-posts-grid', [
        'profileUser' => $user,
        'viewer' => $viewer,
        'likedPostIds' => $likedPostIds ?? [],
        'isOwnProfile' => false,
    ])
</div>

<dialog id="reportDialog" class="profile-report-dialog">
    <form method="POST" action="{{ route('users.report', $user->username) }}" class="profile-report-form">
        @csrf
        <header class="profile-report-header">
            <h2>{{ $user->username }} — Şikayet Et</h2>
            <button type="button" class="profile-report-close" data-close-report aria-label="Kapat">×</button>
        </header>
        <p class="profile-report-hint">Uygunsuz davranış veya profil içeriği hakkında moderasyon ekibimize bildirin.</p>
        <label for="report_reason" class="sr-only">Şikayet sebebi</label>
        <textarea
            id="report_reason"
            name="reason"
            class="profile-report-input {{ $errors->has('reason') ? 'profile-report-input--error' : '' }}"
            rows="4"
            maxlength="1000"
            placeholder="Şikayet sebebinizi yazın…"
            required
        >{{ old('reason') }}</textarea>
        @error('reason') <small class="form-error">{{ $message }}</small> @enderror
        <footer class="profile-report-footer">
            <button type="button" class="btn btn-outline btn-sm" data-close-report>İptal</button>
            <button type="submit" class="btn btn-primary btn-sm profile-report-submit">Gönder</button>
        </footer>
    </form>
</dialog>

@include('partials.ig-story-viewer')

<script>
(function () {
    document.querySelectorAll('.profile-unblock-form, .profile-block-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const msg = form.getAttribute('data-unblock-confirm') || form.getAttribute('data-block-confirm') || '';
            if (!window.confirm(msg)) {
                e.preventDefault();
                return;
            }
            const btn = form.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
        });
    });

    const dialog = document.getElementById('reportDialog');
    const openBtn = document.getElementById('openReportDialog');
    const more = document.querySelector('.profile-more');
    if (!dialog || !openBtn) return;

    openBtn.addEventListener('click', function () {
        if (more) more.open = false;
        dialog.showModal();
    });
    document.querySelectorAll('[data-close-report]').forEach(function (el) {
        el.addEventListener('click', function () { dialog.close(); });
    });
    document.addEventListener('click', function (e) {
        if (!more || !more.open) return;
        if (!more.contains(e.target)) more.open = false;
    });

    @if($errors->has('reason'))
    dialog.showModal();
    @endif
})();
</script>
@include('partials.asset', ['path' => 'js/feed-page.min.js', 'defer' => true])
@include('partials.asset', ['path' => 'js/profile-posts.min.js', 'defer' => true])
@endsection
