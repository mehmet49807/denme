# Gol Arena — Android 3D Futbol

Dokunmatik kontrollü 3D futbol oyunu. Stilize oyuncu modelleri, stadyum atmosferi ve **gerçek oyuncu isimleri** (Süper Lig + Avrupa kulüpleri).

## Özellikler

- 8 takım, 11’er gerçek isimli kadro
- 3D saha, kale, tribün / taraftar şeridi
- Joystick + Pas / Şut / Sprint
- 90 dakikalık maç (hızlandırılmış), skor, gol animasyonu
- Capacitor ile Android APK / AAB

## Hızlı önizleme (tarayıcı)

```bash
cd football-game
npm install
npm run dev
```

Masaüstünde: **WASD / oklar** hareket, **J** pas, **Space** şut, **Shift** sprint, **Esc** duraklat.

## Hazır debug APK

Derlenmiş paket: `football-game/releases/GolArena-debug.apk`  
Telefona kopyalayıp yükleyin (bilinmeyen kaynaklara izin verin).

Yeniden derlemek:

```bash
cd football-game
npm install
npm run build
npx cap sync android
cd android && ./gradlew assembleDebug
```

## Android (Android Studio)

Gereksinimler: JDK 17+, Android Studio (SDK 35), Node 20+.

```bash
cd football-game
npm install
npm run build
npx cap sync android
npx cap open android
```

Release (Play Store) için keystore ile `assembleRelease` / `bundleRelease` kullanın.

## Lisans notu

Oyuncu ve kulüp isimleri **tanıtım / fan projesi** içindir. Ticari yayın için resmi lisans gerekir.

## Klasörler

| Yol | Açıklama |
|-----|----------|
| `src/game/` | Maç motoru, saha, oyuncu, kontroller |
| `src/data/teams.js` | Takımlar ve gerçek oyuncu isimleri |
| `android/` | Capacitor Android projesi (`cap add` sonrası) |
