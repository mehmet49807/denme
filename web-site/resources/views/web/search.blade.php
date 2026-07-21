@extends('layouts.content-page')

@section('title', 'Üye Ara — Gönül Köprüsü')
@section('page-eyebrow', 'Keşif')
@section('page-title', 'Üye Ara')
@section('page-lead', 'Kullanıcı adı, şehir veya ilçe ile arama yapın.')

@section('page-content')
<form method="GET" action="{{ route('search') }}" class="search-page-form" role="search" data-search-form data-suggest-url="{{ $suggestUrl }}">
    <label for="search-q" class="sr-only">Arama</label>
    <div class="search-page-field">
        <input
            type="search"
            id="search-q"
            name="q"
            value="{{ $q }}"
            placeholder="Kullanıcı adı, şehir veya ilçe…"
            minlength="2"
            maxlength="80"
            autocomplete="off"
            class="search-page-input"
            enterkeyhint="search"
            data-search-input
            aria-autocomplete="list"
            aria-controls="search-suggest"
            aria-expanded="false"
        >
        <ul id="search-suggest" class="search-page-suggest" role="listbox" hidden data-search-suggest></ul>
    </div>
    <button type="submit" class="btn btn-primary">Ara</button>
</form>

@if($users && $users->total() > 0)
    <p class="search-page-count">{{ number_format($users->total()) }} üye</p>
    <div class="users-browse-grid search-page-grid">
        @include('partials.users-browse-grid-items', ['users' => $users])
    </div>
    @if($users->hasPages())
        <div class="users-browse-pagination">
            {{ $users->links() }}
        </div>
    @endif
@else
    <p class="feed-empty">{{ $emptyMessage }}</p>
@endif

<script>
(function () {
    var form = document.querySelector('[data-search-form]');
    if (!form) return;
    var input = form.querySelector('[data-search-input]');
    var list = form.querySelector('[data-search-suggest]');
    var url = form.getAttribute('data-suggest-url');
    if (!input || !list || !url) return;

    var timer = null;
    var controller = null;

    function hide() {
        list.hidden = true;
        list.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
    }

    function show(items) {
        if (!items || !items.length) {
            hide();
            return;
        }
        list.innerHTML = items.map(function (item, index) {
            var photo = item.profile_photo_url
                ? '<img src="' + String(item.profile_photo_url).replace(/"/g, '') + '" alt="" width="32" height="32" loading="lazy">'
                : '<span class="search-page-suggest__initial">' + String(item.username || '?').charAt(0).toUpperCase() + '</span>';
            var city = item.city ? '<span class="search-page-suggest__city">' + String(item.city) + '</span>' : '';
            return '<li role="option" id="search-opt-' + index + '">' +
                '<a href="' + String(item.url || '#').replace(/"/g, '') + '" class="search-page-suggest__link">' +
                '<span class="search-page-suggest__avatar">' + photo + '</span>' +
                '<span class="search-page-suggest__meta"><strong>' + String(item.username || '') + '</strong>' + city + '</span>' +
                '</a></li>';
        }).join('');
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function fetchSuggest(q) {
        if (controller) controller.abort();
        controller = new AbortController();
        fetch(url + '?q=' + encodeURIComponent(q), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal
        }).then(function (res) { return res.ok ? res.json() : null; })
          .then(function (json) {
              if (!json || !json.success) return hide();
              show(json.data || []);
          }).catch(function (err) {
              if (err && err.name === 'AbortError') return;
              hide();
          });
    }

    input.addEventListener('input', function () {
        var q = String(input.value || '').trim();
        clearTimeout(timer);
        if (q.length < 2) {
            hide();
            return;
        }
        timer = setTimeout(function () { fetchSuggest(q); }, 220);
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hide();
    });

    document.addEventListener('click', function (e) {
        if (!form.contains(e.target)) hide();
    });
})();
</script>
@endsection
