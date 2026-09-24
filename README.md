# Hirna Mobility Solutions

Hirna Mobility Solutions is a Laravel-based fleet operations portal for vehicle, driver, trip, reservation, fuel, maintenance, route, cost, and security management. It provides a server-rendered web application and JSON integration endpoints for operations, booking, inventory, finance, and HR systems.

## 1. Project Overview

The application supports:

- Fleet and driver administration
- Vehicle reservations and dispatch
- Trip scheduling, completion, and simulated GPS telemetry
- Fuel logging, prediction, and model training
- Maintenance records and status tracking
- Route planning with fuel and cost estimates
- Transport cost analysis with CSV and PDF exports
- Session-based login with six-digit OTP verification
- Security audit logging and role-restricted modules

## 2. Architecture

```text
Browser
	|
	v
Laravel web routes (routes/web.php) + Blade views
	|
	v
Controllers and role middleware
	|----------------------|
	v                      v
Eloquent models       Domain services
	|                    |-- RoutingService
	|                    |-- FuelPredictionService
	v                    v
Configured database   Cache / OSRM routing API / mail provider

External systems
	|
	v
JSON integration routes (routes/api.php)
```

The Vercel deployment entrypoint is `api/index.php`. Static assets are served from `public/` according to `vercel.json`.

## 3. Tech Stack

| Area | Technology |
| --- | --- |
| Runtime | PHP 8.2 or later |
| Framework | Laravel 12 |
| Database | Supabase PostgreSQL through Laravel Eloquent |
| ORM | Laravel Eloquent |
| Frontend build | Vite 7, Laravel Vite Plugin, Tailwind CSS 4 |
| HTTP client | Axios and Laravel HTTP client |
| Testing | PHPUnit 11 through Laravel test runner |
| Deployment | Vercel PHP runtime through `vercel.json` |
| External routing | OpenStreetMap OSRM public routing API, with local fallback paths |
| Mail | SMTP configuration with Brevo HTTPS API priority for OTP delivery |

## 4. Prerequisites

- PHP 8.2 or later with the extensions required by Laravel and the selected database driver
- Composer
- Node.js and npm
- A Supabase PostgreSQL connection configured for Laravel Eloquent
- SMTP credentials or a Brevo API key if real OTP email delivery is required
- Git for source control

## 5. Project Structure

```text
app/
	Http/Controllers/       Web and integration request handlers
	Http/Middleware/        Role checks and security response headers
	Models/                 Eloquent models for fleet and user data
	Services/               Routing and fuel prediction services
bootstrap/app.php         Route, middleware, and exception registration
config/                   Application, database, mail, session, and service config
database/
	migrations/             Database schema history
	seeders/                Demo users and fleet data
supabase/
	migrations/             Authoritative Supabase PostgreSQL migrations
public/                   Public entrypoint, assets, images, and downloads
resources/
	css/                    Tailwind/Vite CSS source
	js/                     Vite JavaScript source
	views/                  Blade templates
routes/
	web.php                 Browser routes and role restrictions
	api.php                 JSON integration routes
tests/                    Feature and unit tests
api/index.php             Vercel PHP function entrypoint
vercel.json               Vercel routing and runtime configuration
composer.json             PHP dependencies and Composer scripts
package.json              Frontend dependencies and scripts
phpunit.xml               PHPUnit suites and testing environment
```

## 6. Local Development Setup

From the project root:

```bash
composer install
Create a local `.env` file and configure the required values below.
php artisan key:generate
supabase db reset
php artisan db:seed
npm install
npm run build
```

The Supabase SQL migrations are the database schema source of truth. The combined setup script in `composer.json` installs PHP and frontend dependencies, creates the environment file when needed, generates the Laravel application key, and builds frontend assets. It does not run the preserved Laravel migrations.

```bash
composer run setup
```

Run the application and Vite development server separately:

```bash
php artisan serve
npm run dev
```

