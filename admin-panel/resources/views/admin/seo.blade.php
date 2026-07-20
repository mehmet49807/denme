@extends('layouts.admin')

@section('title', 'SEO & Google Arama')
@section('lead', 'Site meta etiketleri, Google Analytics, Search Console doğrulama ve sitemap ayarları.')

@section('content')
<div class="admin-seo-page">
    <div class="admin-seo-hero">
        <div class="admin-seo-hero-badges" aria-hidden="true">
            <span class="admin-seo-dot admin-seo-dot--blue"></span>
            <span class="admin-seo-dot admin-seo-dot--red"></span>
            <span class="admin-seo-dot admin-seo-dot--yellow"></span>
            <span class="admin-seo-dot admin-seo-dot--green"></span>
        </div>
        <span class="admin-seo-hero-icon" aria-hidden="true">🔍</span>
        <div class="admin-seo-hero-copy">
            <strong>Arama motoru görünürlüğü</strong>
            <span>Ana sitede meta etiketler, Google izleme kodları ve yapılandırılmış veri bu ayarlardan beslenir.</span>
        </div>
    </div>

    <div class="admin-seo-links">
        <a href="{{ $sitemapUrl }}" target="_blank" rel="noopener" class="admin-seo-link-card admin-seo-link-card--sitemap">
            <span class="admin-seo-link-icon">🗺️</span>
            <strong>Sitemap</strong>
            <span>{{ $sitemapUrl }}</span>
        </a>
        <a href="{{ $robotsUrl }}" target="_blank" rel="noopener" class="admin-seo-link-card admin-seo-link-card--robots">
            <span class="admin-seo-link-icon">🤖</span>
            <strong>robots.txt</strong>
            <span>{{ $robotsUrl }}</span>
        </a>
        <a href="{{ $searchUrl }}" target="_blank" rel="noopener" class="admin-seo-link-card admin-seo-link-card--search">
            <span class="admin-seo-link-icon">👥</span>
            <strong>Üye Arama</strong>
            <span>{{ $searchUrl }}</span>
        </a>
        <form method="POST" action="{{ route('admin.seo.clear-sitemap') }}" class="admin-seo-link-card admin-seo-link-card--action admin-seo-link-card--cache">
            @csrf
            <button type="submit" class="admin-seo-cache-btn">
                <span class="admin-seo-link-icon">⚡</span>
                <strong>Sitemap önbelleğini temizle</strong>
                <span>Değişiklikler hemen yansısın</span>
            </button>
        </form>
        <form method="POST" action="{{ route('admin.seo.openrouter-refresh') }}" class="admin-seo-link-card admin-seo-link-card--action admin-seo-link-card--openrouter">
            @csrf
            <button type="submit" class="admin-seo-cache-btn">
                <span class="admin-seo-link-icon">🤖</span>
                <strong>OpenRouter Türkçe SEO Güncelle</strong>
                <span>
                    Haftalık görev: Pazartesi 09:20 · Model: {{ $openRouterModel }}
                    @if($openRouterLastUpdated)
                        · Son: {{ $openRouterLastUpdated }}
                    @endif
                    @unless($openRouterConfigured)
                        · API anahtarı bekleniyor
                    @endunless
                </span>
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.seo.update') }}" class="admin-seo-form">
        @csrf

        <div class="admin-panel admin-panel--glass admin-seo-panel admin-seo-panel--general">
            <h3 class="admin-seo-panel-title">
                <span class="admin-seo-panel-badge admin-seo-panel-badge--teal">🌐</span>
                Genel SEO
            </h3>
            <div class="admin-seo-grid">
                <div class="form-group">
                    <label for="site_name">Site adı</label>
                    <input type="text" id="site_name" name="site_name" value="{{ old('site_name', $settings['site_name'] ?? '') }}" required maxlength="120">
                </div>
                <div class="form-group">
                    <label for="site_url">Site adresi (canonical taban)</label>
                    <input type="url" id="site_url" name="site_url" value="{{ old('site_url', $settings['site_url'] ?? '') }}" required maxlength="255">
                </div>
                <div class="form-group admin-seo-grid-full">
                    <label for="default_description">Varsayılan meta açıklama</label>
                    <textarea id="default_description" name="default_description" rows="3" required maxlength="500">{{ old('default_description', $settings['default_description'] ?? '') }}</textarea>
                    <p class="admin-field-hint">Önerilen: 150–160 karakter</p>
                </div>
                <div class="form-group admin-seo-grid-full">
                    <label for="default_keywords">Varsayılan anahtar kelimeler</label>
                    <input type="text" id="default_keywords" name="default_keywords" value="{{ old('default_keywords', $settings['default_keywords'] ?? '') }}" maxlength="500">
                </div>
                <div class="form-group admin-seo-grid-full">
                    <label for="og_image_url">Open Graph görsel URL</label>
                    <input type="url" id="og_image_url" name="og_image_url" value="{{ old('og_image_url', $settings['og_image_url'] ?? '') }}" maxlength="500" placeholder="https://www.gonulkoprusu.com/images/og-default.jpg">
                </div>
                <div class="form-group">
                    <label for="twitter_handle">Twitter / X kullanıcı adı</label>
                    <input type="text" id="twitter_handle" name="twitter_handle" value="{{ old('twitter_handle', $settings['twitter_handle'] ?? '') }}" maxlength="80" placeholder="@gonulkoprusu">
                </div>
                <div class="form-group">
                    <label for="support_email">Destek e-postası (Schema.org)</label>
                    <input type="email" id="support_email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? '') }}" maxlength="255">
                </div>
            </div>
        </div>

        <div class="admin-panel admin-panel--glass admin-seo-panel admin-seo-panel--google">
            <h3 class="admin-seo-panel-title">
                <span class="admin-seo-panel-badge admin-seo-panel-badge--google">📊</span>
                Google Arama & İzleme
            </h3>
            <div class="admin-seo-grid">
                <div class="form-group">
                    <label for="google_analytics_id">Google Analytics 4 (Measurement ID)</label>
                    <input type="text" id="google_analytics_id" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}" maxlength="40" placeholder="G-XXXXXXXXXX">
                    @error('google_analytics_id') <small class="form-error">{{ $message }}</small> @enderror
                </div>
                <div class="form-group">
                    <label for="google_tag_manager_id">Google Tag Manager (isteğe bağlı)</label>
                    <input type="text" id="google_tag_manager_id" name="google_tag_manager_id" value="{{ old('google_tag_manager_id', $settings['google_tag_manager_id'] ?? '') }}" maxlength="40" placeholder="GTM-XXXXXXX">
                    @error('google_tag_manager_id') <small class="form-error">{{ $message }}</small> @enderror
                </div>
                <div class="form-group admin-seo-grid-full">
                    <label for="google_site_verification">Google Search Console doğrulama kodu</label>
                    <input type="text" id="google_site_verification" name="google_site_verification" value="{{ old('google_site_verification', $settings['google_site_verification'] ?? '') }}" maxlength="120" placeholder="google-site-verification meta content değeri">
                    <p class="admin-field-hint">Search Console → Ayarlar → Mülk doğrulama → HTML etiketi → content="" içindeki kod</p>
                </div>
                <div class="form-group admin-seo-grid-full">
                    <label for="bing_site_verification">Bing Webmaster doğrulama kodu (isteğe bağlı)</label>
                    <input type="text" id="bing_site_verification" name="bing_site_verification" value="{{ old('bing_site_verification', $settings['bing_site_verification'] ?? '') }}" maxlength="120">
                </div>
            </div>
        </div>

        <div class="admin-panel admin-panel--glass admin-seo-panel admin-seo-panel--social">
            <h3 class="admin-seo-panel-title">
                <span class="admin-seo-panel-badge admin-seo-panel-badge--social">💜</span>
                Sosyal profiller & indeksleme
            </h3>
            <div class="admin-seo-grid">
                <div class="form-group">
                    <label for="instagram_url">Instagram</label>
                    <input type="url" id="instagram_url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}" maxlength="255">
                </div>
                <div class="form-group">
                    <label for="facebook_url">Facebook</label>
                    <input type="url" id="facebook_url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}" maxlength="255">
                </div>
                <div class="form-group admin-seo-grid-full">
                    <label for="twitter_url">Twitter / X profil URL</label>
                    <input type="url" id="twitter_url" name="twitter_url" value="{{ old('twitter_url', $settings['twitter_url'] ?? '') }}" maxlength="255">
                </div>
                <div class="form-group admin-seo-check admin-seo-check--index">
                    <label class="admin-seo-check-label">
                        <input type="checkbox" name="robots_index" value="1" @checked(old('robots_index', ($settings['robots_index'] ?? '1') === '1'))>
                        <span>Arama motorları sayfaları indekslesin (index, follow)</span>
                    </label>
                </div>
                <div class="form-group admin-seo-check admin-seo-check--sitemap">
                    <label class="admin-seo-check-label">
                        <input type="checkbox" name="sitemap_enabled" value="1" @checked(old('sitemap_enabled', ($settings['sitemap_enabled'] ?? '1') === '1'))>
                        <span>Sitemap.xml aktif (kapalıyken 404 döner)</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="admin-seo-actions">
            <button type="submit" class="btn btn-primary admin-seo-save-btn">Ayarları Kaydet</button>
        </div>
    </form>
</div>
@endsection
