# Gönül Köprüsü — Android (TWA)

Web sitesinin **birebir** mobil deneyimi. Tüm kurallar sunucuda kalır:

- Kadınlarda premium yok / mesaj ücretsiz
- Erkeklerde deneme + premium paketler
- E-posta 2FA + admin onay → doğrulanmış rozet
- Ödeme entegrasyonu yok (mevcut kural)

Package: `com.gonulkoprusu`  
Start URL: `https://gonulkoprusu.com/feed`

## Key dosyaları

Sunucudaki `/key` klasörünü proje köküne kopyala:

```
android/key/keystore.properties
android/key/release-keystore.jks
android/app/google-services.json   # zaten app/ altında
```

`keystore.properties` örneği:

```
storeFile=release-keystore.jks
storePassword=...
keyAlias=gonulkoprusu
keyPassword=...
```

## Derleme

Android Studio veya:

```bash
cd android
# key/ altına jks + properties koy
./gradlew :app:assembleRelease
./gradlew :app:bundleRelease
```

Çıktı:

- `app/build/outputs/apk/release/app-release.apk`
- `app/build/outputs/bundle/release/app-release.aab`

## Digital Asset Links

Canlı site: `https://gonulkoprusu.com/.well-known/assetlinks.json`  
SHA-256 (release keystore):  
`00:73:3D:6B:57:DF:28:71:29:49:88:A1:92:C4:1E:02:26:5C:FE:47:E2:B1:D8:BE:55:41:E7:2D:D0:0B:0D:75`

## Notlar

- TWA için cihazda Chrome gerekir; yoksa `FallbackActivity` WebView açar (ileride bağlanabilir).
- Push için FCM servisi iskeleti var; token API’si sonraki adım.
