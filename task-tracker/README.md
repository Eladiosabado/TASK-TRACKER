# Task Tracker

A full-stack task management application with JWT authentication, category management, search/filter/pagination, and strict per-user data isolation.

## Alternative Stack Explanation

This project uses PHP, MySQL, HTML5, CSS3, and Vanilla JavaScript as an alternative stack permitted by the coding exam. This stack was selected because it is the developer's strongest and most productive stack while maintaining all required functionality.

No frameworks (Laravel, React, Vue, Angular, Express) are used — only core PHP 8+, PDO, and the Fetch API.

---

## Features

- JWT-based authentication (register, login, me, logout)
- Passwords hashed with `password_hash()` / verified with `password_verify()`
- Full task CRUD, strictly scoped to the authenticated user
- Category management (global, unique names) with safe delete handling for categories still in use
- Server-side search (title `LIKE`), status filter, category filter
- Server-side pagination with page/limit/total/total_pages
- Task statistics (total / pending / in progress / completed)
- Responsive dashboard: stat cards, filterable/sortable task table, modals for create/edit/delete
- Toast notifications, loading states, empty states, inline form validation
- Defense against cross-user access on every task/category endpoint

---

## Tech Stack

| Layer          | Technology                         |
|----------------|-------------------------------------|
| Backend        | PHP 8+, PDO (prepared statements)   |
| Database       | MySQL                               |
| Auth           | JWT (custom HS256 implementation, no external dependency) |
| Frontend       | HTML5, CSS3, Vanilla JavaScript, Fetch API |
| Server         | Apache (XAMPP)                      |

---

## Database Schema

### `users`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | PK |
| name | VARCHAR(100) | |
| email | VARCHAR(150) | UNIQUE |
| password | VARCHAR(255) | bcrypt hash via `password_hash()` |
| created_at / updated_at | DATETIME | |

### `categories`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | PK |
| name | VARCHAR(100) | UNIQUE, global (not per-user) |
| created_at / updated_at | DATETIME | |

### `tasks`
| Column | Type | Notes |
|---|---|---|
| id | INT UNSIGNED AUTO_INCREMENT | PK |
| title | VARCHAR(255) | |
| description | TEXT | nullable |
| status | ENUM('pending','in_progress','completed') | default `pending` |
| due_date | DATE | nullable |
| category_id | INT UNSIGNED | FK → categories.id (`RESTRICT` on delete) |
| user_id | INT UNSIGNED | FK → users.id (`CASCADE` on delete) |
| created_at / updated_at | DATETIME | |

**Relationships:**
- One user → many tasks
- One category → many tasks
- A task belongs to exactly one user and exactly one category

Indexes: `user_id`, `category_id`, `status`, `due_date`, `title`.

---

## Project Structure

```
task-tracker/
├── api/
│   ├── auth/            (register, login, me, logout)
│   ├── categories/      (index, create, update, delete)
│   ├── tasks/            (index, show, create, update, delete, stats)
│   └── health.php
├── config/               (database.php, config.php, constants.php, env.php)
├── middleware/auth.php   (JWT verification)
├── includes/             (bootstrap, response, validation, jwt, functions, header, footer)
├── assets/
│   ├── css/              (style.css, responsive.css)
│   └── js/               (api.js, auth.js, dashboard.js, tasks.js, categories.js, utils.js)
├── pages/                (login.php, register.php, dashboard.php, 404.php)
├── uploads/, logs/
├── index.php
├── database.sql
├── .env.example
└── .htaccess
```

---

## API Documentation

All responses are JSON. Success: `{ "success": true, "message": "...", "data": {...} }`. Error: `{ "success": false, "message": "..." }` (with an `errors` object for field-level validation failures).

### Auth
| Method | Endpoint | Auth | Body |
|---|---|---|---|
| POST | `/api/auth/register.php` | No | `name, email, password` |
| POST | `/api/auth/login.php` | No | `email, password` → returns JWT |
| GET | `/api/auth/me.php` | Yes | — |
| POST | `/api/auth/logout.php` | Yes | — |

### Categories
| Method | Endpoint | Auth |
|---|---|---|
| GET | `/api/categories/index.php` | Yes |
| POST | `/api/categories/create.php` | Yes |
| PUT | `/api/categories/update.php?id=1` | Yes |
| DELETE | `/api/categories/delete.php?id=1` | Yes (blocked with 409 if tasks still reference it) |

