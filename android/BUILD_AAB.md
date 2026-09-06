# app-release.aab üretimi

Hedef URL: `https://gonulkoprusu.com/key/app-release.aab`  
Sunucu klasörü: `public_html/key/`

## 1) Bilgisayarda (Android Studio / CLI)

```bash
git clone https://github.com/mehmet49807/denme.git
cd denme/android

# public_html/key içinden kopyala:
mkdir -p key
# keystore.properties
# release-keystore.jks
# google-services.json → app/google-services.json

# SDK yolu
echo "sdk.dir=$HOME/Android/Sdk" > local.properties

./gradlew :app:bundleRelease
```

Çıktı:
`app/build/outputs/bundle/release/app-release.aab`

## 2) cPanel’e yükle

Dosya Yöneticisi → `public_html/key/` → **Yükle** → `app-release.aab`

veya FTP:
```
ftp.gonulkoprusu.com
uzak: /public_html/key/app-release.aab
```

## 3) Kontrol

https://gonulkoprusu.com/key/app-release.aab

## Not

Grok sandbox’ta Gradle/Kotlin derlemesi bellek yetersizliğinden (daemon OOM) tamamlanamıyor.  
İmza için mevcut `release-keystore.jks` + `keystore.properties` kullanılmalı.
