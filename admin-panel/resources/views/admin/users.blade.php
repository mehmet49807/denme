@extends('layouts.admin')

@section('title', 'Kullanıcı Yönetimi')
@section('lead', 'Üyeleri arayın, düzenleyin, banlayın veya profillerini görüntüleyin.')

@section('content')
<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value">{{ $stats['total'] }}</div>
        <div class="admin-stat-label">Toplam Üye</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $stats['active'] }}</div>
        <div class="admin-stat-label">Aktif</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ $stats['banned'] }}</div>
        <div class="admin-stat-label">Banlı</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value">{{ $stats['male'] }}</div>
        <div class="admin-stat-label">Erkek</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value">{{ $stats['female'] }}</div>
        <div class="admin-stat-label">Kadın</div>
    </div>
    <div class="admin-stat-card admin-stat-card--blue">
        <div class="admin-stat-value">{{ $stats['invited_total'] ?? 0 }}</div>
        <div class="admin-stat-label">Toplam Davet</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $stats['referred_total'] ?? 0 }}</div>
        <div class="admin-stat-label">Davetle Gelen</div>
    </div>
</div>

<div class="admin-panel admin-panel--glass admin-panel--users">
    <form method="GET" action="{{ route('admin.users') }}" class="admin-users-filter" role="search">
        <div class="admin-users-filter-field admin-users-filter-field--grow">
            <label for="user-search">Ara</label>
            <input
                type="search"
                id="user-search"
                name="search"
                value="{{ request('search') }}"
                placeholder="Kullanıcı adı, e-posta, ad veya şehir…"
                autocomplete="off"
            >
        </div>
        <div class="admin-users-filter-field">
            <label for="user-gender">Cinsiyet</label>
            <select id="user-gender" name="gender" class="admin-users-filter-select">
                <option value="" @selected(! request('gender'))>Tümü</option>
                <option value="male" @selected(request('gender') === 'male')>Erkek</option>
                <option value="female" @selected(request('gender') === 'female')>Kadın</option>
            </select>
        </div>
        <div class="admin-users-filter-field">
            <label for="user-status">Durum</label>
            <select id="user-status" name="status" class="admin-users-filter-select">
                <option value="" @selected(! request('status'))>Tümü</option>
                <option value="active" @selected(request('status') === 'active')>Aktif</option>
                <option value="banned" @selected(request('status') === 'banned')>Banlı</option>
            </select>
        </div>
        <div class="admin-users-filter-actions">
            <button type="submit" class="btn btn-primary btn-sm admin-users-filter-submit">Ara</button>
            @if(request()->filled('search') || request()->filled('gender') || request()->filled('status'))
                <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm">Temizle</a>
            @endif
        </div>
    </form>

    <div class="admin-users-bulk" id="adminUsersBulk" hidden>
        <span class="admin-users-bulk-count"><strong id="adminSelectedCount">0</strong> kullanıcı seçildi</span>
        <button type="button" class="btn btn-primary btn-sm" id="adminBulkPremiumBtn">Seçilenlere Premium Ekle</button>
        <button type="button" class="btn btn-outline btn-sm" id="adminClearSelectionBtn">Seçimi Temizle</button>
    </div>

    <div class="admin-users-desktop admin-table-wrap admin-table-wrap--dropdown">
        <table class="admin-table admin-table--users">
            <thead>
                <tr>
                    <th class="admin-table-check-col">
                        <input type="checkbox" id="adminSelectAllUsers" aria-label="Tümünü seç" title="Tümünü seç">
                    </th>
                    <th>Üye</th>
                    <th>E-posta</th>
                    <th>Konum</th>
                    <th>Cinsiyet</th>
                    <th>Davet</th>
                    <th>Davet Eden</th>
                    <th>Kayıt</th>
                    <th>Durum</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                @php
                    $activePremium = $user->premiumSubscriptions->first();
                    $canGrantPremium = $user->gender === 'male' && !$user->is_banned;
                    $inviteCount = $referralsTableReady ? (int) ($user->referrals_made_count ?? 0) : 0;
                @endphp
                <tr>
                    <td class="admin-table-check-col">
                        @if($canGrantPremium)
                            <input
                                type="checkbox"
                                class="admin-user-select"
                                value="{{ $user->id }}"
                                data-username="{{ $user->username }}"
                                aria-label="{{ $user->username }} seç"
                            >
                        @endif
                    </td>
                    <td>
                        <div class="admin-user-cell">
                            <span class="admin-user-avatar">
                                @if($user->profile_photo_url)
                                    <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username }}" width="36" height="36" loading="lazy" decoding="async">
                                @else
                                    {{ strtoupper(substr($user->username, 0, 1)) }}
                                @endif
                            </span>
                            <span>
                                <strong class="admin-user-name">{{ $user->username }}</strong>
                                <small class="admin-user-meta">{{ $user->first_name }} {{ $user->last_name }} · #{{ $user->id }}</small>
                            </span>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->city }}{{ $user->district ? ', '.$user->district : '' }}</td>
                    <td>
                        <span class="badge badge-gender badge-gender--{{ $user->gender }}">
                            {{ $user->gender === 'male' ? 'Erkek' : 'Kadın' }}
                        </span>
                    </td>
                    <td>
                        @if($user->gender === 'male')
                            <strong>{{ $inviteCount }}</strong>
                            <small class="admin-user-meta">{{ $user->referral_code ?: 'Kod yok' }}</small>
                        @else
                            <span class="admin-user-meta">—</span>
                        @endif
                    </td>
                    <td>{{ $user->referredBy?->username ?? '—' }}</td>
                    <td>{{ $user->created_at?->format('d.m.Y') ?? '—' }}</td>
                    <td>
                        @if($user->is_banned)
                            <span class="badge badge-banned">Banlı</span>
                        @elseif($user->isPremium())
                            <span class="badge badge-premium" title="{{ $activePremium?->expires_at?->format('d.m.Y H:i') }}">
                                Premium{{ $activePremium?->expires_at ? ' · '.$activePremium->expires_at->format('d.m.Y') : '' }}
                            </span>
                        @elseif($user->isOnTrial())
                            <span class="badge badge-pending">Deneme</span>
                        @else
                            <span class="badge badge-resolved">Aktif</span>
                        @endif
                    </td>
                    <td>
                        @include('partials.admin-user-actions', [
                            'user' => $user,
                            'activePremium' => $activePremium,
                            'canGrantPremium' => $canGrantPremium,
                            'layout' => 'dropdown',
                        ])
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="admin-table-empty">Arama kriterlerine uygun kullanıcı bulunamadı.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-users-mobile" aria-label="Üye listesi">
        @forelse($users as $user)
        @php
            $activePremium = $user->premiumSubscriptions->first();
            $canGrantPremium = $user->gender === 'male' && !$user->is_banned;
            $inviteCount = $referralsTableReady ? (int) ($user->referrals_made_count ?? 0) : 0;
        @endphp
        <article class="admin-user-card admin-user-card--open">
            <header class="admin-user-card-summary">
                <span class="admin-user-card-summary-check">
                    @if($canGrantPremium)
                        <input
                            type="checkbox"
                            class="admin-user-select"
                            value="{{ $user->id }}"
                            data-username="{{ $user->username }}"
                            aria-label="{{ $user->username }} seç"
                        >
                    @endif
                </span>
                <span class="admin-user-avatar admin-user-avatar--card">
                    @if($user->profile_photo_url)
                        <img src="{{ $user->profile_photo_url }}" alt="" width="44" height="44" loading="lazy" decoding="async">
                    @else
                        {{ strtoupper(substr($user->username, 0, 1)) }}
                    @endif
                </span>
                <span class="admin-user-card-summary-main">
                    <strong class="admin-user-name">{{ $user->username }}</strong>
                    <small class="admin-user-meta">{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: '—' }} · #{{ $user->id }}</small>
                    <span class="admin-user-card-summary-email">{{ $user->email }}</span>
                </span>
                <span class="admin-user-card-summary-status">
                    @if($user->is_banned)
                        <span class="badge badge-banned">Banlı</span>
                    @elseif($user->isPremium())
                        <span class="badge badge-premium">Premium</span>
                    @elseif($user->isOnTrial())
                        <span class="badge badge-pending">Deneme</span>
                    @else
                        <span class="badge badge-resolved">Aktif</span>
                    @endif
                </span>
            </header>

            <div class="admin-user-card-body">
                <div class="admin-user-card-facts">
                    <div class="admin-user-card-fact">
                        <span class="admin-user-card-fact-label">Konum</span>
                        <span class="admin-user-card-fact-value">{{ $user->city }}{{ $user->district ? ', '.$user->district : '' }}</span>
                    </div>
                    <div class="admin-user-card-fact">
                        <span class="admin-user-card-fact-label">Cinsiyet</span>
                        <span class="admin-user-card-fact-value">
                            <span class="badge badge-gender badge-gender--{{ $user->gender }}">
                                {{ $user->gender === 'male' ? 'Erkek' : 'Kadın' }}
                            </span>
                        </span>
                    </div>
                    <div class="admin-user-card-fact">
                        <span class="admin-user-card-fact-label">Kayıt</span>
                        <span class="admin-user-card-fact-value">{{ $user->created_at?->format('d.m.Y') ?? '—' }}</span>
                    </div>
                    @if($user->gender === 'male')
                    <div class="admin-user-card-fact">
                        <span class="admin-user-card-fact-label">Davet</span>
                        <span class="admin-user-card-fact-value">{{ $inviteCount }} · {{ $user->referral_code ?: '—' }}</span>
                    </div>
                    <div class="admin-user-card-fact admin-user-card-fact--wide">
                        <span class="admin-user-card-fact-label">Davet eden</span>
                        <span class="admin-user-card-fact-value">{{ $user->referredBy?->username ?? '—' }}</span>
                    </div>
                    @endif
                </div>

                @include('partials.admin-user-actions', [
                    'user' => $user,
                    'activePremium' => $activePremium,
                    'canGrantPremium' => $canGrantPremium,
                    'layout' => 'buttons',
                ])
            </div>
        </article>
        @empty
        <p class="admin-users-mobile-empty">Arama kriterlerine uygun kullanıcı bulunamadı.</p>
        @endforelse
    </div>

    {{ $users->links() }}
