# Gol Arena — Android 3D Futbol

Teknik direktör olarak giriş yap, **kendi takımını kur**, logo oluştur; kadroya **gerçek futbolcu isimleri** otomatik atanır. Hazır kulüp markası yok (telif riski azaltılır).

## Özellikler

- **Google / Facebook / Misafir** giriş + teknik direktör adı
- Takım adı (en fazla **3 kelime**), skor tabelası kısa adı (2–4 harf)
- Logo editörü (şekil + renkler)
- Otomatik 11 kişilik kadro (gerçek oyuncu isimleri)
- Telifsiz AI rakip takımlar
- 3D saha, dokunmatik joystick, Pas / Şut / Sprint

## Akış

1. Teknik direktör adını yaz → Google / Facebook / Misafir
2. Takım adı + kısa ad + logo oluştur
3. Kadro otomatik atanır → rakip seç → maç

## Önizleme

```bash
cd football-game
npm install
npm run dev
```

Masaüstü: **WASD** hareket, **J** pas, **Space** şut, **Shift** sprint, **Esc** duraklat.

## Android APK

```bash
bash scripts/build-apk.sh
# → android/app/build/outputs/apk/debug/app-debug.apk
```

## Gerçek Google / Facebook OAuth (opsiyonel)

```bash
cp .env.example .env
# VITE_GOOGLE_CLIENT_ID=...
# VITE_FACEBOOK_APP_ID=...
```

Client ID yoksa butonlar yine çalışır: sağlayıcı + teknik direktör adı ile yerel oturum açılır.

## Not

Futbolcu isimleri tanıtım amaçlıdır. Kulüp adları / logoları kullanıcı tarafından oluşturulur; resmi lig lisansı yoktur.
