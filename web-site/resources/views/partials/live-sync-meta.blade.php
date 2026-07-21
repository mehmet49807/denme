@php
    $liveSyncMode = 'auto';
    if (request()->routeIs('feed')) {
        $liveSyncMode = 'feed';
    } elseif (request()->routeIs('users.index') || request()->routeIs('locations.*')) {
        $liveSyncMode = 'users';
    } elseif (request()->routeIs('premium')) {
        $liveSyncMode = 'premium';
    } elseif (request()->routeIs('profile') || request()->routeIs('users.show')) {
        $liveSyncMode = 'profile';
    }

    $feedSince = null;
    if (isset($posts) && is_object($posts) && method_exists($posts, 'first')) {
        $firstPost = $posts->first();
        $feedSince = $firstPost?->created_at?->toIso8601String();
    }

    $profileSince = null;
    $profileUsername = null;
    if (isset($profileUser) && is_object($profileUser)) {
        $profileUsername = $profileUser->username ?? null;
        if (isset($profilePosts) && is_object($profilePosts) && method_exists($profilePosts, 'first')) {
            $profileSince = $profilePosts->first()?->created_at?->toIso8601String();
        } elseif (isset($posts) && is_object($posts) && method_exists($posts, 'first') && request()->routeIs('users.show', 'profile')) {
            $profileSince = $posts->first()?->created_at?->toIso8601String();
        }
    } elseif (request()->routeIs('profile') && auth()->check()) {
        $profileUsername = auth()->user()->username;
    }
@endphp
<meta name="live-sync-mode" content="{{ $liveSyncMode }}">
@if(in_array($liveSyncMode, ['feed', 'auto'], true) && $feedSince)
<meta name="live-sync-feed-since" content="{{ $feedSince }}">
@endif
@if(in_array($liveSyncMode, ['profile', 'auto'], true) && $profileUsername)
<meta name="live-sync-profile-username" content="{{ $profileUsername }}">
@if($profileSince)
<meta name="live-sync-profile-since" content="{{ $profileSince }}">
@endif
@endif
