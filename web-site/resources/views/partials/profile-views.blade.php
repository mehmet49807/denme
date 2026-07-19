@php
    $profileViews = $profileViews ?? collect();
    $profileViewsCount = (int) ($profileViewsCount ?? $profileViews->count());
    $canAccess = $user->canAccessWhoViewed();
    $viewsCount = $profileViewsCount;
    $previewViewers = $profileViews->filter(fn ($view) => $view->viewer)->take(4)->values();
    $hasViews = $viewsCount > 0;
    $loadedCount = $profileViews->filter(fn ($view) => $view->viewer)->count();
    $pageSize = 12;
@endphp

@if($canAccess)
<details class="pv-panel {{ $hasViews ? 'pv-panel--has-views' : 'pv-panel--empty' }}" data-pv-panel>
    <summary class="pv-panel__summary">
        <span class="pv-panel__lead">
            <span class="pv-panel__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'eye'])</span>
            <span class="pv-panel__titles">
                <span class="pv-panel__title">Kimler baktı</span>
                <span class="pv-panel__subtitle">
                    @if($hasViews)
                        Kapalı · {{ $viewsCount }} bakış · Platinum
                    @else
                        Henüz bakış yok · Platinum
                    @endif
                </span>
            </span>
        </span>

        <span class="pv-panel__aside">
            @if($previewViewers->isNotEmpty())
                <span class="pv-panel__stack" aria-hidden="true">
                    @foreach($previewViewers as $preview)
                        @php $pv = $preview->viewer; @endphp
                        <span class="pv-panel__stack-avatar">
                            @if($pv->profile_photo_url)
                                <img src="{{ $pv->profile_photo_url }}" alt="" width="28" height="28" loading="lazy" decoding="async">
                            @else
                                {{ strtoupper(substr($pv->username, 0, 1)) }}
                            @endif
                        </span>
                    @endforeach
                </span>
            @endif
            <span class="pv-panel__count" aria-label="{{ $viewsCount }} bakış">{{ $viewsCount > 99 ? '99+' : $viewsCount }}</span>
            <span class="pv-panel__chevron" aria-hidden="true"></span>
        </span>
    </summary>

    <div class="pv-panel__body">
        @if(!$hasViews)
            <div class="pv-empty">
                <span class="pv-empty__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'eye'])</span>
                <p class="pv-empty__title">Henüz kimse bakmadı</p>
                <p class="pv-empty__text">Profilini güncelle, hikaye paylaş veya akışta aktif ol — bakışlar burada listelenir.</p>
            </div>
        @else
            {{-- Liste ilk açılışa kadar inert: fazla üyede ilk paint stabil kalır --}}
            <div class="pv-panel__mount" data-pv-mount hidden></div>
            <template data-pv-template>
                <ul class="pv-list" data-pv-list data-pv-page-size="{{ $pageSize }}">
                    @php $renderIndex = 0; @endphp
                    @foreach($profileViews as $view)
                        @continue(!$view->viewer)
                        @php
                            $viewer = $view->viewer;
                            $place = collect([$viewer->city, $viewer->district])->filter()->implode(' · ');
                            if ($place === '') {
                                $place = $viewer->country ?: 'Türkiye';
                            }
                            $visible = $renderIndex < $pageSize;
                            $renderIndex++;
                        @endphp
                        <li class="pv-list__item{{ $visible ? '' : ' is-collapsed' }}" @unless($visible) hidden @endunless>
                            <a href="{{ route('users.show', $viewer->username) }}" class="pv-card">
                                <span class="pv-card__avatar" aria-hidden="true">
                                    @if($viewer->profile_photo_url)
                                        <img src="{{ $viewer->profile_photo_url }}" alt="" width="52" height="52" loading="lazy" decoding="async">
                                    @else
                                        <span class="pv-card__initial">{{ strtoupper(substr($viewer->username, 0, 1)) }}</span>
                                    @endif
                                    @include('partials.online-status-badge', ['user' => $viewer, 'size' => 'sm'])
                                </span>
                                <span class="pv-card__meta">
                                    <span class="pv-card__top">
                                        <strong class="pv-card__name">{{ $viewer->username }}</strong>
                                        @if(method_exists($viewer, 'age') && $viewer->age())
                                            <span class="pv-card__age">{{ $viewer->age() }}</span>
                                        @endif
                                    </span>
                                    <span class="pv-card__place">{{ $place }}</span>
                                    <span class="pv-card__time">{{ $view->created_at->diffForHumans() }}</span>
                                </span>
                                <span class="pv-card__go" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if($loadedCount > $pageSize)
                    <button type="button" class="pv-more" data-pv-more>
                        Daha fazla göster
                    </button>
                @endif
                @if($viewsCount > $loadedCount)
                    <p class="pv-panel__foot">Son {{ $loadedCount }} bakış gösteriliyor · toplam {{ $viewsCount }}</p>
                @elseif($loadedCount > $pageSize)
                    <p class="pv-panel__foot">Toplam {{ $viewsCount }} bakış</p>
                @endif
            </template>
        @endif
    </div>
</details>
@if($hasViews)
<script>
(function () {
    var panel = document.currentScript && document.currentScript.previousElementSibling;
    if (!panel || !panel.matches || !panel.matches('details[data-pv-panel]')) {
        panel = document.querySelector('details[data-pv-panel]');
    }
    if (!panel || panel.dataset.pvBound === '1') return;
    panel.dataset.pvBound = '1';

    var hydrated = false;
    var subtitle = panel.querySelector('.pv-panel__subtitle');

    function hydrate() {
        if (hydrated) return;
        var mount = panel.querySelector('[data-pv-mount]');
        var tpl = panel.querySelector('template[data-pv-template]');
        if (!mount || !tpl) return;
        mount.appendChild(tpl.content.cloneNode(true));
        mount.hidden = false;
        hydrated = true;
        bindMore(mount);
        if (subtitle) {
            subtitle.textContent = 'Açık · {{ $viewsCount }} bakış · Platinum';
        }
    }

    function bindMore(root) {
        var list = root.querySelector('[data-pv-list]');
        var btn = root.querySelector('[data-pv-more]');
        if (!list || !btn) return;
        var pageSize = parseInt(list.getAttribute('data-pv-page-size') || '12', 10) || 12;

        btn.addEventListener('click', function () {
            var hidden = list.querySelectorAll('.pv-list__item.is-collapsed');
            var i = 0;
            for (; i < hidden.length && i < pageSize; i++) {
                hidden[i].hidden = false;
                hidden[i].classList.remove('is-collapsed');
            }
            if (list.querySelectorAll('.pv-list__item.is-collapsed').length === 0) {
                btn.hidden = true;
            }
        });
    }

    panel.addEventListener('toggle', function () {
        if (panel.open) {
            hydrate();
        } else if (subtitle && hydrated) {
            subtitle.textContent = 'Kapalı · {{ $viewsCount }} bakış · Platinum';
        }
    });
})();
</script>
@endif
@elseif($user->gender === 'male')
<section class="pv-lock" aria-label="Kimler baktı Platinum">
    <div class="pv-lock__visual" aria-hidden="true">
        <span class="pv-lock__icon">@include('partials.theme-icon', ['icon' => 'eye'])</span>
        <span class="pv-lock__badge">Platinum</span>
    </div>
    <div class="pv-lock__copy">
        <h2>Kimler baktı</h2>
        <p>{{ __('app.premium.who_viewed_lock') }}</p>
    </div>
    <a href="{{ route('premium') }}#premium-packages" class="pv-lock__cta">Platinum’u incele</a>
</section>
@endif
