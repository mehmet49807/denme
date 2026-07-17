@php
    $cssLanding = base_path('css/landing.css');
    $cssHome = base_path('css/homepage-ember.css');
@endphp
@if(is_file($cssLanding))
<style>{!! str_replace('</style>', '<\/style>', file_get_contents($cssLanding)) !!}</style>
@endif
@if(is_file($cssHome))
<style>{!! str_replace('</style>', '<\/style>', file_get_contents($cssHome)) !!}</style>
@endif
