@extends('layouts.admin')

@section('title', 'Pazarlama')
@section('lead', 'Kampanya linkleri, Instagram / Ads UTM’leri ve son 7 gün büyüme metrikleri.')

@section('header-actions')
    <a href="{{ route('admin.ads') }}" class="btn btn-outline">Reklam medya</a>
    <a href="{{ $frontendUrl }}/kampanya?utm_source=meta&utm_medium=paid&utm_campaign=test1" class="btn btn-outline" target="_blank" rel="noopener">Ads Landing</a>
    <a href="{{ route('admin.seo') }}" class="btn btn-primary">SEO Ayarları</a>
@endsection

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

@php $m = $metrics ?? []; @endphp

<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value">{{ $m['signups'] ?? 0 }}</div>
        <div class="admin-stat-label">Kayıt (7 gün)</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value">{{ $m['female'] ?? 0 }} / {{ $m['male'] ?? 0 }}</div>
        <div class="admin-stat-label">Kadın / Erkek</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $m['instagram'] ?? 0 }}</div>
        <div class="admin-stat-label">Instagram UTM</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ $m['paid'] ?? 0 }}</div>
        <div class="admin-stat-label">Paid UTM</div>
    </div>
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value">{{ $m['seo_city'] ?? 0 }}</div>
        <div class="admin-stat-label">Şehir SEO</div>
    </div>
    <div class="admin-stat-card admin-stat-card--blue">
        <div class="admin-stat-value">{{ $m['referred'] ?? 0 }}</div>
        <div class="admin-stat-label">Davetle gelen</div>
    </div>
    <div class="admin-stat-card admin-stat-card--indigo">
        <div class="admin-stat-value">{{ $m['google'] ?? 0 }}</div>
        <div class="admin-stat-label">Google kayıt</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ $m['with_photo'] ?? 0 }}</div>
        <div class="admin-stat-label">Fotoğraflı kayıt</div>
    </div>
</div>

@if(!empty($m['error']))
    <div class="admin-flash admin-flash--error">Metrikler okunamadı: {{ $m['error'] }}</div>
@endif

<section class="admin-panel admin-panel--glass">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Reklam medya kütüphanesi</h3>
            <p class="admin-package-card__sub">
                Gerçekçi stok görüntülü videolar, klasik paket ve tüm fotoğraflar artık
                <strong>Büyüme → Reklam</strong> menüsünde.
            </p>
        </div>
        <a href="{{ route('admin.ads') }}" class="btn btn-primary">Reklam’a git</a>
    </header>
</section>

@php $pack = $instagramPack ?? null; @endphp
@if($pack)
<section class="admin-panel admin-panel--glass admin-instagram-pack">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Instagram Reels / bio paketi</h3>
            <p class="admin-package-card__sub">
                Yeni Reels v2 videoları (ig-01…ig-05) + açıklama/hashtag’ler.
                Videolar: <a href="{{ route('admin.ads') }}">Reklam</a> menüsünden indir.
                UTM kampanya: <code>{{ $defaultCampaign }}</code>
            </p>
        </div>
        <button type="button" class="btn btn-primary" data-copy-from="igPackFull">Paketi kopyala</button>
    </header>
    <textarea id="igPackFull" class="admin-sr-only" readonly hidden aria-hidden="true">{{ $pack['pack_text'] }}</textarea>

    <div class="admin-marketing-link-grid" style="margin-top:0.85rem;">
        <div class="admin-marketing-link-card">
            <div class="admin-marketing-link-card__meta">
                <strong>Bio link</strong>
                <span>Profil bio’suna yapıştır</span>
            </div>
            <code class="admin-marketing-link-card__url">{{ $pack['bio_url'] }}</code>
            <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $pack['bio_url'] }}">Kopyala</button>
        </div>
        <div class="admin-marketing-link-card">
            <div class="admin-marketing-link-card__meta">
                <strong>Story / sticker link</strong>
                <span>Hikâye link sticker</span>
            </div>
            <code class="admin-marketing-link-card__url">{{ $pack['story_url'] }}</code>
            <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $pack['story_url'] }}">Kopyala</button>
        </div>
        <div class="admin-marketing-link-card">
            <div class="admin-marketing-link-card__meta">
                <strong>Kampanya landing</strong>
                <span>/kampanya — Google + e-posta</span>
            </div>
            <code class="admin-marketing-link-card__url">{{ $pack['kampanya_url'] }}</code>
            <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $pack['kampanya_url'] }}">Kopyala</button>
        </div>
    </div>

    <h4 class="admin-marketing-group">Hazır caption’lar</h4>
    <div class="admin-marketing-link-grid">
        @foreach($pack['captions'] as $i => $cap)
            <div class="admin-marketing-link-card">
                <div class="admin-marketing-link-card__meta">
                    <strong>{{ $cap['label'] }}</strong>
                    <span>Instagram gönderi / story metni</span>
                </div>
                <code class="admin-marketing-link-card__url" style="white-space:pre-wrap;">{{ $cap['text'] }}</code>
                <textarea id="igCap{{ $i }}" class="admin-sr-only" readonly hidden aria-hidden="true">{{ $cap['text'] }}</textarea>
                <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy-from="igCap{{ $i }}">Kopyala</button>
            </div>
        @endforeach
    </div>