</div>

<dialog id="adminPremiumDialog" class="admin-dialog admin-dialog--premium">
    <form method="POST" action="{{ route('admin.users.premium') }}" id="adminPremiumForm" class="admin-dialog-form">
        @csrf
        <header class="admin-dialog-header">
            <h2 id="adminPremiumTitle">Premium Ekle</h2>
            <button type="button" class="admin-dialog-close" data-close-premium-dialog aria-label="Kapat">×</button>
        </header>

        <p class="admin-premium-lead" id="adminPremiumLead">Seçili erkek kullanıcılara premium paketi tanımlayın.</p>

        <div class="admin-premium-users" id="adminPremiumUsers" hidden></div>

        <div class="admin-premium-packages">
            @foreach($premiumPackages as $key => $package)
                <label class="admin-premium-package">
                    <input type="radio" name="package_type" value="{{ $key }}" @checked($loop->first) required>
                    <span class="admin-premium-package-card">
                        <strong>{{ $package['name'] }}</strong>
                        <span>{{ $package['duration_days'] }} gün</span>
                        <small>Yönetici tanımı · ücretsiz</small>
                    </span>
                </label>
            @endforeach
        </div>

        <footer class="admin-dialog-footer">
            <button type="button" class="btn btn-outline" data-close-premium-dialog>İptal</button>
            <button type="submit" class="btn btn-primary">Premium Tanımla</button>
        </footer>
    </form>
