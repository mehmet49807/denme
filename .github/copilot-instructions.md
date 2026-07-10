# Copilot — GitHub Otomatik Deploy

Bu depoda canlı deploy **GitHub Actions + FTP** ile yapılır. Manuel `patch-*.php` veya tek tek FTP yüklemesi kullanma.

## Hızlı kurulum (tek seferlik)

1. `.env.deploy.example` dosyasını `.env.deploy` olarak kopyala; FTP şifrelerini doldur.
2. Şu komutu çalıştır:

```bash
bash scripts/setup/github-deploy-all.sh
```

Bu script sırasıyla:
- GitHub Secrets kaydeder (`FTP_WEB_*`, `FTP_ADMIN_*`, `SETUP_CACHE_KEY`)
- `cursor/github-auto-deploy-9613` → `master` PR'ını merge eder
- İlk deploy workflow'unu izler

## Secrets (elle eklenecekse)

| Secret | Değer |
|--------|-------|
| `FTP_WEB_USER` | `web@gonulkoprusu.com` |
| `FTP_WEB_PASSWORD` | Web FTP şifresi |
| `FTP_ADMIN_USER` | `panel@admin.gonulkoprusu.com` |
| `FTP_ADMIN_PASSWORD` | Admin FTP şifresi |
| `SETUP_CACHE_KEY` | `gk-cpanel-setup-2026` |

## Dal stratejisi

- `cursor/*` → geliştirme, PR aç
- `master` → merge sonrası otomatik deploy

## Deploy sonrası

- Web: `https://gonulkoprusu.com/setup/clear-cache?key=gk-cpanel-setup-2026`
- Admin: `https://admin.gonulkoprusu.com/setup/cpanel?key=gk-cpanel-setup-2026`

## Deploy modu

- `master` push → **delta sync** (git diff + FTP boyut kontrolü), web/admin **paralel**
- Actions → Run workflow → `sync_mode: full` ile tüm dosyaları zorla yükle
- `scripts/deploy/ftp_sync.py --full` yerel tam sync

## Copilot'a verilecek örnek görev

> `.env.deploy` dosyasını oluşturup `scripts/setup/github-deploy-all.sh` çalıştır. Secrets kaydedildikten sonra `cursor/github-auto-deploy-9613` dalını `master`'a merge et ve Actions deploy'unun başarılı olduğunu doğrula.
