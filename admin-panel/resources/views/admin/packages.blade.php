@extends('layouts.admin')

@section('title', 'Paketler')
@section('lead', 'Pro, Gold ve Platinum paket fiyatlarını ve özel rozetleri yönetin.')

@section('content')
@if(session('success'))
    <div class="admin-flash admin-flash--success">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="admin-flash admin-flash--error">
        <ul class="admin-flash-list">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('admin.packages.update') }}" class="admin-packages-form">
    @csrf

    <div class="admin-packages-grid">
        @foreach($packageTypes as $type)
            @php
                $pkg = $packages[$type] ?? [];
                $tierClass = 'admin-package-card--'.$type;
            @endphp
            <section class="admin-panel admin-panel--glass admin-package-card {{ $tierClass }}">
                <header class="admin-package-card__head">
                    <div class="admin-package-card__preview" style="--pkg-from: {{ $pkg['gradient_from'] ?? '#7c3aed' }}; --pkg-to: {{ $pkg['gradient_to'] ?? '#db2777' }};">
                        <span class="admin-package-card__preview-icon" aria-hidden="true">
                            @switch($pkg['badge_icon'] ?? 'star')
                                @case('crown')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 19h20"/><path d="M4 19V9l4 3 4-6 4 6 4-3v10"/></svg>
                                    @break
                                @case('bolt')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                                    @break
                                @case('heart')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
                                    @break
                                @case('sparkles')
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3l1.2 4.2L17 8.5l-3.8 1.3L12 14l-1.2-4.2L7 8.5l3.8-1.3L12 3z"/></svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @endswitch
                        </span>
                        <span class="admin-package-card__preview-badge">{{ $pkg['badge_label'] ?? strtoupper($type) }}</span>
                    </div>
                    <div>
                        <h3 class="admin-panel-title">{{ strtoupper($type) }}</h3>
                        <p class="admin-package-card__sub">Paket bilgileri ve profil rozeti</p>
                    </div>
                </header>

                <div class="admin-form-grid">
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_name">Paket adı</label>
                        <input type="text" id="pkg_{{ $type }}_name" name="packages[{{ $type }}][name]" value="{{ old("packages.{$type}.name", $pkg['name'] ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_duration">Süre (gün)</label>
                        <input type="number" id="pkg_{{ $type }}_duration" name="packages[{{ $type }}][duration_days]" value="{{ old("packages.{$type}.duration_days", $pkg['duration_days'] ?? 7) }}" min="1" max="365" required>
                    </div>
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_price">Fiyat (TL)</label>
                        <input type="number" id="pkg_{{ $type }}_price" name="packages[{{ $type }}][price_tl]" value="{{ old("packages.{$type}.price_tl", $pkg['price_tl'] ?? 0) }}" min="0" step="1" required>
                    </div>
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_icon">Rozet ikonu</label>
                        <select id="pkg_{{ $type }}_icon" name="packages[{{ $type }}][badge_icon]">
                            @foreach($icons as $iconKey => $iconLabel)
                                <option value="{{ $iconKey }}" @selected(old("packages.{$type}.badge_icon", $pkg['badge_icon'] ?? 'star') === $iconKey)>{{ $iconLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_badge_label">Profil rozet etiketi</label>
                        <input type="text" id="pkg_{{ $type }}_badge_label" name="packages[{{ $type }}][badge_label]" value="{{ old("packages.{$type}.badge_label", $pkg['badge_label'] ?? '') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_rozet_label">Paket rozeti başlığı</label>
                        <input type="text" id="pkg_{{ $type }}_rozet_label" name="packages[{{ $type }}][rozet_label]" value="{{ old("packages.{$type}.rozet_label", $pkg['rozet_label'] ?? '') }}" required>
                    </div>
                    <div class="form-group form-group--full">
                        <label for="pkg_{{ $type }}_rozet_text">Paket rozeti açıklaması</label>
                        <textarea id="pkg_{{ $type }}_rozet_text" name="packages[{{ $type }}][rozet_text]" rows="2">{{ old("packages.{$type}.rozet_text", $pkg['rozet_text'] ?? '') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_from">Gradient başlangıç</label>
                        <input type="color" id="pkg_{{ $type }}_from" name="packages[{{ $type }}][gradient_from]" value="{{ old("packages.{$type}.gradient_from", $pkg['gradient_from'] ?? '#7c3aed') }}">
                    </div>
                    <div class="form-group">
                        <label for="pkg_{{ $type }}_to">Gradient bitiş</label>
                        <input type="color" id="pkg_{{ $type }}_to" name="packages[{{ $type }}][gradient_to]" value="{{ old("packages.{$type}.gradient_to", $pkg['gradient_to'] ?? '#db2777') }}">
                    </div>
                </div>

                <div class="admin-package-card__toggles">
                    <label class="admin-check">
                        <input type="checkbox" name="packages[{{ $type }}][badge_enabled]" value="1" @checked(old("packages.{$type}.badge_enabled", $pkg['badge_enabled'] ?? true))>
                        <span>Profilde rozet göster</span>
                    </label>
                    <label class="admin-check">
                        <input type="radio" name="featured_package" value="{{ $type }}" @checked(old('featured_package', collect($packages)->search(fn ($p) => !empty($p['featured'])) ?: 'gold') === $type)>
                        <span>Öne çıkan paket</span>
                    </label>
                </div>
            </section>
        @endforeach
    </div>

    <div class="admin-form-actions">
        <button type="submit" class="btn btn-primary">Paketleri Kaydet</button>
        <a href="{{ route('admin.app-links') }}" class="btn btn-outline">Android / iOS Linkleri</a>
        <a href="{{ route('admin.premium') }}" class="btn btn-outline">Premium Özetine Dön</a>
    </div>
</form>
@endsection
