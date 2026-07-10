<div
    class="profile-settings-sheet"
    id="profileSettingsSheet"
    hidden
    role="dialog"
    aria-modal="true"
    aria-labelledby="profileSettingsTitle"
>
    <button type="button" class="profile-settings-sheet-backdrop" data-close-settings aria-label="Kapat"></button>

    <aside class="profile-settings-sheet-panel">
        <header class="profile-settings-sheet-header">
            <span class="profile-settings-sheet-spacer" aria-hidden="true"></span>
            <h2 class="profile-settings-sheet-title" id="profileSettingsTitle">Ayarlar</h2>
            <button type="button" class="profile-settings-sheet-close" data-close-settings aria-label="Kapat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </header>

        <div class="profile-settings-sheet-body">
            @include('partials.profile-settings-menu', ['user' => $user])
        </div>
    </aside>
</div>
