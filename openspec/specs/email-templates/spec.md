# Email Templates Capability Spec

## Overview

Verwaltung von E-Mail-Vorlagen durch Mitarbeitende.

## Features

### F1: Vorlagen-Verwaltung

- Alle E-Mail-Typen online verwaltbar
- Bearbeitung von Betreff und Inhalt

### F2: Platzhalter

- Automatischer Ersatz beim Versand
- Vordefinierte Platzhalter pro Typ

### F3: Erinnerungs-Konfiguration

- Anzahl Erinnerungs-E-Mails global konfigurierbar
- Zeitliche Abstände zwischen Erinnerungen konfigurierbar

## E-Mail-Typen und Platzhalter

### Confirmation

| Platzhalter | Beschreibung |
|-------------|--------------|
| {{firstName}} | Vorname des Klienten |
| {{registrationId}} | Anmeldungs-ID |
| {{createdAt}} | Erstellungsdatum |

### Waitlist

| Platzhalter | Beschreibung |
|-------------|--------------|
| {{firstName}} | Vorname des Klienten |
| {{position}} | Wartelistenposition |

### Qualification

| Platzhalter | Beschreibung |
|-------------|--------------|
| {{firstName}} | Vorname des Klienten |
| {{qrCode}} | QR-Code URL |
| {{pickupLocation}} | Abholort |

### Reminder

| Platzhalter | Beschreibung |
|-------------|--------------|
| {{firstName}} | Vorname des Klienten |
| {{pickupDate}} | Abholdatum |
| {{pickupLocation}} | Abholort |

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/email-templates` | List all templates |
| GET | `/api/email-templates/{type}` | Get template by type |
| PUT | `/api/email-templates/{type}` | Update template |
| GET | `/api/email-templates/config` | Get reminder config |
| PUT | `/api/email-templates/config` | Update reminder config |

## Fachliche Regeln

- Alle E-Mail-Typen werden online verwaltet
- Änderungen gelten für zukünftige E-Mails
- Platzhalter werden automatisch ersetzt

## Implementation Status

| Component | Status |
|-----------|--------|
| Template CRUD | ✗ Not implemented |
| Placeholder replacement | ✗ Not implemented |
| Reminder config | ✗ Not implemented |
