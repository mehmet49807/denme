<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetici Girişi — Gönül Köprüsü</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}?v=brand-v17" sizes="32x32" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin-login-lumiere.css') }}?v=premium-v4">
</head>
<body class="admin-login-premium">
    <div class="login-premium-bg" aria-hidden="true">
        <div class="login-premium-orb login-premium-orb--1"></div>
        <div class="login-premium-orb login-premium-orb--2"></div>
        <div class="login-premium-orb login-premium-orb--3"></div>
        <div class="login-premium-orb login-premium-orb--4"></div>
        <div class="login-premium-grid"></div>
    </div>

    <div class="login-premium-screen">
        <aside class="login-premium-visual">
            <div class="login-premium-visual-inner">
                <a href="https://gonulkoprusu.com/" class="login-premium-brand">
                    <img src="{{ asset('images/logo-admin.png') }}?v=brand-v17" alt="Gönül Köprüsü">
                    <span>
                        <strong>Gönül Köprüsü</strong>
                        <span>Premium Yönetim Paneli</span>
                    </span>
                </a>

                <div class="login-premium-visual-copy">
                    <p class="login-premium-eyebrow">Yönetici alanı</p>
                    <h2>Kalpleri birleştiren<br><em>platformu</em> yönetin</h2>
                    <p class="login-premium-lead">Güvenli, modern ve premium yönetim deneyimi. Tüm verileriniz uçtan uca korunur.</p>
                </div>

                <ul class="login-premium-features">
                    <li>
                        <span class="login-premium-feature-icon login-premium-feature-icon--rose">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </span>
                        <span>256-bit SSL güvenliği</span>
                    </li>
                    <li>
                        <span class="login-premium-feature-icon login-premium-feature-icon--gold">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </span>
                        <span>Premium yönetim araçları</span>
                    </li>
                    <li>
                        <span class="login-premium-feature-icon login-premium-feature-icon--violet">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        <span>Kullanıcı &amp; içerik kontrolü</span>
                    </li>
                </ul>
            </div>
            <div class="login-premium-visual-glow" aria-hidden="true"></div>
        </aside>

        <main class="login-premium-main">
            <div class="login-premium-card">
                <div class="login-premium-card-shine" aria-hidden="true"></div>

                <div class="login-premium-header">
                    <div class="login-premium-lock">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <span class="login-premium-badge">Yönetici Erişimi</span>
                    <h1>Hoş Geldiniz</h1>
                    <p>Panele erişmek için bilgilerinizi girin</p>
                </div>

                
                <form method="POST" action="{{ route('admin.login') }}" class="login-premium-form">
                    @csrf
                    @if($errors->any())
                        <div class="login-premium-error" role="alert">{{ $errors->first() }}</div>
                    @endif                    <div class="login-premium-field">
                        <label for="login">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            Kullanıcı adı veya e-posta
                        </label>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" placeholder="admin@gonulkoprusu.com" required autofocus autocomplete="username">
                    </div>
                    <div class="login-premium-field">
                        <label for="password">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Şifre
                        </label>
                        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="login-premium-btn">
                        <span>Panele Giriş</span>
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="5" y1="12" x2="19" y2="12"/>
                            <polyline points="12 5 19 12 12 19"/>
                        </svg>
                    </button>
                </form>

                <div class="login-premium-footer">
                    <div class="login-premium-security">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <span>Güvenli bağlantı ile korunuyor</span>
                    </div>
                    <a href="https://gonulkoprusu.com/" class="login-premium-back">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="19" y1="12" x2="5" y2="12"/>
                            <polyline points="12 19 5 12 12 5"/>
                        </svg>
                        Ana siteye dön
                    </a>
                </div>
            </div>

            <p class="login-premium-copy">&copy; 2026 Gönül Köprüsü · Tüm hakları saklıdır</p>
        </main>
    </div>
</body>
</html>
