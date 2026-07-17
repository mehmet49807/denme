@php
    $compact = !empty($compact);
    $linkPremium = !empty($linkPremium) || (auth()->check() && auth()->id() === $user->id);
    $showPremium = $user->showsPremiumMemberBadge();
    $showTrial = $user->showsTrialBadge();
@endphp

@if($showPremium || $showTrial)
<div class="profile-member-badges {{ $compact ? 'profile-member-badges--compact' : '' }}">
    @if($showPremium)
        @if($linkPremium)
            <a href="{{ route('premium') }}" class="member-badge member-badge--premium" title="{{ __('app.premium.member') }}">
                <span class="member-badge-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l2.2 6.8H21l-5.5 4 2.1 6.7L12 15.8 6.4 19.5l2.1-6.7L3 8.8h6.8L12 2z" fill="currentColor"/></svg>
                </span>
                <span class="member-badge-label">{{ __('app.premium.member') }}</span>
            </a>
        @else
            <span class="member-badge member-badge--premium" title="{{ __('app.premium.member') }}">
                <span class="member-badge-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 2l2.2 6.8H21l-5.5 4 2.1 6.7L12 15.8 6.4 19.5l2.1-6.7L3 8.8h6.8L12 2z" fill="currentColor"/></svg>
                </span>
                <span class="member-badge-label">{{ __('app.premium.member') }}</span>
            </span>
        @endif
    @elseif($showTrial)
        @if($linkPremium)
            <a href="{{ route('premium') }}" class="member-badge member-badge--trial" title="{{ __('app.premium.trial_active') }}">
                <span class="member-badge-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <span class="member-badge-label">{{ __('app.premium.trial', ['days' => $user->trialDaysRemaining()]) }}</span>
            </a>
        @else
            <span class="member-badge member-badge--trial" title="{{ __('app.premium.trial_member') }}">
                <span class="member-badge-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 8v4l3 2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/></svg>
                </span>
                <span class="member-badge-label">{{ __('app.premium.trial', ['days' => $user->trialDaysRemaining()]) }}</span>
            </span>
        @endif
    @endif
</div>
@endif
