# Project Overview

**Gärngschee-Mässpäggli** - Open-source Webplattform für die Verwaltung von Spenden und die Verteilung von Mässpäggli an Menschen mit knappen finanziellen Mitteln.

## Benutzerrollen

| Rolle | Beschreibung |
|-------|--------------|
| Besucher | Nicht angemeldete Benutzer |
| Klient | Personen/Familien mit knappen finanziellen Mitteln |
| Spender | Finanzieren Mässpäggli |
| Mitarbeiter | Vereinsmitarbeiter, Zugriff auf administrative Funktionen |
| Administrator | Systemverwaltung |

## Kernfunktionen

- Spendenverwaltung (Payrexx)
- Anmeldungen mit Kindern
- Berechtigungsprüfung (Fairgate)
- Warteliste
- Qualifikation & QR-Codes
- QR-basierte Abgabe
- E-Mail-Benachrichtigungen
- E-Mail-Vorlagenverwaltung

## Technologiestack

| Bereich | Technologie |
|---------|-------------|
| Frontend | Angular 21, NgRx, Angular Material, TypeScript, PWA |
| Backend | PHP 8, Slim 4, Phinx |
| Datenbank | MariaDB |
| Hosting | Cyon.ch |

## Vollständige Dokumentation

→ Siehe [openspec/specs/maesspaeggli.md](../openspec/specs/maesspaeggli.md) für das Fachkonzept.

→ Siehe [architecture.md](./architecture.md) für die Systemarchitektur.

→ Siehe [frontend-conventions.md](./frontend-conventions.md) für Angular-Entwicklungskonventionen.

→ Siehe [backend-conventions.md](./backend-conventions.md) für PHP-Entwicklungskonventionen.

→ Siehe [database-conventions.md](./database-conventions.md) für Datenbank-Migrationskonventionen.
