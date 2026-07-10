@extends('layouts.app')

@section('body-class', 'app-shell')
@section('main-class', 'site-main--wide')

@section('content')
<div class="app-layout">
    <div class="app-content">
        @yield('app-content')
    </div>
    <aside class="app-sidebar">
        @include('partials.app-sidebar', ['active' => $activeNav ?? ''])
    </aside>
</div>
@endsection
