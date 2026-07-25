@extends('layouts.admin')

@section('title', 'Reklam')
@section('lead', 'Gerçekçi reklam videoları ve fotoğraflar — izle, indir, Meta / Google / Instagram’a yükle.')

@section('header-actions')
    <a href="{{ $adsBaseUrl }}/" class="btn btn-outline" target="_blank" rel="noopener">Medya klasörü</a>
    <a href="{{ route('admin.marketing') }}" class="btn btn-outline">Pazarlama / UTM</a>
    <a href="{{ $frontendUrl }}/kampanya?utm_source=ads&utm_medium=video&utm_campaign=realistic" class="btn btn-primary" target="_blank" rel="noopener">Ads Landing</a>
@endsection

@section('content')
<div class="admin-ads-page">
<div class="admin-stat-grid admin-stat-grid--compact">
    <div class="admin-stat-card admin-stat-card--violet">
        <div class="admin-stat-value">{{ $videoCount }}</div>
        <div class="admin-stat-label">Video</div>
    </div>
    <div class="admin-stat-card admin-stat-card--coral">
        <div class="admin-stat-value">{{ $photoCount }}</div>
        <div class="admin-stat-label">Fotoğraf</div>
    </div>
    <div class="admin-stat-card admin-stat-card--emerald">
        <div class="admin-stat-value">{{ collect($videos)->where('kind', 'realistic')->count() }}</div>
        <div class="admin-stat-label">Gerçekçi (rx)</div>
    </div>
    <div class="admin-stat-card admin-stat-card--gold">
        <div class="admin-stat-value">{{ collect($videos)->where('kind', 'classic')->count() }}</div>
        <div class="admin-stat-label">Klasik paket</div>
    </div>
</div>

@if(!empty($researchNotes))
<section class="admin-panel admin-panel--glass admin-ads-research">
    <h3 class="admin-panel-title">Araştırma notları (Meta / dating)</h3>
    <ul class="admin-ai-tasks">
        @foreach($researchNotes as $note)
            <li>{{ $note }}</li>
        @endforeach
    </ul>
</section>
@endif

<section class="admin-panel admin-panel--glass" id="reklam-videolari">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Videolar</h3>
            <p class="admin-package-card__sub">
                Kompakt önizleme · 9:16 Reels/Stories · 16:9 YouTube/Display.
            </p>
        </div>
    </header>

    @php
        $videoGroups = collect($videos)->groupBy(function ($v) {
            $kind = $v['kind'] ?? 'classic';
            $fmt = $v['format'] ?? '';
            return ($kind === 'realistic' ? 'Gerçekçi' : 'Klasik').' · '.$fmt;
        });
    @endphp

    @forelse($videoGroups as $group => $items)
        <h4 class="admin-marketing-group">{{ $group }}</h4>
        <div class="admin-ad-list">
            @foreach($items as $video)
                @php $isPortrait = (($video['format'] ?? '') === '9:16'); @endphp
                <article class="admin-ad-row {{ $isPortrait ? 'admin-ad-row--portrait' : 'admin-ad-row--landscape' }}" data-format="{{ $video['format'] }}">
                    <div class="admin-ad-row__thumb">
                        <video
                            class="admin-ad-row__player"
                            controls
                            playsinline
                            preload="metadata"
                            @if(!empty($video['poster_url'])) poster="{{ $video['poster_url'] }}" @endif
                            src="{{ $video['video_url'] }}"
                        ></video>
                        <span class="admin-ad-row__badge">{{ $video['format'] }}</span>
                    </div>
                    <div class="admin-ad-row__body">
                        <div class="admin-ad-row__title-row">
                            <strong>{{ $video['title'] }}</strong>
                            @if(($video['kind'] ?? '') === 'realistic')
                                <span class="admin-ad-row__tag">Gerçekçi</span>
                            @endif
                        </div>
                        @if(!empty($video['subtitle']))
                            <span class="admin-ad-row__sub">{{ $video['subtitle'] }}</span>
                        @endif
                        @if(!empty($video['channel']))
                            <span class="admin-ad-row__meta">{{ $video['channel'] }}</span>
                        @endif
                        <div class="admin-ad-row__actions">
                            <a class="btn btn-primary btn-sm" href="{{ $video['download_url'] }}" download target="_blank" rel="noopener">İndir</a>
                            <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $video['video_url'] }}">URL</button>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @empty
        <p class="admin-package-card__sub">Henüz video yok. Deploy sonrası <code>/images/ads</code> kontrol edin.</p>
    @endforelse
</section>

<section class="admin-panel admin-panel--glass" id="reklam-fotograflari">
    <header class="admin-package-card__head">
        <div>
            <h3 class="admin-panel-title">Fotoğraflar</h3>
            <p class="admin-package-card__sub">Poster, still ve end-card görselleri.</p>
        </div>
    </header>

    <div class="admin-ad-photo-grid">
        @forelse($photos as $photo)
            <article class="admin-ad-photo-card">
                <a href="{{ $photo['url'] }}" target="_blank" rel="noopener" class="admin-ad-photo-card__link">
                    <img src="{{ $photo['url'] }}" alt="{{ $photo['title'] }}" loading="lazy" class="admin-ad-photo-card__img">
                </a>
                <div class="admin-ad-photo-card__body">
                    <strong>{{ $photo['title'] }}</strong>
                    <span>{{ $photo['kind'] }}</span>
                    <div class="admin-ad-row__actions">
                        <a class="btn btn-primary btn-sm" href="{{ $photo['download_url'] }}" download target="_blank" rel="noopener">İndir</a>
                        <button type="button" class="btn btn-outline btn-sm admin-copy-btn" data-copy="{{ $photo['url'] }}">URL</button>
                    </div>
                </div>
            </article>
        @empty
            <p class="admin-package-card__sub">Henüz fotoğraf yok.</p>
        @endforelse
    </div>
</section>

<p class="admin-package-card__sub">
    Yeniden üret: <code>python3 scripts/marketing/build-realistic-ads.py</code>
    · Klasik paket: <code>python3 scripts/marketing/build-website-ads.py</code>
</p>
</div>

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
