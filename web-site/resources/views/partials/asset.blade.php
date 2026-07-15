@php
    /**
     * Cache-busted asset helper (prefer .min.* built by scripts/build/minify-web-assets.sh).
     * Usage: @include('partials.asset', ['path' => 'css/app.min.css'])
     *         @include('partials.asset', ['path' => 'js/core.min.js'])
     */
    $path = ltrim($path ?? '', '/');
    $type = $type ?? (str_ends_with($path, '.css') ? 'css' : 'js');
    $defer = $defer ?? false;
    $absolute = public_path($path);
    $version = is_file($absolute) ? (string) filemtime($absolute) : '1';
    $url = asset($path).'?v='.$version;
@endphp
@if($type === 'css')
<link rel="stylesheet" href="{{ $url }}">
@else
<script src="{{ $url }}"@if($defer) defer@endif></script>
@endif
