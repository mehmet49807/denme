# Gönül Köprüsü — Backend (Laravel)

API + Admin Panel + Web Frontend for the Gönül Köprüsü dating platform.
This is the single backend that the Web, Android (`com.gonulkoprusu`) and iOS
clients all talk to through one central MySQL database.

## Tech
- Laravel 11 (PHP 8.2+)
- Laravel Sanctum (token auth → same credentials on every platform)
- MySQL 8 (`utf8mb4_unicode_ci`)
- Blade + a custom cream/beige/pastel theme (no black, no gold, no pure white)

## Setup
```bash
composer install
cp .env.example .env
php artisan key:generate
# configure DB_* in .env (central MySQL credentials supplied later)
php artisan migrate --seed
php artisan serve
```

You can also load the raw schema directly:
```bash
mysql -u root -p gonulkoprusu < database/schema/gonulkoprusu_schema.sql
```

Seeded accounts (password `password`):
- `admin` — admin panel access
- `ayse` — sample woman (free full access)
- `mehmet` — sample premium man

## Key design rules baked into the code
- **Privacy:** real name / email / phone live in `$hidden` on the `User` model and
  are only exposed via `PrivateUserResource` (owner & admin). Everyone else gets
  `PublicUserResource` (username, photo, city–district, gender).
- **Username is read-only:** `ProfileController::update()` strips `username`.
- **Straight matching:** feeds filter on `User::oppositeGender()`.
- **No matching gate:** users browse and message directly; no like-to-match.
- **Comments disabled:** there is intentionally no comments table/relation; the
  `PostResource` reports `comments_enabled: false`.
- **Premium = men only:** `User::hasActivePremium()` returns `true` for women
  (free full access). Only premium men can post stories (`canPostStories()`).
- **Safety:** `report` & `block` endpoints exist for every profile.
- **Admin panel menu is on the RIGHT** (`resources/views/layouts/admin.blade.php`).

## Structure
```
app/Http/Controllers/Api/    REST API controllers
app/Http/Controllers/Admin/  Admin panel (right-side menu)
app/Http/Controllers/Web/    Public web frontend
app/Http/Resources/          Public vs Private serialization (privacy)
app/Models/                  Eloquent models + business rules
database/migrations/         Schema as migrations
database/schema/             Raw MySQL DDL
routes/api.php               REST API (see ../docs/API_CONTRACT.md)
routes/web.php               Web frontend + admin panel
```
