@extends('layouts.app-with-sidebar')

@php $activeNav = 'notifications'; @endphp

@section('title', __('app.notifications.title') . ' — ' . __('app.brand'))

@push('head-meta')
<meta name="notifications-poll-url" content="{{ route('notifications.poll') }}">
<meta name="notifications-poll-since" content="{{ now()->toIso8601String() }}">
@endpush

@section('app-content')
<div class="notifications-page">
    <header class="notifications-header">
        <h1>{{ __('app.notifications.title') }}</h1>
        <p class="notifications-subtitle">{{ __('app.notifications.subtitle') }}</p>
    </header>

    @if($items->isNotEmpty())
    <ul class="notification-list" id="notificationList">
        @include('web.notifications.partials.list-items', ['items' => $items])
    </ul>
    @else
    @include('partials.empty-state', [
        'class' => 'notifications-empty',
        'icon' => 'bell',
        'title' => __('app.notifications.empty'),
        'text' => __('app.notifications.empty_hint'),
        'ctaUrl' => route('feed'),
        'ctaLabel' => __('app.messages.go_feed'),
    ])
    @endif
</div>
@endsection

