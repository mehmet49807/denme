# Gönül Köprüsü — Android uygulaması

Mobil siteyi WebView içinde çalıştırır. **Yalnızca giriş ekranı native**dır; girişten sonra tüm akış `https://gonulkoprusu.com` ile aynıdır.

## Akış

1. Native `LoginActivity` → `POST /api/mobile/login`
2. Sunucu tek kullanımlık handoff URL döner
3. WebView `GET /mobile/session/consume/{code}` açar → web oturum çerezi yazılır → `/feed`
4. Bundan sonra feed, mesajlar, profil vb. tamamen web sitesidir
5. Siteden çıkış / oturum bitince WebView `/login` görür → tekrar native giriş

Google ile giriş, kayıt ve şifremi unuttum WebView üzerinden site sayfalarına gider.

## Android Studio ile derleme

1. [Android Studio](https://developer.android.com/studio) ile `android-app/` klasörünü aç
2. Gradle sync tamamlanınca **Run** (emülatör veya cihaz)
3. Release APK: `Build → Generate Signed Bundle / APK`

Komut satırı (SDK kuruluysa):

```bash
cd android-app
./gradlew assembleDebug
# çıktı: app/build/outputs/apk/debug/app-debug.apk
```

`gradlew` yoksa Android Studio bir kez açıp sync yaptıktan sonra wrapper oluşur; veya:

```bash
gradle wrapper --gradle-version 8.9
./gradlew assembleDebug
```

## Firebase push (opsiyonel)

Site zaten `window.GonulNative.getFcmToken()` bekliyor.

1. Firebase Console → Android uygulaması ekle (`com.gonulkoprusu.app`)
2. `google-services.json` dosyasını `android-app/app/` altına koy
3. `AndroidManifest.xml` içindeki `GonulFirebaseMessagingService` yorumunu kaldır
4. Yeniden derle

Örnek dosya: `app/google-services.json.example`

## Backend

- `POST /api/mobile/login` — JSON: `{ "login", "password", "remember?" }`
- `GET /mobile/session/consume/{code}` — tek kullanımlık, 2 dk, web oturumu açar

Handoff kodları cache’te tutulur (migration gerekmez).

## Notlar

- Oturum çerezleri apex domain içindir: her zaman `https://gonulkoprusu.com` (www değil)
- Admin paneli (`admin.gonulkoprusu.com`) uygulamada yok
- Harici linkler Custom Tabs ile açılır; Google OAuth WebView içinde kalır