Or run the Composer development process, which starts the Laravel server, queue listener, Pail logs, and Vite together:

```bash
composer run dev
```

## 7. Environment Variables

Configure the required values in `.env`. Do not commit `.env` or real credentials.

| Variable group | Variables |
| --- | --- |
| Application | `APP_NAME`, `APP_ENV`, `APP_KEY`, `APP_DEBUG`, `APP_URL` |
| Database | `DB_CONNECTION=pgsql`, `DB_URL` or `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_SSLMODE` |
| Supabase | `SUPABASE_URL`, `SUPABASE_ANON_KEY`, `SUPABASE_SERVICE_ROLE_KEY`; optional PostgreSQL pooler values for `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` |
| Session/cache/queue | `SESSION_DRIVER`, `SESSION_LIFETIME`, `SESSION_IDLE_TIMEOUT`, `CACHE_STORE`, `QUEUE_CONNECTION` |
| Mail | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` |
| OTP delivery | `DEMO_OTP_EMAIL`, `BREVO_API_KEY`, `RESEND_API_KEY` |

Laravel connects to Supabase through PostgreSQL and Eloquent. `SUPABASE_URL` and Supabase API keys are not PostgreSQL connection credentials. Configure `DB_CONNECTION=pgsql` plus either `DB_URL` or the Supabase pooler values for `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and `DB_SSLMODE=require`. Supabase service-role and access-token values must remain server-side and must never be exposed to browser code. The default session idle timeout is 7,200 seconds. OTP codes are sent through Brevo when `BREVO_API_KEY` is configured; the application falls back to Laravel mail configuration.

## 8. Database & Migrations

The native Supabase migrations under `supabase/migrations/` are authoritative. The original Laravel migrations under `database/migrations/` remain as historical/reference files and must not be run against the Supabase database.

Provision or reset a local Supabase database with:

```bash
supabase start
supabase db reset
```

After the schema is provisioned, seed the application data with:

```bash
php artisan db:seed
```

The schema contains these primary tables:

| Table | Purpose |
| --- | --- |
| `users` | Accounts, roles, profile fields, and last OTP verification time |
| `drivers` | Licenses, availability, performance score, and trip totals |
| `vehicles` | Fleet identity, type, status, capacity, and GPS coordinates |
| `trips` | Scheduled, active, completed, or cancelled trips |
| `trip_logs` | GPS telemetry, speed, idle time, and timestamps |
| `fuel_logs` | Vehicle/trip fuel or energy usage and cost |
| `maintenance_records` | Scheduled, in-progress, and completed maintenance |
| `performance_records` | Speeding, braking, idle time, and safety scores |
| `routes` | Planned route paths, distances, and average consumption |
| `vehicle_reservations` | Vehicle reservations, assignments, status, and remarks |
| `security_logs` | Login, lockout, honeypot, and administrative security events |
| `sessions` | Laravel session storage when the database session driver is selected |

The seeder creates five management accounts, five driver accounts, six vehicles, historical trips and fuel/performance records, maintenance records, and sample reservations. Seeded accounts use the password defined in `database/seeders/DatabaseSeeder.php`; change or remove seeded credentials before using a shared or production environment.

## 9. Backend / API

### Web application routes

The main browser modules are defined in `routes/web.php`:

| Area | Routes |
| --- | --- |
| Authentication | `/login`, `/verify-otp`, `/resend-otp`, `/logout` |
| Dashboard/import | `/`, `/import-data`, `/import/csv` |
| Profile | `/profile`, `/profile/update`, `/profile/password`, `/profile/avatar` |
| Security administration | `/admin/security`, `/admin/security/unlock`, `/admin/security/users`, `/admin/security/clear-logs` |
| Fleet | `/vehicles`, `/fleet/assign-driver`, `/fleet/drivers` |
| Reservations | `/reservations`, `/reservations/check-availability` |
| Trips/telemetry | `/trips`, `/trips/plan-preview`, `/trips/{trip}/start`, `/trips/{trip}/complete`, `/trips/{trip}/simulate-gps` |
| Fuel | `/fuel`, `/fuel/predict`, `/fuel/train` |
| Cost analysis | `/cost-analysis`, `/cost-analysis/export-csv`, `/cost-analysis/export-pdf` |
| Maintenance | `/maintenance`, `/maintenance/{record}/status` |
| Route planning | `/routes`, `/routes/plan` |

