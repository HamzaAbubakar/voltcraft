# Voltcraft ERP

Laravel 13 and Laravel Breeze (Blade) provide the login/session layer. The existing solar battery dashboard is kept as a Blade view so its UI can be migrated without a frontend rewrite.

## Run locally

Requirements: PHP 8.3+, Composer, and SQLite (or another Laravel-supported database).

```powershell
cd D:\laragon\www\voltcraft
composer install
php artisan migrate
php artisan serve
```

Set `SEED_ADMIN_PASSWORD` in `.env` to a strong password, then run `php artisan db:seed` to create or update the configured `SEED_ADMIN_EMAIL` account. The local `.env` contains the initial admin credentials. Open `http://127.0.0.1:8000` and sign in; public registration is disabled. The dashboard is at `/dashboard` and redirects guests to login.

The dashboard and Breeze login screens currently load Tailwind CSS and Font Awesome from CDNs, so a Node/npm build is not needed to run this first migration. The original HTML is retained at `solar_erp_dashboard (2).html`.

## Current migration scope

- Login, session logout, seeded admin user, and authenticated dashboard access use Laravel Breeze.
- The dashboard UI is in `resources/views/dashboard/index.blade.php`.
- Dashboard figures and module data are still static HTML; forms do not persist ERP records yet.
- Projects, Company Shares, and Investments are shown as disabled sidebar items until their panels are built.
- Before exposing the app publicly, protect the seeded admin credentials and replace CDN assets with locally built assets.
