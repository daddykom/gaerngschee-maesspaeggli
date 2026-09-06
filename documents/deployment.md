# Deployment

Die Anwendung wird pro Umgebung in einem eigenen Verzeichnis installiert. Das
Verzeichnis darf beispielsweise `prod`, `test` oder `staging` heissen:

```text
/srv/gaerngschee/<umgebung>/
├── backend/
│   ├── .env
│   └── public/
└── frontend/
```

Der Webserver verwendet `<deployment>/frontend` als Document-Root. Requests
unter `/api/` werden an `<deployment>/backend/public/index.php` weitergeleitet.
Der Backend-Document-Root wird nicht direkt öffentlich ausgeliefert.

## Environment

Vor dem Start wird `backend/.env.example` nach `backend/.env` kopiert. Die
Datei enthält umgebungsspezifische Werte und darf nicht versioniert werden.
Alternativ kann der Prozess mit `GAERNGSCHEE_ENV_FILE` auf eine ausserhalb des
Deployment-Verzeichnisses liegende Environment-Datei zeigen.

Mindestens diese Werte müssen gesetzt werden:

```dotenv
APP_ENV=prod
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=gaerngschee_prod
DB_USER=...
DB_PASS=...
JWT_SECRET=...
FRONTEND_BASE_URL=https://example.org
MAILER_DSN=smtp://...
MAIL_FROM_ADDRESS=...
MAIL_FROM_NAME=Gärngschee-Mässpäggli
FSA_MODE=real
FSA_BASE_URL=https://...
FSA_ORGANIZATION_ID=...
FSA_ACCESS_KEY=...
FSA_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n...\n-----END PUBLIC KEY-----"
```

`APP_ENV` muss `test` oder `prod` sein. Wenn `FSA_MODE` nicht gesetzt ist,
wird in `test` automatisch der Fake und in `prod` der echte Fairgate-Service
verwendet. Für produktive Deployments soll `FSA_MODE=real` ausdrücklich gesetzt
werden.

## Installation

```bash
cd <deployment>/backend
composer install --no-dev --optimize-autoloader
vendor/bin/phinx migrate -e production
```

Die Datenbank wird vor dem Aktivieren des neuen Frontend-Builds migriert. Danach
wird der Inhalt des Angular-Builds nach `<deployment>/frontend` veröffentlicht.

## Testumgebung

Für die Integrationstests wird `backend/.env.integration` über Docker Compose
als `env_file` geladen. Diese Datei enthält ausschliesslich Testwerte und keine
Produktionszugangsdaten.

## Sicherheit

- `.env`-Dateien gehören nicht ins Repository.
- `JWT_SECRET`, Datenbankpasswörter und Fairgate-Schlüssel werden nur über die
  Prozessumgebung oder eine geschützte Environment-Datei bereitgestellt.
- Der Webserver darf nur `<deployment>/frontend` und die vorgesehenen API- und
  SPA-Rewrite-Regeln erreichen.
