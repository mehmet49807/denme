<footer class="site-footer" id="iletisim">
    <div class="site-footer-accent" aria-hidden="true"></div>
    <div class="site-footer-inner">
        <div class="site-footer-grid">
            <div class="site-footer-brand">
                @include('partials.logo', ['showTagline' => true])
                <p class="site-footer-desc">
                    Ciddi ilişki arayan yetişkinler için güvenli, saygılı ve modern bir tanışma ortamı.
                </p>
                <div class="site-footer-badges">
                    @include('partials.store-badges')
                </div>
            </div>

            <div class="site-footer-col">
                <h3 class="site-footer-heading">Platform</h3>
                <ul class="site-footer-list">
                    <li><a href="{{ route('home') }}">Ana Sayfa</a></li>
                    <li><a href="{{ route('about') }}">Hakkımızda</a></li>
                    @if(Route::has('seo.marriage'))
                        <li><a href="{{ route('seo.marriage') }}">Evlilik Sitesi</a></li>
                    @endif
                    @if(Route::has('seo.serious'))
                        <li><a href="{{ route('seo.serious') }}">Ciddi İlişki</a></li>
                    @endif
                    @if(Route::has('seo.free'))
                        <li><a href="{{ route('seo.free') }}">Ücretsiz Tanışma</a></li>
                    @endif
                    @if(Route::has('seo.friendship'))
                        <li><a href="{{ route('seo.friendship') }}">Arkadaşlık Sitesi</a></li>
                    @endif
                    @if(Route::has('stories'))
                        <li><a href="{{ route('stories') }}">Başarı Hikâyeleri</a></li>
                    @endif
                    <li><a href="{{ route('safe-meeting') }}">Güvenli Tanışma</a></li>
                    <li><a href="{{ route('blog') }}">Blog</a></li>
                    <li><a href="{{ route('sss') }}">Sıkça Sorulan Sorular (SSS)</a></li>
                    @guest
                        <li><a href="{{ route('register') }}">Ücretsiz Kayıt Ol</a></li>
                        <li><a href="{{ route('login') }}">Giriş Yap</a></li>
                    @endguest
                    @auth
                        <li><a href="{{ route('feed') }}">Akış</a></li>
                        <li><a href="{{ route('messages.index') }}">Mesajlar</a></li>
                    @endauth
                </ul>
            </div>

            <div class="site-footer-col">
                <h3 class="site-footer-heading">Yasal</h3>
                <ul class="site-footer-list">
                    <li><a href="{{ route('privacy') }}">Gizlilik Sözleşmesi</a></li>
                    <li><a href="{{ route('kvkk') }}">KVKK Aydınlatma</a></li>
                    <li><a href="{{ route('terms') }}">Kullanım Koşulları</a></li>
                    <li><a href="{{ route('complaints') }}">Şikayet & Engelleme</a></li>
                </ul>
            </div>

            <div class="site-footer-col">
                <h3 class="site-footer-heading">Destek & Sosyal</h3>
                <ul class="site-footer-list">
                    <li>
                        <a href="mailto:destek@gonulkoprusu.com" class="site-footer-contact">
                            <span class="site-footer-contact-icon">@include('partials.theme-icon', ['icon' => 'messages'])</span>
                            destek@gonulkoprusu.com
                        </a>
                    </li>
                    <li>
                        <a href="{{ \App\Support\InstagramUrl::withUtm('footer', 'site', 'instagram') }}"
                           target="_blank" rel="noopener"
                           data-gk-event="instagram_cta" data-gk-event-label="footer">Instagram — @gonulkoprusucom</a>
                    </li>
                    <li><a href="{{ route('city.seo', 'istanbul') }}">İstanbul tanışma</a></li>
                    <li><a href="{{ route('city.seo', 'ankara') }}">Ankara tanışma</a></li>
                    <li><a href="{{ route('city.seo', 'izmir') }}">İzmir tanışma</a></li>
                    <li><a href="{{ route('city.seo', 'bursa') }}">Bursa tanışma</a></li>
                    <li><a href="{{ route('city.seo', 'antalya') }}">Antalya tanışma</a></li>
                    <li><a href="{{ route('register', ['utm_source' => 'footer', 'utm_medium' => 'cta', 'utm_campaign' => 'organic']) }}" data-gk-event="sign_up_click" data-gk-event-label="footer">Ücretsiz kayıt</a></li>
                </ul>
                <p class="site-footer-support-note">
                    Hesap, güvenlik ve teknik konularda 7/24 e-posta desteği. Bio link: gonulkoprusu.com/register
                </p>
            </div>
        </div>

        <div class="site-footer-bottom">
            <p class="site-footer-copy">&copy; {{ date('Y') }} Gönül Köprüsü. Tüm hakları saklıdır.</p>
            <p class="site-footer-trust">
                <span class="site-footer-trust-icon">@include('partials.theme-icon', ['icon' => 'shield'])</span>
                Güvenli tanışma · Moderasyon · KVKK uyumlu
            </p>
        </div>
    </div>
</footer>
