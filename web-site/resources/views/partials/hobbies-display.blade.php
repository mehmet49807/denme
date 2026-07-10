@php
    use App\Support\HobbyCatalog;
    $items = $hobbies ?? HobbyCatalog::resolve($user->hobbies ?? null);
@endphp

@if(count($items) > 0)
<ul class="profile-hobbies" aria-label="Hobiler">
    @foreach($items as $hobby)
        <li class="profile-hobby-pill" style="--hobby-color: {{ $hobby['color'] }}">
            <span class="profile-hobby-icon" aria-hidden="true">{{ $hobby['icon'] }}</span>
            <span class="profile-hobby-label">{{ $hobby['label'] }}</span>
        </li>
    @endforeach
</ul>
@endif
