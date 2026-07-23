# Troubleshooting Log

## Visiting `http://127.0.0.1:8080` returned 403, then 500

After scaffolding Laravel into the project and pointing nginx at it, the browser
returned a series of errors before the app finally served. Three distinct
problems were chained together.

---

### 1. `403 Forbidden`

**Cause:** The nginx docroot was set to `/var/www/html`, but Laravel's
entrypoint lives at `public/index.php`. With no `index.php` in the docroot and
directory listing disabled (nginx default), nginx has nothing to serve → 403.

**Fix:** Point the docroot at Laravel's `public/` folder in
`docker/nginx/default.conf`:

```nginx
root /var/www/html/public;
```

Then reload/restart nginx:

```bash
docker compose restart nginx
```

---

### 2. `500` — missing `.env` / `APP_KEY`

**Cause:** The Laravel install did not produce a `.env` file (no `.env.example`
was present to copy), so `APP_KEY` was unset. Laravel cannot boot without an
application key.

**Fix:** Create `.env` (pointed at the compose **service names**, not
`localhost`) and generate the key:

```dotenv
APP_KEY=
APP_URL=http://127.0.0.1:8080

DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=rave-digital
DB_USERNAME=rave-digital-user
DB_PASSWORD=rave-digital-pass

REDIS_HOST=redis
REDIS_PORT=6379
```

```bash
docker compose exec app php artisan key:generate
```

> Note: inside Docker, other services are reached by their compose service name
> (`db`, `redis`, `ollama`) — never `127.0.0.1`, which points at the container
> itself.

---

### 3. `500` — `tempnam(): file created in the system's temporary directory`

**Cause:** php-fpm runs as the `www-data` user (uid 33) inside the container.
The bind-mounted project files are owned by the host user (uid 1000) with only
group-write permission, so `www-data` could not write to `storage/framework/...`
(cache, sessions, compiled views). PHP fell back to the system temp dir and
raised a warning that Laravel turned into a 500.

**Fix (local dev):** Ensure the framework directories exist and are writable:

```bash
docker compose exec app bash -c '
  mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache &&
  chmod -R 777 storage bootstrap/cache
'
```

**Production-safe alternative:** `chown` these directories to `www-data` in the
Dockerfile (so it survives rebuilds), or run php-fpm as the host uid, instead of
`chmod 777`.

---

### Result

After all three fixes, `http://127.0.0.1:8080` returns **HTTP 200** and serves
the Laravel welcome page.
