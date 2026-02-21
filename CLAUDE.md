# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Elliteam** is a Symfony 7.2 recruitment platform with two main public flows:
1. Candidates submit job applications (CV upload, availability)
2. Companies post job offers (attachment upload, need type)

Both flows feed into an EasyAdmin 4 admin dashboard with statistics and CRUD management.

## Development Environment

All development runs inside Docker. Use `make` commands exclusively — do not run PHP/Symfony commands directly on the host.

```bash
make install       # First-time setup: build + start + composer + db create + migrate
make up            # Start containers
make down          # Stop containers
make shell         # Open PHP container shell (as app user)
make shell-root    # Open PHP container shell (as root)
make logs          # Tail all container logs
make logs-php      # Tail PHP container logs only
```

**Access points:**
- App: `http://localhost:8082`
- PhpMyAdmin: `http://localhost:8081`
- MailHog (email): `http://localhost:1080`

## Common Commands

```bash
# Database
make db-create     # Create database
make db-migrate    # Run pending migrations
make db-reset      # Drop + recreate + migrate

# Development
make clear-cache   # Clear Symfony cache
make composer      # Run composer install
make composer-update  # Update dependencies
make migration     # Generate new migration from entity changes

# Symfony console (pass CMD)
make symfony-console CMD="debug:router"
make symfony-console CMD="make:entity"

# Code generation
make entity NAME="Product"
make controller NAME="ProductController"

# Tests
make test          # Run full test suite
```

To run a single test:
```bash
make shell
# Inside container:
php bin/phpunit tests/path/to/MyTest.php
php bin/phpunit --filter testMethodName
```

## Architecture

### Domain Model

| Entity | Purpose |
|---|---|
| `Candidature` | Job application submitted by a candidate |
| `CompanyOffer` | Job offer posted by a company |
| `User` | Admin/system user with Symfony security |
| `Role` | Roles linked to users via `user_role` junction table |

### Request Flow

```
HTTP Request
  → Nginx (port 8080)
  → PHP-FPM (port 9000)
  → Symfony Kernel
  → Controller
  → Form / Service / Repository
  → Twig template or JSON response
```

### Key Patterns

**File uploads** — All uploads go through `src/Service/FileUploader.php`. It generates a slug-safe filename and stores files under `public/uploads/{subfolder}/`. The subfolder (`cv/`, `company_offers/`) is passed by the controller. The upload directory is configured as a container parameter in `config/services.yaml`.

**Email sending** — Both controllers send emails via Symfony Mailer after successful form submission: a confirmation to the submitter and a notification to `rh@elliteam.com`. Email templates live in `templates/emails/`. MailHog captures all mail in development (port 1080).

**Async messaging** — `config/packages/messenger.yaml` uses a Doctrine transport. Failed messages are stored in `messenger_messages` table with exponential backoff retry. Run workers inside the container with `php bin/console messenger:consume`.

**Admin dashboard** — `src/Controller/Admin/DashboardController.php` aggregates repository statistics (counts by status/type). Each entity has a dedicated `*CrudController` under `src/Controller/Admin/`. EasyAdmin handles list/create/edit/delete automatically.

**Security** — `config/packages/security.yaml` uses form login pointing to `/admin/login`. The `User` entity implements `UserInterface` and stores roles both as a JSON field and via the `Role` entity (ManyToMany). Access control restricts `/admin` to authenticated users.

### Environment Files

- `.env` — Committed defaults (do not put secrets here)
- `.env.dev` — Development overrides (committed)
- `.env.local` — Local machine overrides (not committed, takes precedence)
- `.env.test` — Test environment (used by PHPUnit)

Key variables: `DATABASE_URL`, `MAILER_DSN`, `MESSENGER_TRANSPORT_DSN`, `DEFAULT_URI`.

### Infrastructure (Docker services)

| Service | Image | Internal role |
|---|---|---|
| `php` | Custom PHP 8.3-FPM | Application runtime |
| `nginx` | nginx:alpine | Reverse proxy (port 8080) |
| `mysql` | mysql:8.0 | Primary database (db: `elliteam`) |
| `phpmyadmin` | phpmyadmin | DB GUI (port 8081) |
| `mailhog` | mailhog | SMTP catcher (port 1025/1080) |
| `redis` | redis:7 | Cache backend |
