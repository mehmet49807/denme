@extends('layouts.app-with-sidebar')

@section('title', 'Ayarlar — ' . __('app.brand'))

@section('app-content')
@php
    $backUrl = url()->previous();
    if ($backUrl === url()->current()) {
        $backUrl = route('feed');
    }
@endphp

<div
    class="profile-settings-page feed-container"
    id="profileSettingsPage"
    data-initial-panel="{{ $initialPanel }}"
>
    <header class="profile-settings-page-header">
        <button type="button" class="profile-settings-page-back" data-settings-back @if($initialPanel === 'menu') hidden @endif aria-label="Geri">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <h1 class="profile-settings-page-title" id="profileSettingsTitle">Ayarlar</h1>
        <a href="{{ $backUrl }}" class="profile-settings-page-close" aria-label="Kapat">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </a>
    </header>

    <div class="profile-settings-page-body">
        @include('partials.profile-settings-panels', ['user' => $user, 'initialPanel' => $initialPanel])
    </div>
</div>
@endsection

@push('page-scripts')
<script src="{{ asset('js/profile-settings.js') }}?v=profile-settings-3"></script>
<script src="{{ asset('js/hobbies-picker.js') }}?v=hobbies-1"></script>
<script src="{{ asset('js/locations.js') }}?v=world-locations-1"></script>
@endpush
