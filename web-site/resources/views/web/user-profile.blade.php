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
        <div class="profile-photo-wrap {{ $targetStoryGroup ? 'profile-photo-wrap--has-story' : '' }}">
            @if($targetStoryGroup)
            <button
                type="button"
                class="profile-photo profile-photo--story story-item"
                data-story-index="0"
                data-user-id="{{ $user->id }}"
                aria-label="{{ $user->username }} hikayesi"
            >
                <span class="story-ring story-ring--unseen story-ring--profile">
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
        <div class="profile-header-meta">
            @include('partials.profile-identity', [
                'user' => $user,
                'postsCount' => $posts->count(),
                'tickSize' => 'md',
                'locationAsLinks' => true,
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
            <div class="user-profile-actions">
                @if($viewer->canSendMessages())
                <a href="{{ route('messages.show', $user->username) }}" class="profile-action-btn profile-action-btn--message">
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
                            <path d="M5 7.2A3.2 3.2 0 018.2 4h7.6A3.2 3.2 0 0119 7.2v5.1A3.2 3.2 0 0115.8 15.5H11l-3.8 2.9v-2.9H8.2A3.2 3.2 0 015 12.3V7.2z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                            <path d="M16.2 3.2l1.05 1.9 2.1.3-1.5 1.5.35 2.1-1.95-1.05-1.95 1.05.35-2.1-1.5-1.5 2.1-.3 1.05-1.9z" fill="currentColor"/>
                        </svg>
                    </span>
                    <span class="profile-action-copy">
                        <span class="profile-action-label">{{ __('app.profile.message_locked_cta') }}</span>
                        <span class="profile-action-sub">{{ __('app.profile.message_locked_sub') }}</span>
                    </span>
                </a>
                @endif

                <button type="button" class="profile-action-btn profile-action-btn--report" id="openReportDialog" aria-haspopup="dialog">
                    <span class="profile-action-icon profile-action-icon--report" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 9v4" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            <path d="M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M10.3 3.6L2.6 17a2 2 0 001.7 3h15.4a2 2 0 001.7-3L13.7 3.6a2 2 0 00-3.4 0z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <span class="profile-action-label">{{ __('app.profile.report') }}</span>
                </button>

                <form
                    method="POST"
                    action="{{ route('users.block', $user->username) }}"
                    class="profile-block-form"
                    data-block-confirm="{{ __('app.messages.block_confirm', ['name' => $user->username]) }}"
                >
                    @csrf
                    <button type="submit" class="profile-action-btn profile-action-btn--block" title="{{ __('app.messages.block_title', ['name' => $user->username]) }}">
                        <span class="profile-action-icon profile-action-icon--block" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.75"/>
                                <path d="M5.5 5.5l13 13" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                        </span>
                        <span class="profile-action-label">{{ __('app.messages.block') }}</span>
                    </button>
                </form>
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
                <span class="story-ring story-ring--unseen story-ring--profile">
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

@if($targetStoryGroup)
<div class="ig-story-viewer" id="igStoryViewer" hidden data-groups="{{ $allStoryGroups->toJson() }}">
    <div class="ig-story-frame">
        <div class="ig-story-progress" id="igStoryProgress"></div>

        <header class="ig-story-header">
            <a href="{{ route('users.show', $user->username) }}" id="igStoryUserLink" class="ig-story-user">
                <span class="ig-story-user-avatar" id="igStoryUserAvatar"></span>
                <span class="ig-story-user-meta">
                    <strong id="igStoryUserName"></strong>
                    <small id="igStoryTime">Şimdi</small>
                </span>
            </a>
            <div class="ig-story-header-actions">
                <button type="button" class="ig-story-close" data-close-story aria-label="Kapat">×</button>
            </div>
        </header>

        <div class="ig-story-stage" id="igStoryStage">
            <button type="button" class="ig-story-tap ig-story-tap--prev" id="igStoryTapPrev" aria-label="Önceki"></button>
            <div class="ig-story-media" id="igStoryMedia"></div>
            <button type="button" class="ig-story-tap ig-story-tap--next" id="igStoryTapNext" aria-label="Sonraki"></button>
        </div>
    </div>
</div>
@endif

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
    if (!dialog || !openBtn) return;

    openBtn.addEventListener('click', function () { dialog.showModal(); });
    document.querySelectorAll('[data-close-report]').forEach(function (el) {
        el.addEventListener('click', function () { dialog.close(); });
    });

    @if($errors->has('reason'))
    dialog.showModal();
    @endif
})();
</script>
@include('partials.asset', ['path' => 'js/feed-page.min.js', 'defer' => true])
@include('partials.asset', ['path' => 'js/profile-posts.min.js', 'defer' => true])
@endsection
