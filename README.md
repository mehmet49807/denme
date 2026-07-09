# Gönül Köprüsü — Kaynak Depo

Bu depo (`mehmet49807/denme`) **gonulkoprusu.com** web sitesi ve **admin.gonulkoprusu.com** yönetim paneli için kaynak kodları içerir.

## Klasör yapısı

| Klasör | Açıklama |
|--------|----------|
| `web-site/` | Ana site (Laravel parçaları: routes, app, views, images) |
| `admin-panel/` | Yönetim paneli (Laravel parçaları) |
| `deploy/` | FTP deploy manifest |
| `scripts/deploy/` | GitHub Actions deploy scripti |
| `.github/workflows/` | Otomatik deploy workflow |

> Canlı sunucuda tam Laravel kurulumu vardır. Bu depo değişen dosyaları FTP ile senkronize eder.

## Otomatik deploy (GitHub Actions)

`master` dalına her push'ta **Deploy to cPanel** workflow çalışır:

1. `web-site/` → `web@gonulkoprusu.com` (public_html)
2. `admin-panel/` → `panel@admin.gonulkoprusu.com`
3. Deploy sonrası önbellek temizlenir

Manuel tetikleme: GitHub → Actions → Deploy to cPanel → Run workflow (web / admin / all).

### Gerekli GitHub Secrets

Repository → Settings → Secrets and variables → Actions:

| Secret | Değer |
|--------|-------|
| `FTP_WEB_USER` | `web@gonulkoprusu.com` |
| `FTP_WEB_PASSWORD` | Web FTP şifresi |
| `FTP_ADMIN_USER` | `panel@admin.gonulkoprusu.com` |
| `FTP_ADMIN_PASSWORD` | Admin FTP şifresi |
| `SETUP_CACHE_KEY` | `gk-cpanel-setup-2026` |

## Yerel test deploy

```bash
export FTP_WEB_USER='web@gonulkoprusu.com'
export FTP_WEB_PASSWORD='...'
export FTP_ADMIN_USER='panel@admin.gonulkoprusu.com'
export FTP_ADMIN_PASSWORD='...'
export SETUP_CACHE_KEY='gk-cpanel-setup-2026'

python3 scripts/deploy/ftp_sync.py web
python3 scripts/deploy/ftp_sync.py admin
```

## Admin panel — GitHub menüsü

Deploy sonrası admin panelde `/github` sayfası açılır. Yan menüye link eklemek için canlı `layouts/admin.blade.php` içine:

```blade
@include('partials.admin-nav-github-link')
```

veya doğrudan:

```blade
<a href="{{ route('admin.github') }}">GitHub Deploy</a>
```

## Dal stratejisi

- `master` → canlı deploy dalı
- `cursor/*` → geliştirme / PR dalları

Eski FTP patch dosyaları (`patch-*.php`, `gk-*.php`) ve kök dizindeki statik tema dosyaları kaldırıldı; deploy artık yalnızca GitHub Actions üzerinden yapılır.
