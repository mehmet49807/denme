# Gonul Koprusu REST API Contracts

Base URL placeholder: `https://api.gonulkoprusu.example/api/v1`

Authentication: Bearer token returned by login/register. Tokens are shared by web, Android, and iOS clients. All timestamps are ISO-8601 UTC strings.

## Global Rules

- Straight browsing filter: authenticated women receive male profiles; authenticated men receive female profiles.
- Blocked users are removed from profile, feed, story, and message results in both directions.
- Public profile payloads never expose first name, last name, email, or phone.
- Owner profile and admin endpoints may expose first name, last name, email, and phone.
- Usernames are immutable after registration.
- Comments are disabled. There are no comment endpoints, fields, counters, or UI affordances.
- Female accounts have full access without premium. Premium subscription logic applies only to male accounts.
- Story creation is allowed for women and premium men. Standard men receive `403 story_premium_required`.

## Shared DTOs

### PublicUser

```json
{
  "id": 12,
  "username": "deniz34",
  "profile_photo_url": "https://cdn.example/profiles/12.jpg",
  "gender": "female",
  "city": "Istanbul",
  "district": "Kadikoy"
}
```

### OwnerUser

```json
{
  "id": 12,
  "username": "deniz34",
  "first_name": "Deniz",
  "last_name": "Yilmaz",
  "email": "deniz@example.com",
  "phone": "+905551112233",
  "gender": "female",
  "profile_photo_url": "https://cdn.example/profiles/12.jpg",
  "city": "Istanbul",
  "district": "Kadikoy"
}
```

### Post

```json
{
  "id": 91,
  "author": { "id": 12, "username": "deniz34", "profile_photo_url": "https://cdn.example/profiles/12.jpg", "city": "Istanbul", "district": "Kadikoy" },
  "image_url": "https://cdn.example/posts/91.jpg",
  "likes_count": 38,
  "liked_by_me": false,
  "comments_enabled": false,
  "created_at": "2026-06-05T14:35:00Z"
}
```

## Auth

### POST `/auth/register`

Request:

```json
{
  "username": "deniz34",
  "first_name": "Deniz",
  "last_name": "Yilmaz",
  "email": "deniz@example.com",
  "password": "secret-password",
  "phone": "+905551112233",
  "gender": "female",
  "city": "Istanbul",
  "district": "Kadikoy"
}
```

Response `201`:

```json
{
  "token": "placeholder-token",
  "user": { "id": 12, "username": "deniz34", "first_name": "Deniz", "last_name": "Yilmaz", "email": "deniz@example.com", "phone": "+905551112233", "gender": "female", "city": "Istanbul", "district": "Kadikoy" }
}
```

### POST `/auth/login`

Request: `{ "email": "deniz@example.com", "password": "secret-password" }`

Response: `{ "token": "placeholder-token", "user": OwnerUser }`

### POST `/auth/logout`

Response: `204 No Content`

## Feed and Posts

### GET `/feed`

Returns posts authored by visible opposite-gender users excluding blocks.

Response:

```json
{ "data": [Post], "meta": { "next_cursor": "optional" } }
```

### POST `/posts`

Multipart request: `image`.

Response `201`: `Post`

### POST `/posts/{post}/like`

Response: `{ "post_id": 91, "liked": true, "likes_count": 39 }`

### DELETE `/posts/{post}/like`

Response: `{ "post_id": 91, "liked": false, "likes_count": 38 }`

## Profiles and Safety

### GET `/profile/me`

Response: `OwnerUser`

### PATCH `/profile/me`

Username is rejected if included.

Request:

```json
{
  "first_name": "Deniz",
  "last_name": "Yilmaz",
  "email": "new@example.com",
  "phone": "+905559998877",
  "city": "Ankara",
  "district": "Cankaya",
  "profile_photo_url": "https://cdn.example/profiles/12-new.jpg"
}
```

Response: `OwnerUser`

### GET `/profiles/{user}`

Response: `PublicUser` plus public posts. Hidden fields are never returned.

### POST `/profiles/{user}/report`

Request: `{ "reason": "Inappropriate message" }`

Response `201`: `{ "id": 300, "status": "open" }`

### POST `/profiles/{user}/block`

Response `201`: `{ "blocked": true }`

### DELETE `/profiles/{user}/block`

Response: `{ "blocked": false }`

## Stories

### GET `/stories`

Returns active stories from visible users.

### POST `/stories`

Multipart request: `media`.

Rules:

- Female account: allowed.
- Male account with active premium: allowed.
- Standard male account: `403`.

## Premium

### GET `/premium/packages`

Response:

```json
{
  "data": [
    { "type": "pro", "label": "Pro", "duration_days": 7, "price_try": 250 },
    { "type": "gold", "label": "Gold", "duration_days": 14, "price_try": 300 },
    { "type": "platinum", "label": "Platinum", "duration_days": 30, "price_try": 500 }
  ]
}
```

### GET `/premium/status`

Response: `{ "is_premium": true, "package_type": "platinum", "expires_at": "2026-07-05T14:35:00Z" }`

### POST `/premium/subscribe`

Male users only.

Request: `{ "package_type": "pro", "payment_token": "placeholder-provider-token" }`

Response `201`: premium status payload.

## Messaging

### GET `/conversations`

Returns visible conversation list with last message preview.

### GET `/messages/{user}`

Returns direct messages with one visible user.

### POST `/messages/{user}`

Request: `{ "message_text": "Merhaba" }`

Response `201`: message payload.

## Admin

All admin endpoints require `role=admin`.

- `GET /admin/users` - list/search users.
- `PATCH /admin/users/{user}` - edit profile, role, or status.
- `POST /admin/users/{user}/ban` - ban account.
- `DELETE /admin/users/{user}` - soft delete account.
- `GET /admin/messages` - safety message auditor.
- `GET /admin/reports` - complaints dashboard.
- `PATCH /admin/reports/{report}` - update status and notes.
- `GET /admin/premium` - active premium users, tier distribution, revenue.
- `POST /admin/broadcasts` - send official system message.
