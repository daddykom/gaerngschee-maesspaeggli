# Docker-Umgebung

## Container

| Container | Image | Status | Ports |
|-----------|-------|--------|-------|
| `gaerngschee-backend` | Custom (php:8.3-fpm-alpine) | Up | `8080→80` |
| `gaerngschee-database` | mariadb:10.11 | Up | `3306→3306` |

---

## Backend-Container (`gaerngschee-backend`)

**Image:** Custom Build aus `Dockerfile`

**Stack:**
- PHP 8.3 (FPM)
- Nginx (Webserver)
- Supervisor (Prozess-Manager)

**Konfiguration:**

| Datei | Zweck |
|-------|-------|
| `docker/nginx.conf` | Nginx Routing → PHP-FPM auf Port 9000 |
| `docker/supervisord.conf` | Startet PHP-FPM + Nginx |
| `docker/php.ini` | PHP-Config (Display Errors etc.) |

**Volumes:**
- `./backend:/var/www/html` (Code + Vendor)
- `./db:/var/www/db` (Migrations + Seeds)

**Environment:**
```
DB_HOST=database
DB_PORT=3306
DB_NAME=gaerngschee_dev
DB_USER=gaerngschee
DB_PASS=changeme
PHP_DISPLAY_ERRORS=1
```

---

## Database-Container (`gaerngschee-database`)

**Image:** `mariadb:10.11`

**Environment:**
```
MYSQL_ROOT_PASSWORD=rootpassword
MYSQL_DATABASE=gaerngschee_dev
MYSQL_USER=gaerngschee
MYSQL_PASSWORD=changeme
```

**Volumes:**
- `db_data:/var/lib/mysql` (persistent)

---

## Start/Stop Commands

```bash
# Alle Container starten
docker compose up -d

# Alle Container stoppen
docker compose down

# Einzelne Container
docker restart gaerngschee-backend
docker restart gaerngschee-database

# Logs anzeigen (live mit -f)
docker logs gaerngschee-backend --tail=100 -f
docker logs gaerngschee-database --tail=100 -f

# In Container rein shells
docker exec -it gaerngschee-backend sh
docker exec -it gaerngschee-database mysql -u root -prootpassword
```

---

## Datenbank Commands

```bash
# Migrationen ausführen
npm run db:migrate

# Seeds ausführen (Development)
npm run db:seed

# Seeds ausführen (Test)
npm run db:seed:test

# Setup (start + migrate + seed)
npm run db:setup

# Reset (stop + start + migrate + seed)
npm run db:reset

# Migration Status prüfen
docker exec gaerngschee-backend vendor/bin/phinx status -c /var/www/db/phinx.php
```

---

## Phinx Migration

**Config:** `db/phinx.php`

**Migrations:** `db/migrations/`

**Seeds:** `db/seeds/`

| Environment | Seed Path |
|-------------|-----------|
| development | `db/seeds/development/` |
| test | `db/seeds/test/` |
| production | `db/seeds/production/` |

---

## Wichtige Hinweise

### Service-Namen vs Container-Namen

| Service-Name (docker compose) | Container-Name (docker) |
|-------------------------------|-------------------------|
| `backend` | `gaerngschee-backend` |
| `database` | `gaerngschee-database` |

**Wichtig:** `docker compose exec` nutzt **Service-Namen** (`backend`, `database`), nicht Container-Namen!

```bash
# Richtig (Service-Name)
docker compose exec backend ...

# Falsch (Container-Name - funktioniert nicht!)
docker compose exec gaerngschee-backend ...
```

### Volume-Mapping

Das Volume `./backend:/var/www/html` spiegelt den Container-`vendor/` auf den Host. Änderungen an `composer.json` müssen daher im Container ausgeführt werden:

```bash
docker exec gaerngschee-backend composer update --working-dir=/var/www/html
```

### Troubleshooting

| Problem | Lösung |
|---------|--------|
| CORS-Fehler im Browser | Backend-Exception prüfen: `docker logs gaerngschee-backend` |
| Access denied für DB-User | User + Rechte erstellen (siehe `docker exec gaerngschee-database mysql -u root -prootpassword -e "GRANT ALL..."`) |
| Tabelle existiert nicht | `npm run db:migrate` ausführen |
| Phinx nicht gefunden | `docker exec gaerngschee-backend composer update --working-dir=/var/www/html` |