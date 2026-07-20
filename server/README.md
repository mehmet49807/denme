# Server Composer (Laravel)

Canlı cPanel kurulumunun `composer.json` / `composer.lock` kaynağı.

- PHP: `^8.3` (CloudLinux `alt-php83`)
- Framework: `laravel/framework: ^13.0` (canlı: 13.20.0)

Güncelleme: GitHub Actions → **Laravel Update** (`server/RUN_UPDATE` veya workflow_dispatch)  
veya Admin panel → **Güncelleme** menüsü.

PHP 8.3 eklentileri bozulursa: `scripts/deploy/php83_enable_live.py`  
Geçici probe dosyalarını silmek: `server/CLEANUP_PHP83` push.
