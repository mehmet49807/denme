@extends('layouts.app-with-sidebar')

@php $activeNav = 'messages'; @endphp

@section('title', __('app.messages.title') . ' — ' . __('app.brand'))

@push('head-meta')
<meta name="inbox-poll-url" content="{{ route('messages.inbox.poll') }}">
@endpush

@section('app-content')
<div class="dm-shell dm-shell--list-only">
    @include('web.messages.partials.dm-inbox', ['conversations' => $conversations])

    <section class="dm-thread dm-thread--placeholder" aria-hidden="true">
        <div class="dm-thread-empty">
            <div class="dm-thread-empty-icon" aria-hidden="true">
                <svg viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="48" cy="48" r="44" stroke="currentColor" stroke-width="2" opacity="0.2"/>
                    <path d="M30 44h36M30 52h24" stroke="currentColor" stroke-width="3" stroke-linecap="round" opacity="0.35"/>
                    <path d="M48 24c-8.8 0-16 7.2-16 16v4l-4 6h40l-4-6v-4c0-8.8-7.2-16-16-16z" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round" opacity="0.45"/>
                </svg>
            </div>
            <h2>{{ __('app.messages.title') }}</h2>
            <p>{{ __('app.messages.subtitle') }}</p>
        </div>
    </section>
</div>
@endsection
