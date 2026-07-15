@php
    $path = ltrim($path ?? '', '/');
    $type = $type ?? (str_ends_with($path, '.css') ? 'css' : 'js');
    $defer = ! empty($defer);
    $absolute = public_path($path);
    $version = is_file($absolute) ? (string) filemtime($absolute) : '1';
    $url = asset($path).'?v='.$version;
@endphp
@if($type === 'css')
<link rel="stylesheet" href="{{ $url }}">
@elseif($defer)
<script src="{{ $url }}" defer></script>
@else
<script src="{{ $url }}"></script>
@endif
