@php
    $href = $href ?? url('auth/google');
    $label = $label ?? 'Google ile devam et';
    $class = trim('btn-google-auth '.($class ?? 'btn btn-primary btn-full btn-google-login btn-google-login--top'));
    $showArrow = $showArrow ?? true;
    $iconSize = $iconSize ?? 20;
@endphp
<a
    href="{{ $href }}"
    class="{{ $class }}"
    @if(!empty($event)) data-gk-event="{{ $event }}" @endif
    @if(!empty($eventLabel)) data-gk-event-label="{{ $eventLabel }}" @endif
>
    <span class="btn-google-login__icon" aria-hidden="true">
        @include('partials.google-icon', ['size' => $iconSize])
    </span>
    <span class="btn-google-login__label">{{ $label }}</span>
    @if($showArrow)
        <span class="btn-google-login__arrow" aria-hidden="true">→</span>
    @endif
</a>
