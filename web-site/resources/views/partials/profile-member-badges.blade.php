@php
    $compact = !empty($compact);
    $linkPremium = !empty($linkPremium) || (auth()->check() && auth()->id() === $user->id);
    $packageBadge = $user->packageBadge();
    $showPremium = $user->showsPremiumMemberBadge();
    $showTrial = $user->showsTrialBadge();
    $badgeLabel = $packageBadge['badge_label'] ?? __('app.premium.member');
    $badgeType = $packageBadge['type'] ?? 'premium';
    $badgeStyle = $packageBadge
        ? '--member-badge-from: '.($packageBadge['gradient_from'] ?? '#7c3aed').'; --member-badge-to: '.($packageBadge['gradient_to'] ?? '#db2777').';'
        : '';
@endphp

@if($showPremium || $showTrial)
<div class="profile-member-badges {{ $compact ? 'profile-member-badges--compact' : '' }}">
    @if($showPremium)
        @if($linkPremium)
            <a href="{{ route('premium') }}" class="member-badge member-badge--{{ $badgeType }}" style="{{ $badgeStyle }}" title="{{ $badgeLabel }}">
                <span class="member-badge-icon" aria-hidden="true">
                    @if($packageBadge)
                        @include('partials.theme-icon', ['icon' => $packageBadge['badge_icon'] ?? 'star'])
                    @else
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l2.2 6.8H21l-5.5 4 2.1 6.7L12 15.8 6.4 19.5l2.1-6.7L3 8.8h6.8L12 2z" fill="currentColor"/></svg>
                    @endif
                </span>
                <span class="member-badge-label">{{ $badgeLabel }}</span>
            </a>
        @else
            <span class="member-badge member-badge--{{ $badgeType }}" style="{{ $badgeStyle }}" title="{{ $badgeLabel }}">
                <span class="member-badge-icon" aria-hidden="true">
                    @if($packageBadge)
                        @include('partials.theme-icon', ['icon' => $packageBadge['badge_icon'] ?? 'star'])
                    @else
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l2.2 6.8H21l-5.5 4 2.1 6.7L12 15.8 6.4 19.5l2.1-6.7L3 8.8h6.8L12 2z" fill="currentColor"/></svg>
                    @endif
                </span>
                <span class="member-badge-label">{{ $badgeLabel }}</span>
            </span>
        @endif
    @elseif($showTrial)