### JSON integration API

These routes are defined in `routes/api.php` and currently do not attach the `role` middleware. They validate request payloads in `IntegrationApiController` where applicable.

| Method | Endpoint | Integration |
| --- | --- | --- |
| POST | `/api/operations/trip-request` | Receives a trip request and assigns available resources |
| GET | `/api/operations/vehicle-availability` | Returns active and available vehicles |
| POST | `/api/booking/assign-trip` | Assigns a vehicle/driver and returns ETA and fuel estimate |
| POST | `/api/inventory/fuel-stock` | Stores current fuel stock in cache |
| GET | `/api/inventory/fuel-usage` | Returns fuel usage records |
| GET | `/api/finance/expenses` | Returns fuel and maintenance expenses |
| POST | `/api/hr/sync-driver` | Creates, updates, or deactivates a driver record |
| GET | `/api/hr/driver-performance` | Returns driver performance data |

Validation failures use Laravel's standard validation response behavior. Resource availability failures return `409` or `503` depending on the integration endpoint.

## 10. Authentication & Security

- Login accepts the stored account email/name lookup format and verifies passwords with Laravel's hashed password cast.
- A six-digit OTP is required on first login or after the 50-minute OTP verification window expires.
- OTP codes expire after 10 minutes; resend requests are limited by a 60-second wait.
- Three failed login attempts trigger a temporary 60-second rate-limit lockout.
- Authenticated sessions expire after 7,200 seconds of inactivity by default.
- Logout invalidates the session and regenerates the CSRF token.
- `SecurityHeadersMiddleware` adds clickjacking, MIME sniffing, XSS filter, referrer, permissions, and no-cache headers to web responses.
- Security events are written to `security_logs` and application logs.
- The login form includes a honeypot field to reject automated submissions.
- OTP delivery uses the Brevo HTTPS API when configured and falls back to the configured mail transport.

API integration routes should be protected by an external gateway, network policy, or dedicated authentication layer before production exposure because the current route definitions do not include application authentication middleware.

## 11. System Modules

| Module | Main implementation |
| --- | --- |
| Fleet Vehicle Management | `FleetController`, `Vehicle`, vehicle status and driver assignment routes |
| Vehicle Reservation and Dispatch | `ReservationController`, `VehicleReservation` |
| Trip Scheduling and Telemetry | `TripController`, `Trip`, `TripLog` |
| Fuel Management | `FuelController`, `FuelLog`, `FuelPredictionService` |
| Maintenance | `MaintenanceController`, `MaintenanceRecord` |
| Transport Cost Analysis | `CostAnalysisController` and CSV/PDF export routes |
| Route Planning | `RouteController`, `Route`, `RoutingService` |
| Security and Access Control | `AuthController`, `SecurityController`, `CheckRole`, `SecurityLog` |
| External Integrations | `IntegrationApiController` and `routes/api.php` |

`RoutingService` resolves configured Metro Manila hubs, calculates Haversine distance, requests OSRM road geometry, and generates a local fallback path when OSRM is unavailable. `FuelPredictionService` stores trained weights in cache and predicts fuel or energy use based on distance, speed, vehicle type, and fuel type.

## 12. Role-Based Access Control (RBAC)

The custom `role` middleware reads `user_role` from the session. `admin` bypasses route role lists; other roles must be explicitly listed on a route.

