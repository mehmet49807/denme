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
    $showVerified = false;
    try {
        $showVerified = \App\Support\PhotoVerification::isVerified($user);
    } catch (\Throwable) {
        $showVerified = (bool) ($user->email_verified_at && $user->is_verified);
    }
@endphp

@if($showVerified || $showPremium || $showTrial)
<div class="profile-member-badges {{ $compact ? 'profile-member-badges--compact' : '' }}">
    @if($showVerified)
        <span class="member-badge member-badge--verified" title="Doğrulanmış Profil">
            <span class="member-badge-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2.5l1.6 3.8 4.1.4-3.1 2.8.9 4-3.5-2-3.5 2 .9-4-3.1-2.8 4.1-.4L12 2.5z" fill="currentColor"/><path d="m9.2 12.2 1.9 1.9 3.7-3.8" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>
            </span>
            <span class="member-badge-label">Doğrulanmış Profil</span>
        </span>
    @endif
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

@once
@push('head')
<style>
.member-badge--verified {
    background: linear-gradient(135deg, #2563eb 0%, #7c3aed 55%, #db2777 100%);
    color: #fff;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.28);
}
.member-badge--verified .member-badge-icon { color: #fff; }
.profile-email-verification--pending {
    border-color: rgba(124, 58, 237, 0.25);
    background: linear-gradient(135deg, rgba(124,58,237,0.08), rgba(236,72,153,0.06));
}
.profile-email-code-form { display: flex; flex-wrap: wrap; align-items: center; gap: 0.4rem; margin-top: 0.35rem; }
.profile-email-code-form input {
    border: 1.5px solid rgba(124,58,237,0.25);
    border-radius: 12px;
    padding: 0.5rem 0.75rem;
    background: #fff;
}
</style>
@endpush
@endonce