</section>
@endif

@if(!empty($adsTestPack))
<section class="admin-panel admin-panel--glass">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Ads test paketi (7 gün)</h3>
            <p class="admin-package-card__sub">Düşük bütçeli Meta / Google testi — landing + kontrol listesi.</p>
        </div>
        <button type="button" class="btn btn-primary" data-copy-from="adsPackFull">Paketi kopyala</button>
    </header>
    <textarea id="adsPackFull" class="admin-sr-only" readonly hidden aria-hidden="true">{{ $adsTestPack['pack_text'] }}</textarea>
    <div class="admin-marketing-link-grid" style="margin-top:0.85rem;">
        @foreach($adsTestPack['links'] as $item)
            <div class="admin-marketing-link-card">
                <div class="admin-marketing-link-card__meta">
                    <strong>{{ $item['label'] }}</strong>
                    <span>{{ $item['hint'] }}</span>
                </div>
                <code class="admin-marketing-link-card__url">{{ $item['url'] }}</code>
                <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $item['url'] }}">Kopyala</button>
            </div>
        @endforeach
    </div>
    <ul class="admin-template-list" style="margin-top:0.85rem;">
        @foreach($adsTestPack['checklist'] as $row)
            <li>{{ $row }}</li>
        @endforeach
    </ul>
</section>
@endif

@if(!empty($inviteSharePack))
<section class="admin-panel admin-panel--glass">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Davet WhatsApp / SMS metinleri</h3>
            <p class="admin-package-card__sub">Otomatik SMS yok; WhatsApp ve manuel SMS için hazır metin. Push davet hatırlatması cron ile gider.</p>
        </div>
        <button type="button" class="btn btn-primary" data-copy-from="invitePackFull">Paketi kopyala</button>
    </header>
    <textarea id="invitePackFull" class="admin-sr-only" readonly hidden aria-hidden="true">{{ $inviteSharePack['pack_text'] }}</textarea>
    <div class="admin-marketing-link-grid" style="margin-top:0.85rem;">
        @foreach($inviteSharePack['messages'] as $i => $msg)
            <div class="admin-marketing-link-card">
                <div class="admin-marketing-link-card__meta">
                    <strong>{{ $msg['label'] }}</strong>
                    <span>Kopyala → yapıştır</span>
                </div>
                <code class="admin-marketing-link-card__url" style="white-space:pre-wrap;">{{ $msg['text'] }}</code>
                <textarea id="inviteMsg{{ $i }}" class="admin-sr-only" readonly hidden aria-hidden="true">{{ $msg['text'] }}</textarea>
                <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy-from="inviteMsg{{ $i }}">Kopyala</button>
            </div>
        @endforeach
    </div>
</section>
@endif

@if(!empty($weeklyPlan))
<section class="admin-panel admin-panel--glass">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Haftalık içerik ritmi</h3>
            <p class="admin-package-card__sub">Instagram / organik — günde tek iş.</p>
        </div>
    </header>
    <table class="admin-table" style="margin-top:0.75rem;">
        <thead><tr><th>Gün</th><th>İş</th><th>Link</th></tr></thead>
        <tbody>
            @foreach($weeklyPlan as $row)
                <tr>
                    <td>{{ $row['day'] }}</td>
                    <td>{{ $row['task'] }}</td>
                    <td>
                        <code class="admin-marketing-link-card__url" style="display:inline;">{{ $row['link'] }}</code>
                        <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $row['link'] }}">Kopyala</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</section>
@endif

