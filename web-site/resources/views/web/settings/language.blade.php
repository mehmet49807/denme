@extends('layouts.app-with-sidebar')

@section('title', 'Dil Seç — ' . __('app.brand'))

@section('app-content')
@php
    $locales = [
        'tr' => ['label' => 'Türkçe', 'emoji' => '🇹🇷'],
        'en' => ['label' => 'English', 'emoji' => '🇬🇧'],
        'de' => ['label' => 'Deutsch', 'emoji' => '🇩🇪'],
        'fr' => ['label' => 'Français', 'emoji' => '🇫🇷'],
        'hi' => ['label' => 'हिन्दी', 'emoji' => '🇮🇳'],
    ];
    $currentLocale = app()->getLocale();
@endphp

<div class="profile-settings-page feed-container">
    @include('partials.settings-page-header', ['title' => 'Dil Seç'])

    <div class="profile-settings-page-body">
        <p class="profile-settings-panel-lead">Profil ve uygulama dili</p>
        <ul class="profile-settings-language-list">
            @foreach($locales as $code => $meta)
                <li>
                    <a
                        href="{{ route('profile.locale', $code) }}"
                        class="profile-settings-language-item {{ $currentLocale === $code ? 'profile-settings-language-item--active' : '' }}"
                        @if($currentLocale === $code) aria-current="true" @endif
                    >
                        <span class="profile-settings-language-flag" aria-hidden="true">{{ $meta['emoji'] }}</span>
                        <span>{{ $meta['label'] }}</span>
                        @if($currentLocale === $code)
                            <span class="profile-settings-language-check" aria-hidden="true">✓</span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>
@endsection
