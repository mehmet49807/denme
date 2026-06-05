# Gönül Köprüsü — Android

Native Android client (Kotlin + Jetpack Compose). Package: `com.gonulkoprusu`.

## Tech
- Kotlin, Jetpack Compose (Material 3)
- Retrofit + Moshi (REST), OkHttp logging
- Coil (images), DataStore (token persistence)
- Navigation Compose

## Build
```bash
# Open in Android Studio (Giraffe+), or:
./gradlew :app:assembleDebug
```
> The Gradle wrapper jar is not committed; run `gradle wrapper` once, or open in
> Android Studio which provisions it automatically.

Set the API host in `app/build.gradle.kts` → `API_BASE_URL` (defaults to the
placeholder `https://api.gonulkoprusu.com/api/v1/`).

`google-services.json` (Firebase push) will be supplied later and dropped into
`app/`; the messaging dependency is pre-listed (commented) in the build file.

## Design rules honored
- Palette: cream / beige / warm pastels — **no black, no gold, no pure white**
  (`ui/theme/Color.kt`).
- Feed: username left, `city · district` box right, **Like only, no comments**
  (`ui/screens/FeedScreen.kt`).
- Profile: `username` field is **disabled/read-only**; private fields shown only
  to the owner (`ui/screens/ProfileScreen.kt`).
- Straight matching + privacy are enforced by the backend; the client only ever
  receives `PublicUser` for other people.

## Structure
```
app/src/main/java/com/gonulkoprusu/
  MainActivity.kt            navigation host
  data/
    SessionManager.kt        token persistence
    api/ApiService.kt        REST endpoints (mirrors API_CONTRACT.md)
    api/ApiClient.kt         Retrofit + bearer token
    model/Models.kt          DTOs (PublicUser vs PrivateUser)
  ui/
    theme/                   cream/pastel palette
    screens/                 Login, Register, Feed, Profile
```
