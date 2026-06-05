# Gönül Köprüsü — Multi-Platform Dating & Social Platform

A unified dating/social application across **Web**, **Android** (`com.gonulkoprusu`)
and **iOS**, all backed by a single central **MySQL** database accessed through a
secure REST API.

> The repository root also contains a static marketing/landing HTML template
> (`index.html`, `main.css`, …) that predates this build. The application code
> lives in the three project folders below.

## Repository layout
```
gonulkoprusu-backend-web/   Laravel API + Admin Panel + Web Frontend (PHP/MySQL)
gonulkoprusu-android/       Native Android (Kotlin + Jetpack Compose)
gonulkoprusu-ios/           Native iOS (Swift + SwiftUI)
docs/API_CONTRACT.md        REST API contract shared by all clients
```

## How the platforms connect
All clients authenticate against the same backend using token auth
(Laravel Sanctum), so **one account works everywhere** and state is synced
through the shared database. See [`docs/API_CONTRACT.md`](docs/API_CONTRACT.md).

```
 Web (Blade)  ┐
 Android      ├──►  REST API (/api/v1)  ──►  Central MySQL DB
 iOS          ┘            ▲
                           └── Admin Panel (web, right-side menu)
```

## Design system (enforced everywhere)
- **Colors:** soft, warm light tones — cream, beige, warm pastels (dusty rose,
  sage, lavender, terracotta). **No black, no gold, no pure white.**
- **No religious imagery/symbols** anywhere.
- **Admin panel navigation is strictly on the RIGHT** (slides out on mobile).
- Placeholders included for high-quality men/women imagery, success-story
  testimonials, and a vibrant modern logo.

## Core product rules (implemented in code)
| Rule | Where |
|------|-------|
| Straight matching (women see men, men see women) | `User::oppositeGender()`, feed queries |
| No like-to-match gate — browse & message directly | feed/messaging endpoints |
| Report (Şikayet) & Block (Engelle) on every profile | `SafetyController`, client actions |
| Feed: username left, city·district box right | feed views (web/Android/iOS) |
| **Comments disabled** on all posts | no comments table; `comments_enabled:false` |
| Privacy: name/email/phone visible only to owner+admin | `Public`/`Private` user resources |
| Username is **read-only** | profile update strips `username` |
| Premium = **men only**; women free full access | `User::hasActivePremium()` |
| Only premium men can post **Stories** | `User::canPostStories()`, `StoryController` |

### Premium packages (men only)
| Package | Duration | Price |
|---------|----------|-------|
| Pro | 1 week | 250 TL |
| Gold | 2 weeks | 300 TL |
| Platinum | 1 month (30 days) | 500 TL |

## Admin panel (Laravel, right-side menu)
User Management · Message Auditor · Complaints/Reports · Premium Tracker ·
Admin Broadcast System.

## Getting started
1. **Backend:** see [`gonulkoprusu-backend-web/README.md`](gonulkoprusu-backend-web/README.md)
   (`composer install`, configure MySQL in `.env`, `php artisan migrate --seed`).
2. **Android:** see [`gonulkoprusu-android/README.md`](gonulkoprusu-android/README.md).
3. **iOS:** see [`gonulkoprusu-ios/README.md`](gonulkoprusu-ios/README.md).

> Placeholders in use until supplied: MySQL credentials, Firebase
> `google-services.json` / `GoogleService-Info.plist`, and the production API host.