| Role | Verified access areas |
| --- | --- |
| `admin` | All modules and administrative security controls |
| `fleet_manager` | Fleet, reservations, trips, fuel, maintenance, and route planning |
| `dispatcher` | Fleet assignment, reservations, trips, and route planning |
| `finance` | Trips completion, fuel, and cost analysis |
| `operations` | Dashboard/import, reservations, trip operations, cost analysis, and route planning |
| `driver` | Trip listing; the seeded role is not granted management route permissions |

All authenticated roles can access the dashboard and profile routes. The live perspective switch supports `admin`, `fleet_manager`, `dispatcher`, `finance`, and `operations`.

## 13. Deployment

The repository includes Vercel configuration:

- `api/index.php` is deployed with the `vercel-php@0.9.0` runtime.
- `vercel.json` routes asset paths to `public/` and all other requests to `api/index.php`.
- The configured output directory is `public`.
- Production environment variables must be configured in the hosting provider; do not place credentials in source control.

Before deployment:

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
# Apply supabase/migrations/*.sql through the Supabase deployment pipeline.
php artisan db:seed
```

Do not run `php artisan migrate --force` in this deployment because it would apply the preserved Laravel/SQLite-oriented migration history. Apply the Supabase SQL migrations separately, then run the seeder only when the target environment should contain the demo dataset. Confirm that the selected PostgreSQL connection, session/cache stores, writable storage paths, mail provider, `APP_KEY`, and `APP_URL` are configured. The Vercel entrypoint fails clearly when PostgreSQL is unavailable; it does not fall back to SQLite or run migrations at request time. The application also calls the public OSRM service for route geometry and falls back locally if the request fails.

Avatars and generated downloads use the existing local/public Laravel filesystem. Vercel's filesystem is ephemeral, so persistent file storage is not provided by the current implementation.

## 14. Testing

Run the configured test suite with either command:

```bash
php artisan test
composer run test
```

The current repository contains one feature smoke test for the protected dashboard and login page, plus one unit sanity test. Add module-specific tests when changing authentication, authorization, integrations, migrations, or data mutation behavior.

## 15. Troubleshooting

| Symptom | Checks |
| --- | --- |
| Login redirects back to login | Verify the PostgreSQL connection, provision the Supabase schema, seed a user, verify `APP_KEY`, and inspect `SESSION_DRIVER` and storage permissions |
| OTP is not received | Configure `BREVO_API_KEY` or valid SMTP variables and inspect application logs; the code expires after 10 minutes |
| Frequent OTP prompts | Check `last_otp_verified_at`, server time, and `SESSION_IDLE_TIMEOUT`; OTP verification is valid for 50 minutes |
| Assets are missing | Run `npm run build` and confirm the built files are available under `public/build` |
| No vehicles or drivers can be assigned | Check active vehicle status, available driver status, and trips with `scheduled` or `active` status |
| Route planning has no OSRM geometry | Verify outbound HTTPS access; `RoutingService` generates a local fallback path when OSRM is unavailable |
| Database errors after schema changes | Apply the pending files under `supabase/migrations/`; do not run the historical Laravel migrations |
| Cached configuration is stale | Run `php artisan config:clear` during development or rebuild the deployment cache |

## 16. Contributing

1. Create a focused branch for the change.
2. Update migrations, routes, controllers, services, views, or tests together when the behavior crosses those boundaries.
3. Run `composer run test` and `npm run build` before opening a pull request.
4. Do not commit `.env`, API keys, SMTP passwords, generated secrets, or production data.
5. Document new routes, environment variables, roles, and migrations in this README when applicable.

## 17. License

`composer.json` declares the project license as MIT. No separate `LICENSE` file is currently present in the repository.

## 18. Quick Reference

```bash
# Install and initialize
composer run setup

# Start development
composer run dev

# Build frontend assets
npm run build

# Provision the database schema with the Supabase CLI
supabase db reset

# Seed application data after schema provisioning
php artisan db:seed

# Run tests
composer run test

# Inspect routes
php artisan route:list
```