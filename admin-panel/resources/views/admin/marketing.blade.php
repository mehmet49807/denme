@extends('layouts.admin')

@section('title', 'Pazarlama & Reklam')
@section('lead', 'Reklam videoları, kampanya linkleri, Instagram / Ads UTM’leri ve son 7 gün büyüme metrikleri.')

@section('header-actions')
    <a href="{{ $frontendUrl }}/marketing/ads/" class="btn btn-outline" target="_blank" rel="noopener">Video klasörü</a>
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

<section class="admin-panel admin-panel--glass admin-ad-videos" id="reklam-videolari">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Reklam videoları</h3>
            <p class="admin-package-card__sub">
                Hazır marka videoları — izle, indir, Meta / Google / Instagram’a yükle.
                16:9 web &amp; YouTube · 9:16 Story / Reels. Türkçe seslendirme dahil.
            </p>
        </div>
    </header>

    @php
        $adGroups = collect($adVideos ?? [])->groupBy('group');
    @endphp

    @forelse($adGroups as $group => $videos)
        <h4 class="admin-marketing-group">{{ $group }}</h4>
        <div class="admin-ad-video-grid">
            @foreach($videos as $video)
                <article class="admin-ad-video-card" data-format="{{ $video['format'] }}">
                    <div class="admin-ad-video-card__media">
                        <video
                            class="admin-ad-video-card__player"
                            controls
                            playsinline
                            preload="metadata"
                            poster="{{ $video['poster_url'] }}"
                            src="{{ $video['video_url'] }}"
                        ></video>
                        <span class="admin-ad-video-card__badge">{{ $video['format'] }}</span>
                    </div>
                    <div class="admin-ad-video-card__body">
                        <strong>{{ $video['title'] }}</strong>
                        <span>{{ $video['subtitle'] }}</span>
                        <span class="admin-ad-video-card__meta">{{ $video['channel'] }} · {{ $video['duration_hint'] }}</span>
                        <div class="admin-ad-video-card__actions">
                            <a class="btn btn-primary btn-sm" href="{{ $video['download_url'] }}" download target="_blank" rel="noopener">İndir</a>
                            <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $video['video_url'] }}">Video URL</button>
                            <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $video['cta_url'] }}">CTA link</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @empty
        <p class="admin-package-card__sub">Henüz reklam videosu yok. Deploy sonrası <code>/marketing/ads</code> klasörünü kontrol edin.</p>
    @endforelse

    <p class="admin-package-card__sub admin-ad-videos__hint">
        Yayın: Story/Reels için dikey MP4 + link sticker; YouTube/Display için yatay MP4 + kampanya landing.
        Yeniden üretmek: <code>python3 scripts/marketing/build-website-ads.py</code>
    </p>
</section>

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
        <li><a href="{{ route('admin.ai') }}">AI → Blog / SSS yayınla</a></li>
    </ul>
    <p class="admin-package-card__sub">
        Haftalık ritim: 2 şehir postu · 1 güvenli tanışma · 1 davet story · 1 Ads testi.
        Cron lifecycle: <code>{{ $frontendUrl }}/setup/cron</code>
    </p>
</section>

<script>
(function () {
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', async function () {
            var text = btn.getAttribute('data-copy') || '';
            try {
                await navigator.clipboard.writeText(text);
                var prev = btn.textContent;
                btn.textContent = 'Kopyalandı';
                setTimeout(function () { btn.textContent = prev; }, 1400);
            } catch (e) {
                window.prompt('Kopyala:', text);
            }
        });
    });
})();
</script>
@endsection
