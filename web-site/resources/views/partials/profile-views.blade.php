@php
    $profileViews = $profileViews ?? collect();
    $canAccess = $user->canAccessWhoViewed();
    $viewsCount = $profileViews->count();
    $previewViewers = $profileViews->filter(fn ($view) => $view->viewer)->take(4)->values();
    $hasViews = $viewsCount > 0;
@endphp

@if($canAccess)
<details class="pv-panel {{ $hasViews ? 'pv-panel--has-views' : 'pv-panel--empty' }}" @if($hasViews) open @endif>
    <summary class="pv-panel__summary">
        <span class="pv-panel__lead">
            <span class="pv-panel__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'eye'])</span>
            <span class="pv-panel__titles">
                <span class="pv-panel__title">Kimler baktı</span>
                <span class="pv-panel__subtitle">
                    @if($hasViews)
                        Son bakışlar · Platinum
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
            <span class="pv-panel__count" aria-label="{{ $viewsCount }} bakış">{{ $viewsCount }}</span>
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
            <ul class="pv-list">
                @foreach($profileViews as $view)
                    @continue(!$view->viewer)
                    @php
                        $viewer = $view->viewer;
                        $place = collect([$viewer->city, $viewer->district])->filter()->implode(' · ');
                        if ($place === '') {
                            $place = $viewer->country ?: 'Türkiye';
                        }
                    @endphp
                    <li class="pv-list__item">
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
        @endif
    </div>
</details>
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
