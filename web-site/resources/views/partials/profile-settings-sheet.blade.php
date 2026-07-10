@php
    $initialPanel = old('settings_panel', session('settings_panel', 'menu'));
    if ($errors->any() && $initialPanel === 'menu') {
        if ($errors->has('current_password') || $errors->has('password')) {
            $initialPanel = 'password';
        } elseif ($errors->has('hobbies') || $errors->has('hobbies.*')) {
            $initialPanel = 'hobbies';
        } elseif ($errors->has('locale')) {
            $initialPanel = 'language';
        } else {
            $initialPanel = 'edit';
        }
    }
@endphp

<div
    class="profile-settings-sheet"
    id="profileSettingsSheet"
    hidden
    role="dialog"
    aria-modal="true"
    aria-labelledby="profileSettingsTitle"
    data-initial-panel="{{ $initialPanel }}"
>
    <button type="button" class="profile-settings-sheet-backdrop" data-close-settings aria-label="Kapat"></button>

    <aside class="profile-settings-sheet-panel">
        <header class="profile-settings-sheet-header">
            <button type="button" class="profile-settings-sheet-back" data-settings-back hidden aria-label="Geri">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <h2 class="profile-settings-sheet-title" id="profileSettingsTitle">Ayarlar</h2>
            <button type="button" class="profile-settings-sheet-close" data-close-settings aria-label="Kapat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </header>

        <div class="profile-settings-sheet-body">
            <div class="profile-settings-sheet-stages">
                @include('partials.profile-settings-panels', ['user' => $user])
            </div>
        </div>
    </aside>
</div>
