# Gönül Köprüsü — Android uygulama

Hedef: **birebir mobil web** tasarımı (Trusted Web Activity / TWA).

## Şu anki adım: Demo (APK yok)

Özel link:

```
https://gonulkoprusu.com/uygulama-demo?key=gk-app-demo-2026
```

Admin: **Büyüme → Uygulama → Android demo**

Demo onayından sonra APK + AAB üretilecek.

## Son adım (henüz yapılmadı): APK + AAB

1. Package adı kararlaştır (ör. `com.gonulkoprusu.app`)
2. Keystore oluştur
3. Bubblewrap ile TWA proje üret:
   - start URL: `https://gonulkoprusu.com/feed` (veya `/login`)
   - manifest: `https://gonulkoprusu.com/manifest.webmanifest`
4. `/.well-known/assetlinks.json` yayınla (SHA-256)
5. `bundleRelease` → AAB, isteğe bağlı APK
6. Play Console internal testing → production

Capacitor alternatifi yalnızca ekstra native özellik gerekirse (şu an TWA yeterli).
