# Gonul Koprusu Backend, API, Admin, and Web Frontend

Laravel/PHP boilerplate for the **Gonul Koprusu** social dating platform.

## Scope

- Central REST API backed by MySQL.
- Laravel admin panel with a right-side navigation menu.
- Public web landing/profile/feed views using a cream and warm pastel visual system.
- Strict privacy boundaries: public users only receive username, profile photo, posts, and city-district.
- Direct browsing and messaging; no swipe or like-to-match gate.
- Post comments are intentionally disabled across all API contracts and UI placeholders.

## Folders

- `database/schema/001_initial_schema.sql` - complete MySQL DDL.
- `docs/api-contracts.md` - REST API contracts for web, Android, and iOS clients.
- `routes/api.php` - Laravel API route map.
- `routes/web.php` - web/admin route map.
- `app/Http/Controllers` - controller stubs for API and admin actions.
- `resources/views` - Blade layouts and pages.
- `resources/css/gonulkoprusu.css` - shared light cream/pastel styling.

## Environment placeholders

Database credentials, object storage, payment provider keys, Firebase configuration, and push notification credentials are intentionally not committed. Add them through Laravel environment configuration when supplied.
