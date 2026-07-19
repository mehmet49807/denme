<div class="gk-main">

    <div class="gk-wave" aria-hidden="true">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0,40 C360,90 720,0 1080,40 C1260,60 1380,50 1440,40 L1440,80 L0,80 Z" fill="currentColor"/>
        </svg>
    </div>

    <section class="gk-strip" aria-label="Platform güven göstergeleri">
        <div class="gk-wrap gk-strip-inner">
            <div class="gk-strip-item">
                <span class="gk-strip-icon">@include('partials.theme-icon', ['icon' => 'shield'])</span>
                <span>Moderasyonlu ortam</span>
            </div>
            <div class="gk-strip-item">
                <span class="gk-strip-icon">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                <span>Ciddi üyelik</span>
            </div>
            <div class="gk-strip-item">
                <span class="gk-strip-icon">@include('partials.theme-icon', ['icon' => 'messages'])</span>
                <span>Güvenli mesajlaşma</span>
            </div>
            <div class="gk-strip-item">
                <span class="gk-strip-icon">@include('partials.theme-icon', ['icon' => 'support'])</span>
                <span>7/24 destek</span>
            </div>
        </div>
    </section>

    <section class="gk-intro">
        <div class="gk-wrap gk-intro-layout">
            <div class="gk-intro-text">
                <p class="gk-label">Gönül Köprüsü — tanışma ve sohbet sitesi</p>
                <h2>Evlilik niyetiyle tanışmak için<br>modern ve güvenli platform</h2>
                <p class="gk-lead">Türkiye’nin güvenli <strong>tanışma sitesi</strong>: ücretsiz kayıt, şehir odaklı keşif ve saygılı <strong>online sohbet</strong>. Kaydırmalı uygulamaların hızına değil, ciddi ilişki ve evlilik odaklı bağların kalitesine odaklanıyoruz.</p>
                <div class="gk-stats">
                    <div><strong>10.000+</strong><span>aktif profil</span></div>
                    <div><strong>%100</strong><span>moderasyon</span></div>
                    <div><strong>KVKK</strong><span>uyumlu</span></div>
                </div>
            </div>
            <div class="gk-intro-visual">
                <div class="gk-intro-frame">
                    <x-optimized-image name="landing-community" alt="Gönül Köprüsü topluluğu" width="640" height="480" loading="lazy" />
                </div>
                <div class="gk-intro-badge">
                    <strong>Ücretsiz</strong>
                    <span>kadın üyelik</span>
                </div>
            </div>
        </div>
    </section>

    <section class="gk-mosaic" aria-label="Özellikler">
        <div class="gk-wrap">
            <header class="gk-section-head">
                <p class="gk-label">Neler sunuyoruz?</p>
                <h2>Tanışma süreciniz için eksiksiz araçlar</h2>
            </header>
            <div class="gk-mosaic-grid">
                <article class="gk-tile gk-tile--hero gk-tile--photo">
                    <div class="gk-tile-photo" aria-hidden="true">
                        <x-optimized-image name="landing-community" alt="" width="640" height="800" loading="lazy" />
                    </div>
                    <div class="gk-tile-overlay" aria-hidden="true"></div>
                    <div class="gk-tile-content">
                        <span class="gk-tile-icon">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                        <h3>Ciddi Üyelik</h3>
                        <p>Evlilik ve ciddi ilişki arayan yetişkinler için tasarlanmış topluluk.</p>
                    </div>
                </article>
                <article class="gk-tile gk-tile--coral">
                    <span class="gk-tile-icon">@include('partials.theme-icon', ['icon' => 'shield'])</span>
                    <h3>Güvenli Platform</h3>
                    <p>Engelleme, şikayet ve moderasyon süreçleri aktif.</p>
                </article>
                <article class="gk-tile gk-tile--lilac">
                    <span class="gk-tile-icon">@include('partials.theme-icon', ['icon' => 'messages'])</span>
                    <h3>Özel Mesajlaşma</h3>
                    <p>Güvenli ve özel sohbet ortamında iletişim kurun.</p>
                </article>
                <article class="gk-tile gk-tile--wide gk-tile--photo gk-tile--sunset">
                    <div class="gk-tile-photo" aria-hidden="true">
                        <x-optimized-image name="landing-step-discover" alt="" width="800" height="400" loading="lazy" />
                    </div>
                    <div class="gk-tile-overlay" aria-hidden="true"></div>
                    <div class="gk-tile-content">
                        <span class="gk-tile-icon">@include('partials.theme-icon', ['icon' => 'sparkles'])</span>
                        <h3>Eşleşme Odaklı Keşif</h3>
                        <p>Şehir ve tercihlerinize göre profilleri keşfedin, anlamlı bağlantılar kurun.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section class="gk-path">
        <div class="gk-wrap">
            <header class="gk-section-head gk-section-head--light">
                <p class="gk-label">Başlangıç rehberi</p>
                <h2>3 adımda tanışmaya başlayın</h2>
            </header>
            <ol class="gk-path-list">
                <li class="gk-path-step">
                    <div class="gk-path-num">1</div>
                    <div class="gk-path-card">
                        <div class="gk-path-photo">
                            <x-optimized-image name="landing-step-profile" alt="Profil oluştur" width="360" height="220" loading="lazy" />
                        </div>
                        <div class="gk-path-body">
                            <h3>Profilini Oluştur</h3>
                            <p>Ücretsiz kayıt ol, kendini tanıt ve keşfedilmeye başla.</p>
                        </div>
                    </div>
                </li>
                <li class="gk-path-step">
                    <div class="gk-path-num">2</div>
                    <div class="gk-path-card">
                        <div class="gk-path-photo">
                            <x-optimized-image name="landing-step-discover" alt="Keşfet" width="360" height="220" loading="lazy" />
                        </div>
                        <div class="gk-path-body">
                            <h3>Keşfet & Bağlan</h3>
                            <p>Profilleri incele, beğen ve mesaj gönder.</p>
                        </div>
                    </div>
                </li>
                <li class="gk-path-step">
                    <div class="gk-path-num">3</div>
                    <div class="gk-path-card">
                        <div class="gk-path-photo">
                            <x-optimized-image name="landing-step-meet" alt="Güvenle tanış" width="360" height="220" loading="lazy" />
                        </div>
                        <div class="gk-path-body">
                            <h3>Güvenle Tanış</h3>
                            <p>Anlamlı sohbetler kur, güvenli tanışma kültürünü benimse.</p>
                        </div>
                    </div>
                </li>
            </ol>
        </div>
    </section>

    <section class="gk-reviews">
        <div class="gk-wrap">
            <header class="gk-section-head">
                <p class="gk-label">Üye yorumları</p>
                <h2>Topluluğumuz ne diyor?</h2>
            </header>
            <div class="gk-reviews-track">
                <article class="gk-review gk-review--coral">
                    <div class="gk-review-stars" aria-hidden="true">★★★★★</div>
                    <p>“Samimi bir ortamda tanışmak istiyordum. Gönül Köprüsü tam aradığım platform oldu.”</p>
                    <footer>
                        <x-optimized-image class="gk-review-avatar" name="testimonial-ayse" alt="" width="48" height="48" sizes="48px" />
                        <div><strong>Ayşe</strong><span>İstanbul</span></div>
                    </footer>
                </article>
                <article class="gk-review gk-review--lilac">
                    <div class="gk-review-stars" aria-hidden="true">★★★★★</div>
                    <p>“Güvenlik ve moderasyon konusunda ciddiler. Rahatça mesajlaşabiliyorum.”</p>
                    <footer>
                        <x-optimized-image class="gk-review-avatar" name="testimonial-mehmet" alt="" width="48" height="48" sizes="48px" />
                        <div><strong>Mehmet</strong><span>Ankara</span></div>
                    </footer>
                </article>
                <article class="gk-review gk-review--mint">
                    <div class="gk-review-stars" aria-hidden="true">★★★★★</div>
                    <p>“Yüzeysel kaydırmalar yerine gerçek bağlar kurmak için ideal bir yer.”</p>
                    <footer>
                        <x-optimized-image class="gk-review-avatar" name="testimonial-elif" alt="" width="48" height="48" sizes="48px" />
                        <div><strong>Elif</strong><span>İzmir</span></div>
                    </footer>
                </article>
            </div>
        </div>
    </section>

    <section class="gk-trust">
        <div class="gk-wrap gk-trust-inner">
            <div class="gk-trust-copy">
                <p class="gk-label">Güvenlik & gizlilik</p>
                <h2>Verileriniz ve deneyiminiz koruma altında</h2>
            </div>
            <div class="gk-trust-grid">
                <a href="{{ route('safe-meeting') }}" class="gk-trust-card gk-trust-card--coral">
                    <span>@include('partials.theme-icon', ['icon' => 'shield'])</span>
                    <div>
                        <strong>Profil Doğrulama</strong>
                        <small>Şeffaf profiller</small>
                    </div>
                </a>
                <a href="{{ route('kvkk') }}" class="gk-trust-card gk-trust-card--lilac">
                    <span>@include('partials.theme-icon', ['icon' => 'heart'])</span>
                    <div>
                        <strong>Veri Güvenliği</strong>
                        <small>KVKK uyumlu</small>
                    </div>
                </a>
                <a href="{{ route('complaints') }}" class="gk-trust-card gk-trust-card--mint">
                    <span>@include('partials.theme-icon', ['icon' => 'messages'])</span>
                    <div>
                        <strong>Gizlilik Kontrolü</strong>
                        <small>Engelleme & şikayet</small>
                    </div>
                </a>
                <a href="mailto:destek@gonulkoprusu.com" class="gk-trust-card gk-trust-card--amber">
                    <span>@include('partials.theme-icon', ['icon' => 'support'])</span>
                    <div>
                        <strong>7/24 Destek</strong>
                        <small>destek@gonulkoprusu.com</small>
                    </div>
                </a>
            </div>
            <nav class="gk-trust-links" aria-label="Yasal bağlantılar">
                <a href="{{ route('privacy') }}">Gizlilik Sözleşmesi</a>
                <a href="{{ route('kvkk') }}">KVKK Aydınlatma</a>
                <a href="{{ route('safe-meeting') }}">Güvenli Tanışma Rehberi</a>
            </nav>
        </div>
    </section>

    @php
        $seoCities = \App\Support\FeaturedCities::links(app(\App\Services\LocationDataService::class));
        $seoCitiesTop = array_slice($seoCities, 0, 24);
    @endphp
    <section class="gk-seo-cities" aria-labelledby="gk-seo-cities-heading">
        <div class="gk-wrap">
            <header class="gk-section-head">
                <p class="gk-label">Şehir şehir tanışma</p>
                <h2 id="gk-seo-cities-heading">Türkiye’nin her yerinde güvenli tanışma</h2>
                <p class="gk-lead">İstanbul, Ankara, İzmir ve onlarca şehirde ücretsiz tanışma, sohbet ve evlilik odaklı eşleşme. Şehrini seç, hemen keşfet.</p>
            </header>
            @if(Route::has('seo.marriage') || Route::has('seo.serious') || Route::has('seo.free') || Route::has('seo.friendship'))
            <nav class="gk-seo-pillars" aria-label="Tanışma konuları">
                @if(Route::has('seo.marriage'))
                    <a href="{{ route('seo.marriage') }}">Evlilik sitesi</a>
                @endif
                @if(Route::has('seo.serious'))
                    <a href="{{ route('seo.serious') }}">Ciddi ilişki</a>
                @endif
                @if(Route::has('seo.free'))
                    <a href="{{ route('seo.free') }}">Ücretsiz tanışma</a>
                @endif
                @if(Route::has('seo.friendship'))
                    <a href="{{ route('seo.friendship') }}">Arkadaşlık sitesi</a>
                @endif
            </nav>
            @endif
            <ul class="gk-seo-city-list">
                @foreach($seoCitiesTop as $cityLink)
                    <li><a href="{{ route('city.seo', $cityLink['slug']) }}">{{ $cityLink['name'] }} tanışma</a></li>
                @endforeach
            </ul>
            <p class="gk-seo-city-more">
                <a href="{{ route('blog') }}">Tanışma blogu</a>
                ·
                <a href="{{ route('sss') }}">SSS</a>
                ·
                <a href="{{ route('safe-meeting') }}">Güvenli tanışma</a>
                @if(Route::has('stories'))
                    ·
                    <a href="{{ route('stories') }}">Başarı hikâyeleri</a>
                @endif
                ·
                <a href="{{ route('about') }}">Hakkımızda</a>
            </p>
        </div>
    </section>

    @guest
    <section class="gk-cta">
        <div class="gk-wrap gk-cta-box">
            <div class="gk-cta-glow gk-cta-glow--a" aria-hidden="true"></div>
            <div class="gk-cta-glow gk-cta-glow--b" aria-hidden="true"></div>
            <div class="gk-cta-content">
                <h2>Hikâyen burada başlasın</h2>
                <p>Profilini birkaç dakikada oluştur. Kadın üyelik tamamen ücretsiz.</p>
                <div class="gk-cta-fast">
                    <div class="gk-cta-btns">
                        <a href="{{ route('register', ['utm_source' => 'home', 'utm_medium' => 'cta', 'utm_campaign' => 'organic']) }}" class="gk-btn gk-btn--fill" data-gk-event="sign_up_click" data-gk-event-label="home_body_cta">Ücretsiz Üye Ol</a>
                        <a href="{{ route('login') }}" class="gk-btn gk-btn--ghost">Giriş Yap</a>
                    </div>
                    <p class="gk-cta-fast-divider" aria-hidden="true"><span>veya</span></p>
                    <div class="gk-cta-google-wrap">
                        <a href="{{ url('auth/google') }}" class="gk-cta-google" data-gk-event="sign_up_click" data-gk-event-label="home_body_google">
                            <span class="gk-cta-google__icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="16" height="16">
                                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.36-8.16 2.36-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                                    <path fill="none" d="M0 0h48v48H0z"/>
                                </svg>
                            </span>
                            <span>Google ile devam et</span>
                        </a>
                        <p class="gk-cta-google-note">
                            <span class="gk-cta-google-note__icon" aria-hidden="true">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                            Ücretsiz kayıt ol ve hesabınla saniyeler içinde mesajlaşmaya başla
                        </p>
                    </div>
                </div>
            </div>
            <div class="gk-cta-side">
                @include('partials.store-badges')
                <ul>
                    <li>Ücretsiz kayıt</li>
                    <li>Güvenli mesajlaşma</li>
                    <li>Profil ve keşif</li>
                </ul>
            </div>
        </div>
    </section>
    @endguest

</div>
