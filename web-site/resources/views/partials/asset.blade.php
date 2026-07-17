@php
    $path = ltrim($path ?? '', '/');
    $type = $type ?? (str_ends_with($path, '.css') ? 'css' : 'js');
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
        $version = substr(hash_file('sha256', $absolute), 0, 12);
    } else {
        $version = (string) time();
    }

    $url = asset($path).'?v='.$version;
@endphp
@if($type === 'css')
<link rel="stylesheet" href="{{ $url }}">
@elseif($defer)
<script src="{{ $url }}" defer></script>
@else
<script src="{{ $url }}"></script>
@endif