</dialog>

<dialog id="adminUserEditDialog" class="admin-dialog">
    <form method="POST" id="adminUserEditForm" class="admin-dialog-form">
        @csrf
        @method('PUT')
        <header class="admin-dialog-header">
            <h2 id="adminUserEditTitle">Kullanıcı Düzenle</h2>
            <button type="button" class="admin-dialog-close" data-close-dialog aria-label="Kapat">×</button>
        </header>

        <div class="form-group">
            <label for="edit_username">Kullanıcı adı</label>
            <input type="text" name="username" id="edit_username" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+" autocomplete="off">
            <small class="admin-field-hint">3–50 karakter; yalnızca harf, rakam ve alt çizgi</small>
        </div>
        <div class="form-group">
            <label for="edit_first_name">Ad</label>
            <input type="text" name="first_name" id="edit_first_name" required>
        </div>
        <div class="form-group">
            <label for="edit_last_name">Soyad</label>
            <input type="text" name="last_name" id="edit_last_name" required>
        </div>
        <div class="form-group">
            <label for="edit_email">E-posta</label>
            <input type="email" name="email" id="edit_email" required>
        </div>
        <div class="form-group">
            <label for="edit_phone">Telefon</label>
            <input type="text" name="phone" id="edit_phone">
        </div>
        <div class="form-group">
            <label for="edit_country">Ülke</label>
            <input type="text" name="country" id="edit_country">
        </div>
        <div class="form-group">
            <label for="edit_city">Şehir</label>
            <input type="text" name="city" id="edit_city" required>
        </div>
        <div class="form-group">
            <label for="edit_district">İlçe</label>
            <input type="text" name="district" id="edit_district">
        </div>
        <div class="form-group admin-checkbox-group">
            <label>
                <input type="checkbox" name="is_banned" id="edit_is_banned" value="1">
                Kullanıcıyı banla
            </label>
        </div>
        <div class="form-group" id="edit_banned_reason_wrap">
            <label for="edit_banned_reason">Ban sebebi</label>
            <textarea name="banned_reason" id="edit_banned_reason" rows="3"></textarea>
        </div>

        <footer class="admin-dialog-footer">
            <button type="button" class="btn btn-outline" data-close-dialog>İptal</button>
            <button type="submit" class="btn btn-primary">Kaydet</button>
        </footer>
    </form>
