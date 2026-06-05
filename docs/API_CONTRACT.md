# Gönül Köprüsü — REST API Contract (v1)

Base URL: `https://api.gonulkoprusu.com/api/v1` (placeholder)
Local dev: `http://localhost:8000/api/v1`

All clients (Web, Android `com.gonulkoprusu`, iOS) use this same contract against
the central MySQL database. Auth is token-based (Laravel Sanctum): the **same
credentials work on every platform** and state is synced through the shared DB.

## Conventions
- `Content-Type: application/json`
- Authenticated requests: `Authorization: Bearer <token>`
- Timestamps: ISO-8601 UTC
- Errors: `422` validation `{ "message": "...", "errors": { "field": ["..."] } }`,
  `401` unauthenticated, `403` forbidden, `404` not found.
- Pagination: Laravel style `{ data: [...], links: {...}, meta: {...} }`.

### Privacy contract (enforced server-side)
| Field | Visible to others | Visible to owner/admin |
|-------|:----------------:|:----------------------:|
| username, profile_photo, city, district, gender, posts | ✅ | ✅ |
| first_name, last_name, email, phone | ❌ | ✅ |

`username` is **read-only** and can never be updated.

---

## 1. Auth

### POST `/auth/register`
Body:
```json
{
  "username": "ayse",
  "first_name": "Ayşe",
  "last_name": "Yılmaz",
  "email": "ayse@example.com",
  "password": "secret123",
  "phone": "+905000000000",
  "gender": "female",            // "male" | "female"
  "city": "İzmir",
  "district": "Karşıyaka"
}
```
`201` →
```json
{ "token": "1|abc...", "user": { "id": 1, "username": "ayse", "...": "private profile" } }
```

### POST `/auth/login`
```json
{ "login": "ayse", "password": "secret123" }   // login = username OR email
```
`200` → `{ "token": "...", "user": { ...private profile... } }`

### GET `/auth/me`  *(auth)*
Returns the owner's full (private) profile.

### POST `/auth/logout`  *(auth)*
Revokes the current token.

---

## 2. Profile

### GET `/profile`  *(auth)*
Owner's full private profile.

### PUT `/profile`  *(auth)*
Updates any field **except `username`** (silently ignored if sent).
```json
{ "first_name": "Ayşe", "city": "İzmir", "district": "Bornova", "bio": "Merhaba" }
```

### GET `/users/{user}`  *(auth)*
Another user's **public** profile + their posts. `404` if blocked.
```json
{
  "user": { "id": 7, "username": "mehmet", "profile_photo": "...", "city": "Ankara", "district": "Çankaya", "gender": "male", "is_premium": true },
  "posts": { "data": [ ...PostResource... ] }
}
```

---

## 3. Feed  (Instagram-like, comments DISABLED)

Layout contract per item: **username on the left, `city · district` box on the right.**

### GET `/feed`  *(auth)*
Straight matching — women receive men's posts and vice versa; blocked users removed.
```json
{
  "data": [
    {
      "id": 12,
      "image_url": "https://.../p.jpg",
      "caption": "Merhaba",
      "likes_count": 24,
      "liked_by_me": false,
      "comments_enabled": false,
      "author": { "username": "mehmet", "city": "Ankara", "district": "Çankaya", "...": "public" },
      "created_at": "2026-06-05T10:00:00Z"
    }
  ]
}
```

### POST `/posts`  *(auth)*
```json
{ "image_url": "https://.../p.jpg", "caption": "optional" }
```

### POST `/posts/{post}/like`  *(auth)*  → toggles like
`200` → `{ "liked": true, "likes_count": 25 }`

### DELETE `/posts/{post}`  *(auth, owner only)*

> There are **no comment endpoints by design**. Comments are closed platform-wide.

---

## 4. Stories  (Premium MEN only)

### GET `/stories`  *(auth)*
Active (non-expired) stories from the opposite gender.

### POST `/stories`  *(auth)*
Only allowed for **premium men** → otherwise `403`.
```json
{ "media_url": "https://.../s.jpg" }
```

---

## 5. Premium  (MEN only — women have free full access)

### GET `/premium/packages`  *(public)*
```json
{
  "packages": {
    "pro":      { "days": 7,  "price": 250, "label": "Pro - 1 Hafta" },
    "gold":     { "days": 14, "price": 300, "label": "Gold - 2 Hafta" },
    "platinum": { "days": 30, "price": 500, "label": "Platinum - 1 Ay" }
  }
}
```

### GET `/premium/status`  *(auth)*
`{ "is_premium": true, "subscription": { ... } }`

### POST `/premium/subscribe`  *(auth)*
Women → `422` (premium not applicable). Payment gateway is a placeholder.
```json
{ "package_type": "platinum" }   // "pro" | "gold" | "platinum"
```

---

## 6. Messaging

### GET `/conversations`  *(auth)* — list with last message + unread count
### GET `/conversations/{user}`  *(auth)* — full thread (marks incoming as read)
### POST `/conversations/{user}`  *(auth)*
```json
{ "message_text": "Merhaba!" }
```
Blocked relationships → `403`.

---

## 7. Safety  (available on EVERY profile)

### POST `/users/{user}/report`  *(auth)* — Şikayet
```json
{ "reason": "Uygunsuz davranış" }
```

### POST `/users/{user}/block`  *(auth)* — Engelle
### DELETE `/users/{user}/block`  *(auth)* — remove block

---

## 8. Admin

The admin surface is the **web panel** (`/admin`, right-side menu) backed by:
- User Management (view / edit / ban / delete)
- Message Auditor (read user-to-user messages)
- Complaints / Reports dashboard (status workflow)
- Premium Tracker (active users, tier distribution, revenue)
- Admin Broadcast System (official system messages → all/men/women/premium)

These run through session-auth web routes guarded by the `admin` middleware
(`EnsureUserIsAdmin`). A JSON admin API can be layered on the same controllers
if mobile admin tooling is needed later.