<section class="admin-panel admin-panel--glass admin-marketing-links">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Hazır kampanya linkleri</h3>
            <p class="admin-package-card__sub">Kopyala → Instagram bio, story sticker veya Meta/Google Ads landing URL.</p>
        </div>
    </header>

    @php
        $groups = collect($links)->groupBy('group');
    @endphp

    @foreach($groups as $group => $items)
        <h4 class="admin-marketing-group">{{ $group }}</h4>
        <div class="admin-marketing-link-grid">
            @foreach($items as $item)
                <div class="admin-marketing-link-card">
                    <div class="admin-marketing-link-card__meta">
                        <strong>{{ $item['label'] }}</strong>
                        <span>{{ $item['hint'] }}</span>
                    </div>
                    <code class="admin-marketing-link-card__url" data-copy-source>{{ $item['url'] }}</code>
                    <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $item['url'] }}">Kopyala</button>
                </div>
            @endforeach
        </div>
    @endforeach
</section>

<section class="admin-panel admin-panel--glass">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Sosyal & kampanya ayarları</h3>
            <p class="admin-package-card__sub">Instagram hesabı sitedeki tüm CTA’larda kullanılır.</p>
        </div>
    </header>

    <form method="POST" action="{{ route('admin.marketing.update') }}" class="admin-marketing-form">
        @csrf
        <div class="admin-form-grid">
            <div class="form-group form-group--full">
                <label for="instagram_url">Instagram URL</label>
                <input type="url" id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $instagramUrl) }}" placeholder="https://www.instagram.com/gonulkoprusucom">
            </div>
            <div class="form-group form-group--full">
                <label for="facebook_url">Facebook URL (opsiyonel)</label>
                <input type="url" id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $facebookUrl) }}" placeholder="https://www.facebook.com/...">
            </div>
            <div class="form-group">
                <label for="marketing_default_campaign">Varsayılan kampanya adı</label>
                <input type="text" id="marketing_default_campaign" name="marketing_default_campaign" value="{{ old('marketing_default_campaign', $defaultCampaign) }}" placeholder="organic">
            </div>
            <div class="form-group form-group--full">
                <label for="marketing_notes">Notlar (iç ekip)</label>
                <textarea id="marketing_notes" name="marketing_notes" rows="4" placeholder="Haftalık plan, bütçe, kreatıf notları...">{{ old('marketing_notes', $marketingNotes) }}</textarea>
            </div>
        </div>
        <div class="admin-form-actions">
            <button type="submit" class="btn btn-primary">Kaydet</button>
            <a href="{{ route('admin.app-links') }}" class="btn btn-outline">Uygulama Linkleri</a>
            <a href="{{ route('admin.packages') }}" class="btn btn-outline">Paketler</a>
        </div>
    </form>
</section>

<section class="admin-panel admin-panel--glass">
    <h3 class="admin-panel-title">Hızlı bağlantılar</h3>
    <ul class="admin-ai-tasks">
        <li><a href="{{ $frontendUrl }}" target="_blank" rel="noopener">Canlı site</a></li>
        <li><a href="{{ $instagramUrl }}" target="_blank" rel="noopener">Instagram hesabı</a></li>
        <li><a href="{{ route('admin.seo') }}">SEO & Google (GA / GTM / sitemap)</a></li>
        <li><a href="{{ route('admin.emails') }}">E-posta kampanyaları</a></li>
        <li><a href="{{ route('admin.broadcasts') }}">Duyurular</a></li>
        <li><a href="{{ route('admin.referrals') }}">Davet / Referans</a></li>
            </ul>
    <p class="admin-package-card__sub">
        Haftalık ritim: 2 şehir postu · 1 güvenli tanışma · 1 davet story · 1 Ads testi.
        Cron lifecycle: <code>{{ $frontendUrl }}/setup/cron</code>
    </p>
</section>

<script>
(function () {
    async function copyText(btn, text) {
        try {
            await navigator.clipboard.writeText(text);
            var prev = btn.textContent;
            btn.textContent = 'Kopyalandı';
            setTimeout(function () { btn.textContent = prev; }, 1400);
        } catch (e) {
            window.prompt('Kopyala:', text);
        }
    }

    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            copyText(btn, btn.getAttribute('data-copy') || '');
        });
    });

    document.querySelectorAll('[data-copy-from]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-copy-from') || '';
            var el = id ? document.getElementById(id) : null;
            copyText(btn, el ? (el.value || el.textContent || '') : '');
        });
    });
})();
</script>
@endsection
