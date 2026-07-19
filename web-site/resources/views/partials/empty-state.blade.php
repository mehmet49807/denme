@php
    $title = $title ?? 'Henüz bir şey yok';
    $text = $text ?? '';
    $icon = $icon ?? 'sparkles';
    $ctaUrl = $ctaUrl ?? null;
    $ctaLabel = $ctaLabel ?? null;
    $class = trim('gk-empty '.($class ?? ''));
@endphp
<div class="{{ $class }}" role="status">
    <div class="gk-empty__icon" aria-hidden="true">
        @include('partials.theme-icon', ['icon' => $icon])
    </div>
    <h2 class="gk-empty__title">{{ $title }}</h2>
    @if($text !== '')
        <p class="gk-empty__text">{{ $text }}</p>
    @endif
    @if($ctaUrl && $ctaLabel)
        <a href="{{ $ctaUrl }}" class="btn btn-primary gk-empty__cta">{{ $ctaLabel }}</a>
    @endif
</div>
