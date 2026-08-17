# Authentication Capability Spec

## Overview

User authentication and authorization for the Gärngschee-Mässpäggli platform.

## Benutzerrollen

| Rolle | Beschreibung |
|-------|--------------|
| Besucher | Nicht angemeldete Benutzer |
| Klient | Personen/Familien mit knappen Mitteln |
| Spender | Finanzen Mässpäggli |
| Mitarbeiter | Vereinsmitarbeiter |
| Administrator | Systemverwaltung |

## Features

### F1: Besucher (Anonymous)

- View public information
- Access donation page
- Submit registration

### F2: Klient (Client)

- Manage own registration
- View registration status
- Receive QR code after qualification
- Update personal information

### F3: Spender (Donor)

- Select number of Mässpäggli
- Redirect to Payrexx for payment
- No account required (payment handles identity)

### F4: Mitarbeiter (Staff)

- Manage all registrations
- Manage waitlist
- Verify QR codes
- Confirm pickups
- Manage email templates
- Manage reminder rules

### F5: Administrator

- Manage master data
- System configuration
- User management

## Security Considerations

- Password hashing (bcrypt)
- Session tokens (secure, httpOnly cookies)
- CSRF protection
- Rate limiting on auth endpoints

## Implementation Status

| Component | Status |
|-----------|--------|
| Besucher (public access) | ✗ Not implemented |
| Klient authentication | ✗ Not implemented |
| Spender (Payrexx handles) | ✗ Not implemented |
| Mitarbeiter auth | ✗ Not implemented |
| Admin auth | ✗ Not implemented |

## Open Decisions

- Session vs JWT tokens?
- Fairgate integration for eligibility?
- Existing Fairgate accounts or separate?

## Implementation Plan: Slim Backend Authorization

Dieser Plan betrifft ausschließlich den Slim-Backend-Teil. Die Umsetzung beginnt
erst nach einer separaten Freigabe von Schritt 1.

### Zielmodell

- Öffentliche Endpunkte liegen unter `/public/*`.
- Geschützte Endpunkte liegen unter einem Gruppenpfad: `/client/*`, `/user/*`
  oder `/admin/*`.
- Es gibt genau drei angemeldete Benutzergruppen: `client`, `user`, `admin`.
- Jeder Benutzer gehört genau einer Gruppe an.
- Nach erfolgreicher Authentifizierung wird die `user_id` in der Session abgelegt.
- Die Benutzergruppe wird über die `user_id` aus der Datenbank ermittelt.
- Geschützte Zugriffe werden über die URL-Struktur und Middleware geregelt.
  Nicht registrierte URLs liefern keinen Zugriff.
- Die API verwendet signierte JWTs. Die JWT-Bibliothek wird über Composer
  installiert und verwaltet.
- Das Frontend kann das JWT später im Local Storage speichern und als Bearer-Token
  senden. Diese Frontend-Integration ist nicht Teil dieses Plans.

### Umsetzungsschritte

#### Schritt 1: Backend-Grundlagen und Abhängigkeiten

- Vorhandene Slim-Struktur, Session-Initialisierung und Route-Registrierung prüfen.
- `firebase/php-jwt` über Composer installieren.
- Konfiguration für JWT-Schlüssel, Algorithmus und Ablaufzeit über
  Umgebungsvariablen vorbereiten.
- Noch keine Authentifizierungslogik implementieren.

**Freigabe erforderlich:** Dieser Schritt wird erst nach ausdrücklicher Bestätigung
gestartet.

#### Schritt 2: Benutzerzugriff im Repository

- `UserRepository::findById()` ergänzen.
- Benutzergruppe anhand der `user_id` laden.
- Registrierung mit `password_hash()` und `PASSWORD_DEFAULT` vorbereiten.
- Login-Prüfung mit `password_verify()` vorbereiten.
- Keine Passwörter in API-Antworten ausgeben.

#### Schritt 3: JWT- und Session-Service

- Service für JWT-Erstellung und -Prüfung erstellen.
- JWT enthält mindestens `sub` mit der Benutzer-ID sowie `iat` und `exp`.
- Nach erfolgreichem Login `user_id` in der Session speichern.
- Logout löscht die Session und invalidiert die serverseitige Anmeldung.
- Bearer-Token aus dem `Authorization`-Header lesen und prüfen.

#### Schritt 4: Öffentliche und Auth-Routen

- Öffentliche Route-Struktur unter `/public/*` registrieren.
- `POST /auth/register` für die Registrierung erstellen.
- `POST /auth/login` für Login und JWT-Ausgabe erstellen.
- `POST /auth/logout` für Session-Löschung erstellen.
- `GET /auth/me` für den aktuell angemeldeten Benutzer erstellen.
- Fehlerantworten und HTTP-Statuscodes einheitlich definieren.

#### Schritt 5: Gruppen-Middleware

- Authentifizierungs-Middleware für gültige Session oder gültiges JWT erstellen.
- Gruppen-Middleware für `client`, `user` und `admin` erstellen.
- Gruppen-Middleware nur auf die jeweiligen URL-Gruppen anwenden.
- `AdminRoutes` auf `/admin/*` umstellen und die Inline-Prüfung entfernen.
- Sicherstellen, dass kein Benutzer auf eine fremde Gruppenroute zugreifen kann.

#### Schritt 6: Anwendung integrieren und prüfen

- Alle Routen in der Slim-Anwendung registrieren.
- Session- und CORS-Verhalten für Authorization-Header prüfen.
- Tests für öffentliche Route, Login, Logout, ungültiges JWT und jede Benutzergruppe
  ergänzen.
- Phinx-/PHP- und Anwendungstests ausführen.

#### Schritt 7: Plan entfernen

- Erst wenn Schritt 6 abgeschlossen und geprüft ist, diesen
  `Implementation Plan`-Abschnitt aus dieser Spec entfernen.
