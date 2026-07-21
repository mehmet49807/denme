@php
    $path = ltrim($path ?? '', '/');
    // Explicit css|js only — never inherit leftover $type from parent views
    // (e.g. premium @foreach($packages as $type => $pkg) was turning CSS into <script>).
    $assetType = str_ends_with($path, '.css') ? 'css' : 'js';
    if (isset($type) && in_array($type, ['css', 'js'], true)) {
        $assetType = $type;
    }
    $defer = ! empty($defer);

    $absolute = null;
    $docRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''), '/\\');
    foreach ([
        public_path($path),
        base_path($path),
        base_path('public/'.$path),
        $docRoot !== '' ? $docRoot.'/'.$path : null,
        $docRoot !== '' ? dirname($docRoot).'/'.$path : null,
    ] as $candidate) {
        if ($candidate && is_file($candidate)) {
            $absolute = $candidate;
            break;
        }
    }

    if ($absolute) {
        // filemtime is far cheaper than hashing the whole file on every HTML render.
        $version = (string) (filemtime($absolute) ?: time());
    } else {
        $version = (string) (config('app.asset_version') ?: time());
    }

    $url = asset($path).'?v='.$version;
@endphp
@if($assetType === 'css')
<link rel="stylesheet" href="{{ $url }}">
@elseif($defer)
<script src="{{ $url }}" defer></script>
@else
<script src="{{ $url }}"></script>
@endif