### Tasks
| Method | Endpoint | Auth |
|---|---|---|
| GET | `/api/tasks/index.php?status=&category_id=&search=&page=&limit=` | Yes |
| GET | `/api/tasks/show.php?id=1` | Yes |
| POST | `/api/tasks/create.php` | Yes |
| PUT | `/api/tasks/update.php?id=1` | Yes |
| DELETE | `/api/tasks/delete.php?id=1` | Yes |
| GET | `/api/tasks/stats.php` | Yes |

All task/category queries are scoped with `WHERE user_id = <authenticated user>` — the `user_id` is **always** derived from the validated JWT and never trusted from the request body.

Auth header format: `Authorization: Bearer <token>`

---

## Installation (XAMPP / Local Windows)

1. **Copy the project**
   Place the folder at `C:\xampp\htdocs\task-tracker`.

2. **Start Apache and MySQL** in the XAMPP Control Panel.

3. **Create the database** via phpMyAdmin (`http://localhost/phpmyadmin`):
   - Click "Import", choose `database.sql`, and run it.
   - Or via CLI: `mysql -u root -p < database.sql`

4. **Configure environment**
   Copy `.env.example` to `.env` in the project root and adjust as needed:
   ```
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=task_tracker
   DB_USER=root
   DB_PASSWORD=
   JWT_SECRET=CHANGE_THIS_TO_A_LONG_RANDOM_SECRET
   APP_URL=http://localhost/task-tracker
   FRONTEND_URL=http://localhost/task-tracker
   ```
   Generate a strong `JWT_SECRET`, e.g. via `php -r "echo bin2hex(random_bytes(32));"`.

5. **Visit the app**
   `http://localhost/task-tracker/` → redirects to the login page.

6. **Create your test account**
   Use the Register page to create an account (passwords are hashed via `password_hash()` — no plaintext credentials are ever inserted into the database).

---

## Running Locally / Testing

### Manual API smoke test (curl)
```bash
curl http://localhost/task-tracker/api/health.php

curl -X POST http://localhost/task-tracker/api/auth/register.php \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123"}'

curl -X POST http://localhost/task-tracker/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```
Use the returned `token` for subsequent authenticated requests:
```bash
curl http://localhost/task-tracker/api/auth/me.php \
  -H "Authorization: Bearer <token>"
```

### Test checklist

**Auth:** register, duplicate email (409), login, wrong password (401), logout, page refresh keeps session, invalid JWT (401), missing JWT (401).

**Categories:** list, create, duplicate name (409), update, delete (with and without tasks assigned).

**Tasks:** create, read, update, delete, search by title, filter by status, filter by category, pagination (page/limit), statistics accuracy.

**Cross-user security:**
1. Register User A and User B.
2. User A creates Task A; User B creates Task B.
3. Using User A's token, attempt to `GET/PUT/DELETE` Task B's id → all should return `404 Not Found` (never leaking that the task exists under another user).
4. Repeat symmetrically for User B against Task A.

---

## Deployment

1. Point the app at a PHP 8+ / MySQL-compatible host.
2. Set real environment variables (`DB_*`, `JWT_SECRET`, `APP_URL`, `FRONTEND_URL`) via your host's environment panel, or a production `.env` file that is **not** committed to git.
3. Import `database.sql` into your production database.
4. Ensure HTTPS is enabled; the app works over HTTP locally but production should always use HTTPS for token security.
5. If the frontend and backend ever run on different origins, update `FRONTEND_URL` in `config.php`/`.env` so CORS headers are correct.
6. Confirm `.htaccess` is honored (`AllowOverride All` on Apache) so directory listing stays disabled and dotfiles stay protected.
7. Run through the full test checklist above against the live URL.

---

## Known Limitations

- JWT is stateless: logout is client-side (token removal). There is no server-side token blacklist/revocation.
- Categories are global rather than per-user, per the required schema — any authenticated user can create/edit/delete any category.
- No file uploads are implemented; the `uploads/` folder is reserved for future use.
- No email verification or password-reset flow (out of scope for the exam).

---

## Test Account Instructions

No seeded user accounts exist in `database.sql` (by design — no plaintext passwords are ever inserted directly). To test:

1. Go to `/pages/register.php`
2. Register with any name/email/password (password ≥ 8 characters)
3. Log in at `/pages/login.php`

Sample categories (`School`, `Work`, `Personal`, `Projects`) are pre-seeded via `database.sql`.
