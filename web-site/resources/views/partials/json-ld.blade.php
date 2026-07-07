@php
    $schema = $schema ?? [];
@endphp
@if(!empty($schema))
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}</script>
@endif
