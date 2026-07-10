<button
    type="button"
    class="admin-action-item admin-edit-user-btn"
    role="menuitem"
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
    class="admin-action-item admin-edit-user-btn admin-action-item--warn"
    role="menuitem"
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
<form method="POST" action="{{ route('admin.users.unban', $user) }}" class="admin-action-form" role="none">
    @csrf
    <button type="submit" class="admin-action-item admin-action-item--success" role="menuitem">Banı Kaldır</button>
</form>
@endif

@if($canGrantPremium)
<button
    type="button"
    class="admin-action-item admin-action-item--premium admin-grant-premium-btn"
    role="menuitem"
    data-user-id="{{ $user->id }}"
    data-username="{{ $user->username }}"
    data-is-premium="{{ $user->isPremium() ? '1' : '0' }}"
    data-expires="{{ $activePremium?->expires_at?->format('d.m.Y') ?? '' }}"
>{{ $user->isPremium() ? 'Premium Süresini Uzat' : 'Premium Ekle' }}</button>
@elseif($user->gender === 'female')
<span class="admin-action-item admin-action-item--muted" role="menuitem">Premium gerekmez (kadın)</span>
@endif

<a href="{{ rtrim(config('app.frontend_url', 'https://www.gonulkoprusu.com'), '/') }}/users/{{ $user->username }}" class="admin-action-item" role="menuitem" target="_blank" rel="noopener">Profili Gör</a>

<form
    method="POST"
    action="{{ route('admin.users.destroy', $user) }}"
    class="admin-action-form"
    role="none"
    onsubmit="return confirm('{{ $user->username }} kullanıcısını ve tüm içeriğini (gönderi, hikaye, mesaj vb.) kalıcı olarak silmek istediğinize emin misiniz? Bu işlem geri alınamaz.');"
>
    @csrf
    @method('DELETE')
    @foreach(request()->only(['search', 'gender', 'status', 'page']) as $key => $value)
        @if($value !== null && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
    <button type="submit" class="admin-action-item admin-action-item--danger" role="menuitem">Kullanıcıyı Sil</button>
</form>
