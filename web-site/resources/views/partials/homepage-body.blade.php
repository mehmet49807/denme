@php
    $memberCount = (int) ($memberCount ?? 0);
    $onlineCount = (int) ($onlineCount ?? 0);
    $memberLabel = $memberCount >= 1000
        ? number_format((int) floor($memberCount / 1000) * 1000, 0, ',', '.').'+'
        : ($memberCount > 0 ? number_format($memberCount, 0, ',', '.') : '10.000+');
    $onlineLabel = $onlineCount > 0 ? number_format($onlineCount, 0, ',', '.') : null;
    $homeFaqs = $homeFaqs ?? [];
    $homeStories = $homeStories ?? [];
@endphp
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
                <p class="gk-lead">Türkiye’nin güvenli <strong>tanışma sitesi</strong>: ücretsiz kayıt, şehir odaklı keşif ve saygılı <strong>online sohbet</strong>.</p>
                <p class="gk-intro-perk" role="note">
                    <strong>Kadınlarda mesajlaşma ücretsiz</strong> — kimler baktı ve galeri de dahil, premium gerekmez.
                </p>
                <div class="gk-stats">
                    <div>
                        <strong>{{ $memberLabel }}</strong>
                        <span>aktif üye</span>
                    </div>
                    @if($onlineLabel)
                    <div>
                        <strong>{{ $onlineLabel }}</strong>
                        <span>çevrimiçi</span>
                    </div>
                    @else
                    <div>
                        <strong>%100</strong>
                        <span>moderasyon</span>
                    </div>
                    @endif
                    <div>
                        <strong>KVKK</strong>
                        <span>uyumlu</span>
                    </div>
                </div>
            </div>
            <div class="gk-intro-visual">
                <div class="gk-intro-frame">
                    <x-optimized-image name="landing-community" alt="Gönül Köprüsü topluluğu" width="640" height="480" loading="lazy" />
                </div>
                <div class="gk-intro-badge">
                    <strong>Ücretsiz</strong>
                    <span>kadın mesaj</span>
                </div>
            </div>
        </div>
    </section>

    <section class="gk-mosaic" aria-label="Özellikler">
        <div class="gk-wrap">
            <header class="gk-section-head">
                <p class="gk-label">Neler sunuyoruz?</p>
                <h2>Ciddi, güvenli, net iletişim</h2>
            </header>
            <div class="gk-mosaic-grid gk-mosaic-grid--three">
                <article class="gk-tile gk-tile--coral">
                    <span class="gk-tile-icon">@include('partials.theme-icon', ['icon' => 'heart'])</span>
                    <h3>Ciddi Üyelik</h3>
                    <p>Evlilik ve ciddi ilişki arayan yetişkinler için tasarlanmış topluluk.</p>
                </article>
                <article class="gk-tile gk-tile--lilac">
                    <span class="gk-tile-icon">@include('partials.theme-icon', ['icon' => 'shield'])</span>
                    <h3>Güvenli Platform</h3>
                    <p>Engelleme, şikayet ve moderasyon süreçleri aktif.</p>
                </article>
                <article class="gk-tile gk-tile--mint">
                    <span class="gk-tile-icon">@include('partials.theme-icon', ['icon' => 'messages'])</span>
                    <h3>Özel Mesajlaşma</h3>
                    <p>Kadınlarda mesaj ücretsiz; güvenli sohbet ortamında tanışın.</p>
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

    @if($homeStories !== [])
    <section class="gk-home-stories" aria-labelledby="gk-home-stories-heading">
        <div class="gk-wrap">
            <header class="gk-section-head">
                <p class="gk-label">Başarı hikâyeleri</p>
                <h2 id="gk-home-stories-heading">Gerçek bağlar, kısa hikâyeler</h2>
            </header>
            <div class="gk-home-stories-grid">
                @foreach($homeStories as $story)
                    <article class="gk-home-story">
                        <p class="gk-home-story__quote">“{{ $story['quote'] }}”</p>
                        <footer>
                            <strong>{{ $story['names'] }}</strong>
                            <span>{{ $story['city'] }}</span>
                        </footer>
                    </article>
                @endforeach
            </div>
            @if(Route::has('stories'))
                <p class="gk-home-stories-more">
                    <a href="{{ route('stories') }}">Tüm başarı hikâyeleri</a>
                </p>
            @endif
        </div>
    </section>
    @endif

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

    @if($homeFaqs !== [])
    <section class="gk-home-faq" aria-labelledby="gk-home-faq-heading">
        <div class="gk-wrap">
            <header class="gk-section-head">
                <p class="gk-label">Sık sorulanlar</p>
                <h2 id="gk-home-faq-heading">Merak edilen 3 soru</h2>
            </header>
            <div class="city-seo-faq gk-home-faq-list">
                @foreach($homeFaqs as $item)
                    <details>
                        <summary>{{ $item['question'] }}</summary>
                        <p>{{ $item['answer'] }}</p>
                    </details>
                @endforeach
            </div>
            <p class="gk-home-faq-more"><a href="{{ route('sss') }}">Tüm SSS</a></p>
        </div>
    </section>
    @endif

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
                <p class="gk-label">Yakınındaki üyeler</p>
                <h2 id="gk-seo-cities-heading">Şehrindeki profilleri keşfet</h2>
                <p class="gk-lead">İstanbul’dan Antalya’ya — şehir sayfasından yakındaki üyeleri gör, ücretsiz kayıt ol.</p>
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
                    <li>
                        <a href="{{ route('city.seo', $cityLink['slug']) }}">
                            <span class="gk-seo-city-name">{{ $cityLink['name'] }}</span>
                            <span class="gk-seo-city-meta">yakındaki üyeler</span>
                        </a>
                    </li>
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
        <div class="gk-wrap gk-cta-box gk-cta-box--simple">
            <div class="gk-cta-glow gk-cta-glow--a" aria-hidden="true"></div>
            <div class="gk-cta-glow gk-cta-glow--b" aria-hidden="true"></div>
            <div class="gk-cta-content">
                <h2>Hemen üye ol</h2>
                <p>Kadınlarda mesaj ücretsiz. Google ile saniyeler içinde başla.</p>
                <div class="gk-cta-fast">
                    <div class="gk-cta-btns">
                        <a href="{{ route('register', ['utm_source' => 'home', 'utm_medium' => 'cta', 'utm_campaign' => 'organic']) }}" class="gk-btn gk-btn--fill" data-gk-event="sign_up_click" data-gk-event-label="home_body_cta">Ücretsiz Üye Ol</a>
                    </div>
                    <p class="gk-cta-fast-divider" aria-hidden="true"><span>veya</span></p>
                    <div class="gk-cta-google-wrap">
                        @include('partials.google-auth-button', [
                            'label' => 'oogle ile devam et',
                            'class' => 'gk-btn gk-btn--fill gk-cta-google',
                            'event' => 'sign_up_click',
                            'eventLabel' => 'home_body_google',
                            'showArrow' => false,
                            'iconSize' => 16,
                            'gate' => true,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endguest

</div>
