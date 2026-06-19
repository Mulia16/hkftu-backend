# HKFTU Training System — Backend

Backend API for the HKFTU Continuing Education Centre training operations platform. Replaces the legacy Windows/MSSQL on-premise system with a modern platform supporting online registration, class scheduling, payments, attendance, certificates, and operational reporting.

## Tech Stack

- **PHP** 8.4 / **Laravel** 13
- **Database** PostgreSQL 14+ (multi-schema per module)
- **Cache & Queue** Redis
- **Auth** Laravel Sanctum (Bearer Token)
- **Modules** nwidart/laravel-modules v13

## Requirements

- PHP 8.4+ with extensions: `pdo_pgsql`, `redis`, `mbstring`, `xml`, `zip`
- PostgreSQL 14+
- Redis 6+
- Composer 2

## Setup

```bash
composer install

cp .env.example .env
php artisan key:generate
```

Configure `.env`:

```env
APP_NAME="HKFTU Training System"
APP_ENV=local
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=hkftu_backend
DB_USERNAME=postgres
DB_PASSWORD=

QUEUE_CONNECTION=redis
CACHE_STORE=redis

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

```bash
php artisan migrate
php artisan db:seed
php artisan serve
```

## Module Structure

```
Modules/
├── Auth/               # Authentication, RBAC, audit logs
├── CourseCatalogue/    # Subjects, courses, seasons, categories
├── ClassScheduling/    # Classes, sessions, centres, classrooms, clash checks
├── Enrolment/          # Seat reservation, enrolment (P0)
├── Payment/            # Payment intents, receipts, refunds (P0)
├── Membership/         # Member verification, pricing rules (P0)
├── Attendance/
├── Certificate/
├── InstructorFinance/
├── Reporting/
└── Notification/
```

## API

Base URL: `/api/v1/`

Authentication: `Authorization: Bearer <token>` — obtain token via `POST /api/v1/auth/login`.

API documentation (dev only): `http://localhost:8000/docs/api`

Log viewer (dev only): `http://localhost:8000/log-viewer`

## Queue Worker

```bash
php artisan queue:work
```

Use Laravel Horizon for production (Linux only).
