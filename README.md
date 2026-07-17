# HKFTU Training System — Backend

Backend API for the HKFTU Continuing Education Centre training operations platform — online registration, class scheduling, payments, attendance, certificates, instructor finance, and reporting.

## Stack

PHP 8.3 · Laravel 13 · PostgreSQL 14+ (multi-schema) · Redis · Sanctum · Spatie Permission · nwidart/laravel-modules · DomPDF · RazerMs · maatwebsite/excel

## Requirements

PHP 8.3+ (`pdo_pgsql`, `redis`, `mbstring`, `xml`, `zip`, `exif`) · PostgreSQL 14+ · Redis 6+ · Composer 2

## Quick Start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan db:fresh --seed
php artisan storage:link
php artisan serve
```

> Use `db:fresh --seed` (not `migrate:fresh --seed`) — handles PostgreSQL custom schemas automatically.

## Seed Accounts

| Email                | Role           |
| -------------------- | -------------- |
| admin@hkftu.org      | system_admin   |
| planner@hkftu.org    | course_planner |
| manager@hkftu.org    | centre_manager |
| counter@hkftu.org    | counter_staff  |
| instructor@hkftu.org | instructor     |
| finance@hkftu.org    | finance_staff  |
| learner1@example.com | public_learner |

Password for all: `password`

## Modules

| Module            | Description                                                                           |
| ----------------- | ------------------------------------------------------------------------------------- |
| Auth              | Login, RBAC (9 roles, 23 permissions), audit, learner/instructor profiles, membership |
| CourseCatalogue   | Subjects, courses, seasons, categories, course text versioning, notices               |
| ClassScheduling   | Classes, sessions, centres, classrooms, clash detection (8 types), holidays           |
| Enrolment         | Seat reservation, enrolment, waitlist, transfers, counter enrolment                   |
| Payment           | Manual + RazerMs payment, receipts, refunds, coupons                                  |
| Membership        | Member verification (mock HQ API), snapshots, pricing eligibility                     |
| Attendance        | Attendance grid, batch save, submit, learner history                                  |
| Certificate       | Eligibility calculation, batch issue, PDF generation, reprint                         |
| InstructorFinance | Fee rules, fee calculation, payment batches, cheque records, contracts                |
| Reporting         | _Sprint 13_                                                                           |
| Notification      | _Sprint 14_                                                                           |

## Database

9 PostgreSQL schemas: `auth`, `course_catalogue`, `class_scheduling`, `enrolment`, `attendance`, `payment`, `certificate`, `instructor_finance`, `public`

## Commands

```bash
php artisan db:fresh --seed    # Drop schemas + migrate + seed
php artisan serve              # Dev server
php artisan test               # Pest tests
vendor/bin/pint                # Lint
php artisan route:clear        # Clear route cache
php artisan storage:link       # File upload symlink
php artisan schedule:run
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```
