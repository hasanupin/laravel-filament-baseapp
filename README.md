# laravel-filament-baseapp
Laravel Filament Base Admin — Laravel 13 + Filament 5, running on Docker (PHP 8.4 Apache + MySQL 8.4).

## Start a new app from this base
This repo is meant to be copied. On GitHub, enable **Settings → Template repository** once. Then, for each new app:

1. Click **Use this template**, create the new repo, and clone it.
2. Name it and pick a free port. Other ports follow the app port, so copies can run side by side:
   ```bash
   bin/new-project inventory 8091   # http://localhost:8091, MySQL 3308, Mailpit 8026
   ```
   This sets `APP_NAME`, `APP_SLUG` (the Docker name prefix), `DB_DATABASE` and the host ports in `.env.example` and `.env`. Running it again is safe.
3. Continue with **Setup** below. To add your own modules, see "Adding a module" in `CLAUDE.md`.

## Containers
Names are `<APP_SLUG>-…`. The defaults below are for `APP_SLUG=baseapp`.

| Container | Purpose | Host port |
|-----------|---------|-----------|
| `baseapp-app` | PHP 8.4 + Apache | `8090` (`APP_PORT`) |
| `baseapp-queue` | `php artisan queue:work` (database driver) | – |
| `baseapp-mysql` | MySQL 8.4 | `3307` (`FORWARD_DB_PORT`) |
| `baseapp-mailpit` | Catches outgoing mail (password reset) | SMTP `1025` (`FORWARD_MAILPIT_PORT`), inbox UI `8025` (`FORWARD_MAILPIT_DASHBOARD_PORT`) |

No Redis: cache, session and queue all use the `database` driver.

## Setup
```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```
Use `docker compose exec app bash` for a shell. `docker compose` commands work whatever the `APP_SLUG` is.
Open http://localhost:8090/ (it redirects to `/login` until you sign in) and log in with a sample account (password `password`):

| Email | Role | Menu access |
|-------|------|-------------|
| `superadmin@example.com` | Super Admin | everything |
| `admin@example.com` | Admin | Users + User Roles full CRUD, User Menu view/update |
| `staff@example.com` | Staff | Users, view only |

Override the admin login with `SEED_ADMIN_EMAIL` / `SEED_ADMIN_PASSWORD`. Forgot-password emails show up at http://localhost:8025.

## Profile
The avatar menu (top right) → **Profil** lets every signed-in user change their name, password and email, whatever their role.
- Changing the password or email asks for the current password.
- A new email is **not** applied right away. A link goes to the new address (valid 1 hour), and the old address gets a notice with a link to block the change. The email switches only after the link is clicked.

## Languages
Indonesian (`id`) is the default; English (`en`) is the fallback. Switch from the avatar menu or the link under the login form. The choice is kept in the session (`/locale/{id|en}`).

All UI text lives in `lang/{id,en}/app.php`; Laravel's auth, password and validation messages are in `lang/{id,en}/`. Code must call `__()` for labels; `tests/Feature/LocalizationTest.php` fails on hard-coded labels and on keys that exist in only one language.

Menu labels: a blank label in User Menu means "use the translated default". Typing a label overrides it in every language. Menu groups come from `app.menu_groups`, so add a key there (in both files) to create a new group.

## Settings menu
The sidebar's **Settings** group holds Users, User Roles and User Menu. **User Menu** edits each module's sidebar label, icon, group, order and visibility. Rows map one-to-one to modules in code, so they can be edited but not created or deleted. Hiding an item only removes it from the sidebar; access is still controlled by User Roles.

## Roles & permissions
Each role ticks View / Create / Update / Delete per module (stored as JSON on `roles.permissions`). Policies read it, so Filament hides menu items and buttons the role can't use. To add a module: add a case to `app/Enums/Module.php`, a policy for its model, `use HasManagedNavigation` on its resource, and a row in `MenuSeeder`.

Code layout: Filament resources/pages are thin bridges, validation rules live in `app/Http/Requests`, logic lives in `app/Services`.

The queue worker restarts until the migrations have run (it needs the `cache`/`jobs` tables). After you change code, run `docker compose restart queue` so the worker picks it up. If routes look stale (e.g. `/login` 404s), run `docker compose exec app php artisan optimize:clear`.

## Tests
```bash
docker compose exec app php artisan test
```
