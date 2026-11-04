# TYPEWUD · Type Faster. Type Smarter.

A standalone Laravel 12 source project with a monochrome responsive UI.

## Important: first install dependencies

This ZIP contains the complete **application source**, but NOT the `vendor/` directory or Composer binaries. These third-party dependencies must be installed with Composer. It is not a prebuilt executable.

Requirements: PHP 8.2+, Composer 2, PHP extensions required by Laravel (including mbstring, PDO, sqlite or MySQL), and a terminal.

### Quick start (SQLite, easiest)

```bash
composer install
cp .env.example .env
php artisan key:generate
# Create an empty database/database.sqlite file if it does not exist
php artisan migrate --seed
php artisan serve
```

On Windows PowerShell use `Copy-Item .env.example .env` and `New-Item database/database.sqlite -ItemType File` instead of `cp` and `touch`. On macOS/Linux: `touch database/database.sqlite`.

Open http://127.0.0.1:8000

### MySQL instead

Create a database named `typewud`, then set these in `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=typewud
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate --seed`.

### Make your own account an admin

Register normally, then in the project directory run:

```bash
php artisan tinker
```

In Tinker:

```php
App\Models\User::where('email', 'YOUR_EMAIL@example.com')->update(['role' => 'admin']);
```

Exit Tinker and visit `/admin`. Do not expose admin role registration publicly.

## Features included

- Responsive dark homepage; login/register/logout.
- Typing test: time / words / practice, Easy / Normal / Hard / Expert, English / Indonesian.
- Basic WPM and accuracy with server-side recalculation for logged-in results.
- Dashboard: history, personal best, average, goals, report problem.
- Filterable leaderboard, admin management of users and text content.
- Friendly Duel: private room, join code, Ready, countdown, progress via AJAX polling, result.
- No Reverb required, no build step required for static CSS/JS.

## Known limitations (read before production)

This is a **functional starter/MVP**, not a fully finished implementation of every feature in PRD v2.1. The following need additional development: achievements, daily challenges, graphs, detailed mistake review, exact word-target generation, sophisticated scoring/consistency, replay, full anti-cheat, reconnect/forfeit policies, automated tests, real-time WebSockets, and production deployment hardening. Current leaderboard is basic and not yet partitioned by every PRD filter. The typing engine uses seeded fixed texts. Duel result waits for the timer to end and uses periodic HTTP polling, not WebSockets. Browser timing and input can still be manipulated, so competitive scores must not be treated as cheat-proof.

**Do not publish this MVP as a public competitive service without security review and testing.**

## Project structure

- `routes/web.php`: routes.
- `app/Http/Controllers/`: auth, typing, duel, admin.
- `app/Models/`: Eloquent models.
- `database/migrations/`: database tables.
- `database/seeders/`: 8 sample texts.
- `resources/views/`: Blade UI.
- `public/js/` and `public/css/`: no-build frontend.

## Suggested next steps

1. Test registration, login, typing, saving results.
2. Test duel in two browsers with different accounts.
3. Add feature tests and stronger validation for leaderboard eligibility.
4. Add achievements, challenges and analytics.
5. Consider Reverb later if live duel needs faster updates.

## Development note

The application source was generated without downloading Composer dependencies in the build environment. It has been syntax checked for PHP, but not run end-to-end with Laravel. Please run `composer install`, migrations, and functional tests locally before relying on it.
