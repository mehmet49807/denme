# Gönül Köprüsü — iOS

Native iOS client (Swift + SwiftUI). Bundle id: `com.gonulkoprusu`.

## Tech
- Swift 5.9+, SwiftUI
- `async/await` + `URLSession` REST client (no third-party deps required)
- `AsyncImage` for images, `UserDefaults` for the token (move to Keychain for prod)

## Open / Build
Create an Xcode project (App template, SwiftUI lifecycle) named `GonulKoprusu`
and add the `GonulKoprusu/` sources, or generate a project with XcodeGen using a
`project.yml`. Set the API host in `Networking/APIClient.swift` → `baseURL`
(defaults to the placeholder `https://api.gonulkoprusu.com/api/v1/`).

`GoogleService-Info.plist` (Firebase push) will be supplied later and added to
the target.

## Design rules honored
- Palette: cream / beige / warm pastels — **no black, no gold, no pure white**
  (`Theme/Theme.swift`).
- Feed: username left, `city · district` box right, **Like only, no comments**
  (`Views/FeedView.swift`).
- Profile: `username` is displayed as **read-only**; private fields shown only to
  the owner (`Views/ProfileView.swift`).
- The client only ever decodes `PublicUser` for other people; private fields come
  exclusively through the owner's `PrivateUser`.

## Structure
```
GonulKoprusu/
  GonulKoprusuApp.swift     app entry + root routing
  Theme/Theme.swift         cream/pastel palette + LocationBox
  Models/Models.swift       Codable DTOs (PublicUser vs PrivateUser)
  Networking/APIClient.swift async REST client (mirrors API_CONTRACT.md)
  Session/SessionManager.swift token persistence
  Views/                    Login, Register, Feed, Profile
  Info.plist
```
