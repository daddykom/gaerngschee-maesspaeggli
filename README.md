# Gärngschee-Mässpäggli

Open-Source-Webplattform für die Verwaltung von Spenden und die Verteilung von Mässpäggli an Menschen mit knappen finanziellen Mitteln.

## Über das Projekt

Die Gärngschee-Mässpäggli-Plattform ermöglicht es Spendern, Mässpäggli zu finanzieren, und Klienten, sich für den Erhalt von Mässpäggli zu registrieren. Die Anwendung verwaltet Anmeldungen, Wartelisten und die QR-code-basierte Abgabe.

## Benutzerrollen

### Besucher
Nicht angemeldete Benutzer können öffentliche Informationen ansehen, zur Spendenseite wechseln und sich für Mässpäggli anmelden.

### Klient
Personen oder Familien mit knappen finanziellen Mitteln. Klienten können ihre Anmeldung bearbeiten, den Status verfolgen, Benachrichtigungen empfangen und erhalten einen QR-Code für die Abholung.

### Spender
Personen, welche Mässpäggli finanzieren. Spender wählen die Anzahl Mässpäggli aus und werden zur Payrexx-Spendenseite weitergeleitet.

### Mitarbeiter
Mitarbeiter des Vereins Gärngschee mit Zugriff auf alle administrativen Funktionen: Anmeldungen verwalten, Warteliste führen, QR-Codes prüfen, Abgaben bestätigen und E-Mail-Vorlagen konfigurieren.

### Administrator
Verwaltung der Anwendung: Stammdaten, Systemkonfiguration und Benutzerverwaltung.

## Kernfunktionen

### Spenden
- Weiterleitung zu Payrexx für sichere Zahlungsabwicklung
- Auswahl der Anzahl Mässpäggli
- Automatische Erfassung erfolgreicher Spenden

### Anmeldung
- Erfassung und Bearbeitung von Anmeldungen
- Erfassung mehrerer Kinder pro Anmeldung
- Altersgruppen-Zuordnung pro Kind
- Zusätzliche Informationen

### Berechtigungsprüfung
- Übernahme der Berechtigungsinformationen aus Fairgate
- Anzeige des Prüfungsstatus

### Warteliste
- Automatische Aufnahme bei ungenügender Verfügbarkeit
- Nachträgliche Qualifikation bei Verfügbarkeit

### Qualifikation
- Automatische Zusage bei genügend Mässpäggli
- Versand der Informations-E-Mail mit QR-Code

### Abgabe
- QR-Code-Prüfung
- Anzeige der Anmeldung
- Bestätigung der Abgabe (auch teilweise)
- Rückgängig-Machen durch berechtigte Mitarbeitende

### Benachrichtigungen
- Bestätigungs-E-Mail nach Anmeldung
- Information für Wartelisten-Klienten
- Informations-E-Mail mit QR-Code nach Qualifikation
- Konfigurierbare Erinnerungs-E-Mails

### E-Mail-Verwaltung
- Verwaltung aller E-Mail-Vorlagen
- Bearbeitung von Betreff und Inhalt
- Platzhalter-Unterstützung
- Konfiguration der Erinnerungsintervalle

## Technologiestack

### Frontend
- Angular 21
- NgRx (Store, Effects)
- Angular Material
- TypeScript
- Progressive Web App (PWA)
- Nx Build-System

### Backend
- PHP 8
- Slim Framework 4
- Phinx (Database Migrations)

### Datenbank
- MariaDB

### Integrationen
- Payrexx (Zahlungsabwicklung)
- Fairgate (Berechtigungsprüfung)

## Entwicklung

### Voraussetzungen
- Docker
- Node.js
- PHP 8
- Composer

### Setup

```bash
# Docker-Umgebung starten
docker-compose up -d

# Datenbank migrieren
cd frontend && npm run db:migrate

# Datenbank seeden (Entwicklungsdaten)
cd frontend && npm run db:seed

# Dependencies installieren
cd frontend && npm install
cd backend && composer install
```

### Verfügbare Scripts

#### Frontend (frontend/package.json)
```bash
npm start              # Entwicklungserver starten
npm run build          # Produktions-Build
npm run test           # Unit Tests
npm run e2e            # End-to-End Tests
npm run lint           # Linting
npm run db:start       # Docker Datenbank starten
npm run db:stop        # Docker Datenbank stoppen
npm run db:migrate     # Migrationen ausführen
npm run db:seed        # Seed-Daten laden
npm run db:setup       # Datenbank initialisieren
npm run db:reset       # Datenbank zurücksetzen
```

#### Backend (backend/composer.json)
```bash
composer install       # Dependencies installieren
composer update        # Dependencies aktualisieren
vendor/bin/phinx       # Phinx CLI für Migrationen
```

### Tests
```bash
# Unit Tests (Frontend)
npm test

# Unit Tests (Backend)
cd backend && vendor/bin/phpunit

# End-to-End Tests
npm run e2e
```

## Architekturprinzipien

- **Mobile First** – Optimiert für mobile Geräte
- **Accessibility First** – Barrierefreiheit als Priorität
- **Privacy-friendly** – Datenschutzfreundlich
- **Open Source** – Frei verfügbar und transparent
- **API-first** – Klare Trennung zwischen Frontend und Backend
- **Low operating costs** – Minimale Betriebskosten
- **Clear separation** – Frontend, Backend und Datenbank sind unabhängig

## Projektstruktur

```
├── frontend/               # Angular SPA
│   ├── src/
│   │   ├── app/           # Angular Application Code
│   │   └── environments/  # Environment-Konfiguration
│   ├── project.json       # Nx Konfiguration
│   └── package.json       # Node.js Dependencies
├── backend/               # PHP Slim API
│   ├── src/              # PHP Application Code
│   └── composer.json      # PHP Dependencies
├── db/                   # Datenbank
│   ├── migrations/       # Phinx Migrationen
│   └── seeds/            # Seed-Daten
├── docker-compose.yml    # Docker Konfiguration
└── openspec/            # OpenSpec Spezifikationen
```

## Lizenz

MIT