</dialog>

<script>
window.adminPremiumPackages = @json($premiumPackages);

(function () {
    const dialog = document.getElementById('adminUserEditDialog');
    const form = document.getElementById('adminUserEditForm');
    const title = document.getElementById('adminUserEditTitle');
    const bannedWrap = document.getElementById('edit_banned_reason_wrap');
    const bannedCheck = document.getElementById('edit_is_banned');
    const updateUrlTemplate = @json(route('admin.users.update', ['user' => '__ID__']));

    const premiumDialog = document.getElementById('adminPremiumDialog');
    const premiumForm = document.getElementById('adminPremiumForm');
    const premiumTitle = document.getElementById('adminPremiumTitle');
    const premiumLead = document.getElementById('adminPremiumLead');
    const premiumUsers = document.getElementById('adminPremiumUsers');
    const bulkBar = document.getElementById('adminUsersBulk');
    const selectedCountEl = document.getElementById('adminSelectedCount');
    const selectAll = document.getElementById('adminSelectAllUsers');
    const bulkPremiumBtn = document.getElementById('adminBulkPremiumBtn');
    const clearSelectionBtn = document.getElementById('adminClearSelectionBtn');

    function getUserCheckboxes() {
        return Array.from(document.querySelectorAll('.admin-user-select'));
    }

    function getSelectedCheckboxes() {
        return getUserCheckboxes().filter(function (cb) { return cb.checked; });
    }

    function updateBulkBar() {
        const selected = getSelectedCheckboxes();
        const count = selected.length;
        selectedCountEl.textContent = String(count);
        bulkBar.hidden = count === 0;

        const all = getUserCheckboxes();
        if (selectAll) {
            selectAll.indeterminate = count > 0 && count < all.length;
            selectAll.checked = all.length > 0 && count === all.length;
        }
    }

    function clearSelection() {
        getUserCheckboxes().forEach(function (cb) { cb.checked = false; });
        if (selectAll) {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
        updateBulkBar();
    }

    function openPremiumDialog(userIds, labels) {
        closeAllDropdowns();
        premiumForm.querySelectorAll('input[name="user_ids[]"]').forEach(function (el) { el.remove(); });

        userIds.forEach(function (id) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            premiumForm.appendChild(input);
        });

        const isSingle = userIds.length === 1;
        premiumTitle.textContent = isSingle ? labels[0] + ' — Premium' : 'Seçilenlere Premium Ekle';
        premiumLead.textContent = isSingle
            ? 'Bu kullanıcıya premium paketi tanımlayın. Mevcut premium varsa süre uzatılır.'
            : userIds.length + ' erkek kullanıcıya premium paketi tanımlayın.';

        if (labels.length) {
            premiumUsers.hidden = false;
            premiumUsers.innerHTML = labels.map(function (name) {
                return '<span class="admin-premium-user-chip">' + name + '</span>';
            }).join('');
        } else {
            premiumUsers.hidden = true;
            premiumUsers.innerHTML = '';
        }

        premiumDialog.showModal();
    }

    getUserCheckboxes().forEach(function (cb) {
        cb.addEventListener('change', updateBulkBar);
    });

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            getUserCheckboxes().forEach(function (cb) { cb.checked = selectAll.checked; });
            updateBulkBar();
        });
    }

    if (bulkPremiumBtn) {
        bulkPremiumBtn.addEventListener('click', function () {
            const selected = getSelectedCheckboxes();
            if (!selected.length) return;
            openPremiumDialog(
                selected.map(function (cb) { return cb.value; }),
                selected.map(function (cb) { return cb.dataset.username; })
            );
        });
    }

    if (clearSelectionBtn) {
        clearSelectionBtn.addEventListener('click', clearSelection);
    }

    document.querySelectorAll('.admin-grant-premium-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openPremiumDialog([btn.dataset.userId], [btn.dataset.username]);
        });
    });

    document.querySelectorAll('[data-close-premium-dialog]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            if (premiumDialog.open) premiumDialog.close();
        });
    });

    premiumDialog.addEventListener('click', function (e) {
        if (e.target === premiumDialog) premiumDialog.close();
    });

    function closeAllDropdowns() {
        document.querySelectorAll('[data-dropdown]').forEach(function (wrap) {
            wrap.classList.remove('is-open');
            const toggle = wrap.querySelector('.admin-action-dropdown-toggle');
            if (toggle) toggle.setAttribute('aria-expanded', 'false');
        });
    }

    function openDropdown(wrap) {
        closeAllDropdowns();
        wrap.classList.add('is-open');
        const toggle = wrap.querySelector('.admin-action-dropdown-toggle');
        if (toggle) toggle.setAttribute('aria-expanded', 'true');
    }

    document.querySelectorAll('[data-dropdown]').forEach(function (wrap) {
        const toggle = wrap.querySelector('.admin-action-dropdown-toggle');
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            wrap.classList.contains('is-open') ? closeAllDropdowns() : openDropdown(wrap);
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('[data-dropdown]')) closeAllDropdowns();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllDropdowns();
            if (dialog.open) dialog.close();
            if (premiumDialog.open) premiumDialog.close();
        }
    });

    function toggleBannedReason() {
        bannedWrap.hidden = !bannedCheck.checked;
    }

    bannedCheck.addEventListener('change', toggleBannedReason);

    function openEditModal(btn) {
        closeAllDropdowns();
        form.action = updateUrlTemplate.replace('__ID__', btn.dataset.userId);
        title.textContent = btn.dataset.username + ' — Düzenle';
        document.getElementById('edit_username').value = btn.dataset.username || '';
        document.getElementById('edit_first_name').value = btn.dataset.firstName || '';
        document.getElementById('edit_last_name').value = btn.dataset.lastName || '';
        document.getElementById('edit_email').value = btn.dataset.email || '';
        document.getElementById('edit_phone').value = btn.dataset.phone || '';
        document.getElementById('edit_country').value = btn.dataset.country || '';
        document.getElementById('edit_city').value = btn.dataset.city || '';
        document.getElementById('edit_district').value = btn.dataset.district || '';
        bannedCheck.checked = btn.dataset.isBanned === '1';
        document.getElementById('edit_banned_reason').value = btn.dataset.bannedReason || '';
        toggleBannedReason();
        dialog.showModal();
    }

    document.querySelectorAll('.admin-edit-user-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            openEditModal(btn);
        });
    });

    document.querySelectorAll('[data-close-dialog]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            if (dialog.open) dialog.close();
        });
    });

    dialog.addEventListener('click', function (e) {
        if (e.target === dialog) dialog.close();
    });
})();
</script>
@endsection
