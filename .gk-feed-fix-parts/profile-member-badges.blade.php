@php
    $compact = !empty($compact);
    $linkPremium = !empty($linkPremium) || (auth()->check() && auth()->id() === $user->id);
    $packageBadge = null;
    $showPremium = false;
    $showTrial = false;
    try {
        $packageBadge = $user->packageBadge();
        $showPremium = $user->showsPremiumMemberBadge();
        $showTrial = $user->showsTrialBadge();
    } catch (\Throwable) {
        try {
            $showPremium = $user->gender === 'male' && $user->isPremium();
            $showTrial = method_exists($user, 'showsTrialBadge') ? $user->showsTrialBadge() : false;
        } catch (\Throwable) {
            $showPremium = false;
            $showTrial = false;
        }
    }
    $badgeLabel = is_array($packageBadge) ? ($packageBadge['badge_label'] ?? __('app.premium.member')) : __('app.premium.member');
    $badgeType = is_array($packageBadge) ? ($packageBadge['type'] ?? 'premium') : 'premium';
    $badgeStyle = is_array($packageBadge)
        ? '--member-badge-from: '.($packageBadge['gradient_from'] ?? '#7c3aed').'; --member-badge-to: '.($packageBadge['gradient_to'] ?? '#db2777').';'
        : '';
    $badgeIcon = is_array($packageBadge) ? ($packageBadge['badge_icon'] ?? null) : null;
@endphp

@if($showPremium || $showTrial)
<div class="profile-member-badges {{ $compact ? 'profile-member-badges--compact' : '' }}">
    @if($showPremium)
        @if($linkPremium)
            <a href="{{ route('premium') }}" class="member-badge member-badge--{{ $badgeType }}" style="{{ $badgeStyle }}" title="{{ $badgeLabel }}">
                <span class="member-badge-icon" aria-hidden="true">
                    @if($badgeIcon)
                        @include('partials.theme-icon', ['icon' => $badgeIcon])
                    @else
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2l2.2 6.8H21l-5.5 4 2.1 6.7L12 15.8 6.4 19.5l2.1-6.7L3 8.8h6.8L12 2z" fill="currentColor"/></svg>
                    @endif
                </span>
                <span class="member-badge-label">{{ $badgeLabel }}</span>
            </a>
        @else
            <span class="member-badge member-badge--{{ $badgeType }}" style="{{ $badgeStyle }}" title="{{ $badgeLabel }}">
                <span class="member-badge-icon" aria-hidden="true">
                    @if($badgeIcon)
                        @include('partials.theme-icon', ['icon' => $badgeIcon])
                    @else
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2l2.2 6.8H21l-5.5 4 2.1 6.7L12 15.8 6.4 19.5l2.1-6.7L3 8.8h6.8L12 2z" fill="currentColor"/></svg>
                    @endif
                </span>
                <span class="member-badge-label">{{ $badgeLabel }}</span>
            </span>
        @endif
    @elseif($showTrial)
        @if($linkPremium)
            <a href="{{ route('premium') }}" class="member-badge member-badge--trial" title="{{ __('app.premium.trial_active') }}">
                <span class="member-badge-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <span class="member-badge-label">{{ __('app.premium.trial', ['days' => $user->trialDaysRemaining()]) }}</span>
            </a>
        @else
            <span class="member-badge member-badge--trial" title="{{ __('app.premium.trial_member') }}">
                <span class="member-badge-icon" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <span class="member-badge-label">{{ __('app.premium.trial', ['days' => $user->trialDaysRemaining()]) }}</span>
            </span>
        @endif
    @endif
</div>
@endif
