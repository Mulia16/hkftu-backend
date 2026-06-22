# HKFTU Training System — Backend

Backend API for the HKFTU Continuing Education Centre training operations platform. Replaces the legacy Windows/MSSQL on-premise system with a modern platform supporting online registration, class scheduling, payments, attendance, certificates, and operational reporting.

## Tech Stack

- **PHP** 8.3+ / **Laravel** 13
- **Database** PostgreSQL 14+ (multi-schema per module)
- **Cache & Queue** Redis (predis)
- **Auth** Laravel Sanctum (Bearer Token) + Spatie Permission (teams)
- **Modules** nwidart/laravel-modules v13 (11 modules)
- **Deploy** FrankenPHP worker mode (Docker) or traditional Nginx + PHP-FPM

## Requirements

- PHP 8.3+ with extensions: `pdo_pgsql`, `redis`, `mbstring`, `xml`, `zip`
- PostgreSQL 14+
- Redis 6+
- Composer 2

## Setup (Local)

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

## Setup (Docker — FrankenPHP Worker Mode)

```bash
docker-compose up -d
```

Runs at `http://localhost:8000` with FrankenPHP worker mode, PostgreSQL 16, Redis 7, and dedicated queue worker.

To compare with traditional Nginx + PHP-FPM deploy, use the `--server=frankenphp` flag with Laravel Octane or deploy with a standard Nginx container.

## Seed Data

After `php artisan db:seed`, the following accounts are available:

| Email | Password | Role |
|-------|----------|------|
| admin@hkftu.org | password | system_admin |
| planner@hkftu.org | password | course_planner |
| manager@hkftu.org | password | centre_manager |
| counter@hkftu.org | password | counter_staff |
| instructor@hkftu.org | password | instructor |
| finance@hkftu.org | password | finance_staff |

Also seeds: 3 seasons, ~20 categories, 10 subjects, 6 centres, ~16 classrooms.

## Module Structure

```
Modules/
├── Auth/               # Authentication, RBAC (23 permissions), audit logs, security events
├── CourseCatalogue/    # Subjects, courses, seasons, categories, course text versions
├── ClassScheduling/    # Classes, sessions, centres, classrooms, clash checks, holidays
├── Enrolment/          # Seat reservation, enrolment (stub)
├── Payment/            # Payment intents, receipts, refunds (stub)
├── Membership/         # Member verification, pricing rules (stub)
├── Attendance/         # (stub)
├── Certificate/        # (stub)
├── InstructorFinance/  # (stub)
├── Reporting/          # (stub)
└── Notification/       # (stub)
```

## API Endpoints

Base URL: `/api/v1/`

Authentication: `Authorization: Bearer <token>` — obtain token via `POST /api/v1/auth/login`.

### Auth
- `POST /auth/login` — Login, returns Sanctum token
- `POST /auth/logout` — Logout (auth)
- `GET /users/me` — Current user profile (auth)
- `PATCH /users/me` — Update name/phone (auth)
- `POST /auth/password/request` — Request password reset
- `POST /auth/password/reset` — Reset password with token

### Course Catalogue
- `GET/POST /seasons`, `GET/PATCH/DELETE /seasons/{id}`
- `GET/POST /categories`, `GET/PATCH/DELETE /categories/{id}`
- `GET/POST /subjects`, `GET/PATCH/DELETE /subjects/{id}`
- `GET/POST /courses`, `GET/PATCH/DELETE /courses/{id}`
- `GET /course-texts/{subjectId}`, `POST /course-texts/{subjectId}`, `GET/PATCH /course-texts/{subjectId}/{versionId}`

### Class Scheduling
- `GET/POST /centres`, `GET/PATCH/DELETE /centres/{id}`
- `GET/POST /centres/{id}/classrooms`, `GET/PATCH/DELETE /centres/{id}/classrooms/{roomId}`
- `GET/POST /classes`, `GET/PATCH/DELETE /classes/{id}`
- `POST /classes/{id}/publish` — Publish class (blocks if clash errors)
- `GET /classes/{id}/sessions` — List class sessions
- `POST /classes/{id}/clash-check` — Run clash validation
- `POST /classes/{classId}/clashes/{clashId}/resolve` — Resolve a clash
- `GET /classes/{id}/availability` — Real-time quota status

### Audit
- `GET /audit-logs` — Paginated audit logs (auth)

## Database Architecture

Multi-schema PostgreSQL:
- `auth` — users, roles, permissions, staff_profiles, audit_logs, security_events
- `course_catalogue` — seasons, categories, subjects, courses, course_text_versions
- `class_scheduling` — centres, classrooms, classes, class_sessions, clash_check_results, holidays

Cross-schema foreign keys used (e.g. `class_scheduling.classes.instructor_id → auth.users.id`).

## Dev Commands

```bash
composer dev          # Server + queue + vite concurrently
composer test         # Clear config + run tests
php artisan test      # Run Pest tests
vendor/bin/pint       # Lint with Laravel Pint
```

## CI/CD

GitHub Actions workflow at `.github/workflows/ci.yml`:
1. **Lint** — Laravel Pint
2. **Test** — Pest with PostgreSQL service
3. **Build** — Docker image (on push to main)

## API Documentation

Auto-generated OpenAPI docs (dev only): `http://localhost:8000/docs/api`

Log viewer (dev only): `http://localhost:8000/log-viewer`
