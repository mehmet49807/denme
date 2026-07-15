@php
    $profileViews = $profileViews ?? collect();
    $canAccess = $user->canAccessPremiumProfileFeatures();
@endphp

@if($canAccess)
<section class="profile-views" aria-label="Kimler baktı">
    <header class="profile-views-head">
        <h2 class="profile-views-title">
            <span class="profile-views-title-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'eye'])</span>
            <span>Kimler baktı</span>
            <span class="profile-views-count">{{ $profileViews->count() }}</span>
        </h2>
    </header>

    @if($profileViews->isEmpty())
        <p class="profile-views-empty">Henüz profiline bakan kimse yok. Daha aktif oldukça burada görünecekler.</p>
    @else
        <ul class="profile-views-list">
            @foreach($profileViews as $view)
                @continue(!$view->viewer)
                @php $viewer = $view->viewer; @endphp
                <li class="profile-views-item">
                    <a href="{{ route('users.show', $viewer->username) }}" class="profile-views-card">
                        <span class="profile-views-avatar" aria-hidden="true">
                            @if($viewer->profile_photo_url)
                                <img src="{{ $viewer->profile_photo_url }}" alt="" width="48" height="48" loading="lazy" decoding="async">
                            @else
                                <span class="profile-views-avatar-initial">{{ strtoupper(substr($viewer->username, 0, 1)) }}</span>
                            @endif
                        </span>
                        <span class="profile-views-meta">
                            <strong class="profile-views-username">{{ $viewer->username }}</strong>
                            <span class="profile-views-info">
                                {{ $viewer->city ?? ($viewer->country ?? 'Türkiye') }}
                                @if($viewer->district) · {{ $viewer->district }}@endif
                            </span>
                            <span class="profile-views-time">{{ $view->created_at->diffForHumans() }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</section>
@elseif($user->gender === 'male')
<section class="profile-premium-lock" aria-label="Kimler baktı Premium">
    <div class="profile-premium-lock-icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'eye'])</div>
    <div class="profile-premium-lock-copy">
        <h2>Kimler baktı</h2>
        <p>Profiline bakanları görmek Premium üyelere özeldir.</p>
    </div>
    <a href="{{ route('premium') }}" class="btn btn-primary btn-sm">Premium’u incele</a>
</section>
@endif
