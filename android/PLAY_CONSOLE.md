# Play Console kontrol listesi — Gönül Köprüsü

Package: `com.gonulkoprusu`  
Sürüm: 2.0.2 (versionCode 202)

## 1. İmza
- [x] Release keystore: `key/release-keystore.jks`
- [x] Alias: `gonulkoprusu`
- [x] SHA-256 Asset Links: `/.well-known/assetlinks.json`

## 2. Derleme
```bash
cd android
# key/keystore.properties + release-keystore.jks
./gradlew :app:bundleRelease
```
- [ ] AAB: `app/build/outputs/bundle/release/app-release.aab`

## 3. Mağaza
- [ ] Ad: Gönül Köprüsü, dil TR, ücretsiz
- [ ] İkon 512: `android/play/icon-512.png` (logo-mark-lg’den üretildi)
- [ ] Gizlilik: https://gonulkoprusu.com/privacy
- [ ] 18+ dating / sosyal
- [ ] Test hesabı ver
- [ ] Internal → production

## 4. TWA doğrulama
https://digitalassetlinks.googleapis.com/v1/statements:list?source.web.site=https://gonulkoprusu.com&relation=delegate_permission/common.handle_all_urls
