# Pharma Orders — Order Management Prototype

A small order-management platform for a business that takes orders mostly via WhatsApp:

- **Admin dashboard** (Vue 3 SPA under `/admin`) — manage clients, suppliers, products and orders.
- **Public landing page** (Vue 3 SPA at `/`) — customers browse products, add to cart, submit their delivery
  info, and confirm the order via a pre-filled WhatsApp message (`wa.me` link — no WhatsApp API integration).

## Stack

- **Backend**: Laravel 13 (PHP 8.4), REST API, Laravel Sanctum (SPA cookie auth), Form Requests, API Resources,
  MySQL/MariaDB (this environment runs it via XAMPP; SQLite works too — see below).
- **Frontend**: Vue 3 (`<script setup>`), Vite, Vue Router, Pinia (with `localStorage`-persisted cart), Axios,
  Tailwind CSS v4.

## Requirements

- PHP 8.3+ with `pdo_mysql` (or `pdo_sqlite`, see below) enabled
- Composer
- Node.js 20+ and npm
- A MySQL/MariaDB server (e.g. XAMPP's bundled MariaDB) — or SQLite if you prefer

## 1. Backend setup (Laravel API)

This project ships pre-configured for a local MySQL server on `127.0.0.1:3306` with a `root` user and no
password (XAMPP's default). Create the database once:

```bash
mysql -u root -e "CREATE DATABASE pharma_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# (with XAMPP, use its bundled client instead, e.g. /opt/lampp/bin/mysql)
```

Then:

```bash
cd backend
composer install
cp .env.example .env      # already present and pre-filled for local dev
php artisan key:generate  # skip if APP_KEY is already set in .env
php artisan migrate --seed
php artisan storage:link
php artisan serve          # http://localhost:8000
```

If your MySQL user/password/port differ, update `DB_*` in `backend/.env` accordingly.

### Using SQLite instead

If you'd rather use SQLite, edit `backend/.env`:

```
DB_CONNECTION=sqlite
```

(remove/ignore the `DB_HOST`/`DB_DATABASE`/etc. mysql lines), make sure PHP's `pdo_sqlite` extension is enabled,
then:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

### Running tests

Tests run against a dedicated `pharma_orders_testing` MySQL database (configured in `phpunit.xml`) via
`RefreshDatabase`. Create it once:

```bash
mysql -u root -e "CREATE DATABASE pharma_orders_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Then:

```bash
php artisan test
```

All 11 feature tests (public order creation, price recalculation, client dedup, rate limiting, admin login,
order status changes + history, order deletion) pass against this setup.

## 2. Frontend setup (Vue SPA)

In a second terminal:

```bash
cd frontend
npm install
npm run dev                # http://localhost:5173
```

The SPA serves **both** the public site (`/`) and the admin dashboard (`/admin/...`).

## Admin credentials (seeded)

```
email:    admin@example.com
password: password
```

## WhatsApp number

The business WhatsApp number used to build `wa.me` confirmation links lives in `backend/.env`:

```
WHATSAPP_NUMBER=15551234567
```

Change it to your real number (digits only, international format, no `+` or spaces) and restart `php artisan serve`.

## How authentication works

Sanctum is configured for **SPA (cookie) authentication**, not API tokens:

- `backend/config/cors.php` allows the Vite dev origins (`FRONTEND_URLS` in `.env`) with `supports_credentials: true`.
- `backend/bootstrap/app.php` calls `$middleware->statefulApi()` so requests from `SANCTUM_STATEFUL_DOMAINS`
  (also in `.env`) are authenticated via the session cookie.
- The frontend axios client (`frontend/src/api/client.js`) sends `withCredentials: true` and first hits
  `GET /sanctum/csrf-cookie` before logging in, exactly as Laravel's SPA auth flow expects.

If you deploy the frontend to a different host/port, update `FRONTEND_URLS` and `SANCTUM_STATEFUL_DOMAINS` in
`backend/.env` (and `VITE_API_URL` in `frontend/.env`) accordingly.

## Data model overview

- `clients`, `suppliers`, `products`, `orders`, `order_items`, `order_status_histories`
- `orders.status` is a PHP backed enum (`App\Enums\OrderStatus`): `request_received → availability_check →
  ordered_from_supplier → paid → shipped → delivered`, with `cancelled` reachable at any point. Every status
  change writes a row to `order_status_histories` (see `App\Services\OrderService::changeStatus`).
- Order totals and item prices are **always recalculated server-side** from the current product price — the
  client-submitted price (if any) is ignored (`App\Services\OrderService::attachItems`).
- `POST /api/public/orders` is throttled to 10 requests/minute/IP; the rest of `/api/public/*` is throttled to
  60/minute/IP.

## Design decisions / assumptions (prototype scope)

A few ambiguous points were resolved pragmatically rather than blocking on them:

- **One combined Vue app** serves both the public site and the admin dashboard (routes split under `/` and
  `/admin`), rather than two separate builds — simpler to run for a prototype.
- **Cart** is a Pinia store persisted to `localStorage`; it is only cleared after a successful order submission.
- **Order reference format**: `ORD-{year}-{sequence}` (e.g. `ORD-2026-0001`), sequence resets per year.
- **Product images**: stored on the `public` disk (`storage/app/public`), served via `php artisan storage:link`.
  Admin product create/update accepts `multipart/form-data` with Laravel's `_method` override for `PUT`.
- **Client de-duplication on public orders**: an existing client is matched by exact phone number; if none is
  found, a new client is created with `source = website`.
- **Seed data** uses generic, neutral product names (electronics/home/beauty/accessories/toys) rather than
  pharmacy-specific items, since the brief describes a generic small business selling products over WhatsApp.
- Deleting a client cascades to their orders (foreign key `cascadeOnDelete`) — acceptable for a prototype;
  a production version would likely soft-delete or block deletion when orders exist.

## Switching database later

Update the `DB_*` values in `backend/.env` (MySQL, PostgreSQL and SQLite are all supported by Laravel without
code changes), then run `php artisan migrate --seed` again.
# pharma
