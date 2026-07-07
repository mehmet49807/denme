@extends('layouts.app')

@section('body-class', 'page-content')

@section('content')
@php
    $activeLegal = trim($__env->yieldContent('legal-active'));
@endphp
<article class="content-page">
    <header class="content-page-hero">
        <div class="content-page-hero-inner">
            @hasSection('page-eyebrow')
                <p class="content-page-eyebrow">@yield('page-eyebrow')</p>
            @endif
            <h1>@yield('page-title')</h1>
            @hasSection('page-lead')
                <p class="content-page-lead">@yield('page-lead')</p>
            @endif
            <p class="content-page-meta">Son güncelleme: {{ $lastUpdated ?? '5 Haziran 2026' }}</p>
        </div>
    </header>

    <div class="content-page-layout">
        @include('partials.legal-nav', ['active' => $activeLegal])
        <div class="content-prose glass-card">
            @yield('page-content')
            @include('partials.legal-footer')
        </div>
    </div>
</article>
@endsection
