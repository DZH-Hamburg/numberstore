# AGENTS.md

## Cursor Cloud specific instructions

### Overview

This is a Laravel 13 app with Breeze (Blade) auth, a REST API under `/api/v1`, Sanctum tokens, and Swagger UI. Refer to `README.md` for full setup and usage.

### System dependencies (pre-installed in snapshot)

- PHP 8.4 (from `ppa:ondrej/php`) with extensions: cli, curl, mbstring, xml, zip, mysql, sqlite3, gd, intl, bcmath, opcache
- Composer 2.x (`/usr/local/bin/composer`)
- MySQL 8.0 (Ubuntu package)
- Node.js 22.x (via nvm; `.nvmrc` pins `22.21.1`)

### Starting MySQL

MySQL must be running before the Laravel app can connect. Start it with:

```bash
sudo -S <<< "" bash -c 'mkdir -p /var/run/mysqld && chown mysql:mysql /var/run/mysqld && mysqld_safe &'
sleep 3
```

The default root user has an empty password and connects via socket or TCP at `127.0.0.1:3306`.

### Running tests

Tests use SQLite in-memory (configured in `phpunit.xml`). However, `.env` values take precedence over `phpunit.xml` `<env>` tags. **You must prefix with `APP_ENV=testing`**:

```bash
APP_ENV=testing php artisan test
```

Without the prefix, tests fail with 419/CSRF and auth errors because `.env` overrides the test-specific drivers.

### Linting

```bash
./vendor/bin/pint --test
```

### Running the dev server

```bash
php artisan serve --host=0.0.0.0 --port=8000   # Laravel backend
npm run dev                                       # Vite HMR
```

Or all-in-one: `composer run dev` (starts PHP server, queue worker, log tailer, and Vite concurrently).

### Building frontend assets

Before running tests that render Blade views (outside the test env), build assets first:

```bash
npm run build
```

### API endpoints

- `GET /api/v1/health` — public health check
- `POST /api/v1/auth/token` — get Sanctum bearer token (JSON body: `email`, `password`)
- `GET /api/v1/user` — authenticated user info (`Authorization: Bearer <token>`)

### Swagger docs

Generate with `php artisan l5-swagger:generate`. View at `/api/documentation`.
