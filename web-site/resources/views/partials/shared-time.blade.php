@php
    $datetime = $datetime ?? null;
    $label = $label ?? '';
    $title = $title ?? null;
    $timeClass = trim('shared-time__value '.($class ?? ''));
    $wrapClass = trim('shared-time '.($wrap ?? ''));
@endphp
@if($datetime)
<span class="{{ $wrapClass }}"@if($title) title="{{ $title }}"@endif>
    <span class="shared-time__icon" aria-hidden="true">
        @include('partials.theme-icon', ['icon' => 'clock'])
    </span>
    <time
        class="{{ $timeClass }}"
        data-relative-time
        datetime="{{ $datetime }}"
        @if($title) title="{{ $title }}" @endif
    >{{ $label }}</time>
</span>
@endif
