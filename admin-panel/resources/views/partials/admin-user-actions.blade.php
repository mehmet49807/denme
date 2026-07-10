@php
    $activePremium = $activePremium ?? $user->premiumSubscriptions->first();
    $canGrantPremium = $canGrantPremium ?? ($user->gender === 'male' && ! $user->is_banned);
    $inviteCount = $inviteCount ?? 0;
    $layout = $layout ?? 'dropdown';
@endphp

@if($layout === 'dropdown')
<div class="admin-action-dropdown" data-dropdown>
    <button type="button" class="admin-action-dropdown-toggle btn btn-outline btn-sm" aria-expanded="false" aria-haspopup="true">
        İşlemler
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
    </button>
    <div class="admin-action-dropdown-menu" role="menu">
        @include('partials.admin-user-action-items', compact('user', 'activePremium', 'canGrantPremium'))
    </div>
</div>
@else
<div class="admin-user-card-actions" role="group" aria-label="{{ $user->username }} işlemleri">
    <button
        type="button"
        class="btn btn-outline btn-sm admin-edit-user-btn"
        data-user-id="{{ $user->id }}"
        data-first-name="{{ $user->first_name }}"
        data-last-name="{{ $user->last_name }}"
        data-email="{{ $user->email }}"
        data-phone="{{ $user->phone }}"
        data-country="{{ $user->country ?? 'Türkiye' }}"
        data-city="{{ $user->city }}"
        data-district="{{ $user->district }}"
        data-is-banned="{{ $user->is_banned ? '1' : '0' }}"
        data-banned-reason="{{ $user->banned_reason ?? '' }}"
        data-username="{{ $user->username }}"
    >Düzenle</button>

    @if(!$user->is_banned)
    <button
        type="button"
        class="btn btn-outline btn-sm admin-edit-user-btn admin-user-card-btn--warn"
        data-user-id="{{ $user->id }}"
        data-first-name="{{ $user->first_name }}"
        data-last-name="{{ $user->last_name }}"
        data-email="{{ $user->email }}"
        data-phone="{{ $user->phone }}"
        data-country="{{ $user->country ?? 'Türkiye' }}"
        data-city="{{ $user->city }}"
        data-district="{{ $user->district }}"
        data-is-banned="1"
        data-banned-reason=""
        data-username="{{ $user->username }}"
    >Banla</button>
    @else
    <form method="POST" action="{{ route('admin.users.unban', $user) }}" class="admin-user-card-form">
        @csrf
        <button type="submit" class="btn btn-outline btn-sm admin-user-card-btn--success">Banı Kaldır</button>
    </form>
    @endif

    @if($canGrantPremium)
    <button
        type="button"
        class="btn btn-outline btn-sm admin-grant-premium-btn admin-user-card-btn--premium"
        data-user-id="{{ $user->id }}"
        data-username="{{ $user->username }}"
        data-is-premium="{{ $user->isPremium() ? '1' : '0' }}"
        data-expires="{{ $activePremium?->expires_at?->format('d.m.Y') ?? '' }}"
    >{{ $user->isPremium() ? 'Premium+' : 'Premium' }}</button>
    @endif

    <a href="{{ rtrim(config('app.frontend_url', 'https://www.gonulkoprusu.com'), '/') }}/users/{{ $user->username }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">Profil</a>

    <form
        method="POST"
        action="{{ route('admin.users.destroy', $user) }}"
        class="admin-user-card-form"
        onsubmit="return confirm('{{ $user->username }} kullanıcısını ve tüm içeriğini kalıcı olarak silmek istediğinize emin misiniz?');"
    >
        @csrf
        @method('DELETE')
        @foreach(request()->only(['search', 'gender', 'status', 'page']) as $key => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <button type="submit" class="btn btn-outline btn-sm admin-user-card-btn--danger">Sil</button>
    </form>
</div>
@endif
