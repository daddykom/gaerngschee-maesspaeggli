# Gratisangebote-Karte

Open-Source-Webapplikation zur Unterstützung von Menschen mit wenig finanziellen Mitteln bei der Suche nach kostenlosen Angeboten, sowie zur Hilfe für einsame Menschen beim Entdecken von Aktivitäten und sozialen Kontakten in ihrer Region.

## Zielgruppe

- Menschen mit geringem Einkommen
- Einsame oder sozial isolierte Menschen
- Gemeinden
- Soziale Organisationen
- Vereine

## Hauptfunktionen

### Angebotssuche

Kostenlose Angebote können über folgende Ansichten gesucht werden:

- **Kartenansicht** - Angebote auf einer interaktiven Karte
- **Listenansicht** - Angebote als Liste

Zwischen beiden Ansichten kann jederzeit gewechselt werden.

### Filterung

Angebote können über Kategorien gefiltert werden:

- Essen
- Freizeit
- Kultur
- Sport
- Beratung
- Treffpunkte
- Bildung

### Angebotsdetails

Angebote werden auf der Karte dargestellt. Zu jedem Angebot können Detailinformationen angezeigt werden.

### Erfassung und Redaktion

Benutzer können eigene Angebote erfassen. Neu erfasste Angebote erhalten zuerst den Status `pending` und werden erst nach Prüfung durch einen Redaktor öffentlich angezeigt.

Ein Redaktor kann:

- das Angebot freigeben
- das Angebot ablehnen
- das Angebot bearbeiten
- Kategorien ergänzen oder ändern
- Texte, Orte, Zeiten und andere Angaben korrigieren

## Technologiestack

### Frontend

- Angular
- NgRx
- TypeScript

### Backend

- PHP
- Slim Framework

### Datenbank

- MariaDB

### Kartenlösung

- OpenFreeMap als Kartenquelle
- MapLibre für die Kartendarstellung

## Plattform

Die Anwendung wird als Progressive Web App (PWA) umgesetzt:

- Installation auf Desktop und Mobilgeräten
- Responsive Design
- Barrierefreiheit (Accessibility First)
- Mehrsprachigkeit

## Entwicklung

### Entwicklungsumgebung

- macOS
- WebStorm
- Docker

Die lokale Docker-Umgebung soll dem Zielsystem bei Cyon möglichst ähnlich sein.

### Hosting

- Cyon.ch (Webhosting Double)

### Versionsverwaltung

- Git
- GitHub

Das Projekt wird als Open Source veröffentlicht.

## Qualitätssicherung

### Teststrategie

Unit Tests sind vorgesehen für:

- Angular Components
- Angular Services
- NgRx Reducer
- NgRx Selectors
- NgRx Effects
- PHP Backend-Code
- Pure Functions
- Validierungslogik
- Mapping- und Transformationslogik

Zusätzlich soll Playwright für End-to-End-Tests geprüft werden.

### Continuous Integration

Vor einem Merge in den Hauptbranch müssen automatisch ausgeführt werden:

- Unit Tests
- Linting
- Build-Prüfung
- Backend-Tests
- optional Playwright-Tests

GitHub Actions werden für die automatischen Prüfungen verwendet.

## Deployment

Nach Änderungen auf dem Hauptbranch soll die Anwendung automatisch auf den Server deployt werden:

- Automatisches Deployment über GitHub Actions
- Deployment auf Cyon

## Programmierstil

Der Programmierstil soll möglichst funktional sein:

- Functional Style bevorzugen
- Pure Functions bevorzugen
- Immutable Data bevorzugen
- Klassen nur verwenden, wo sie sinnvoll oder frameworkbedingt nötig sind
- Geschäftslogik möglichst aus Components und Services herauslösen
- Seiteneffekte klar begrenzen
- Typen explizit und streng verwenden
- Keine unnötige Objektorientierung

## Architekturprinzipien

- Open Source
- Einfach installierbar
- Für Gemeinden und Organisationen wiederverwendbar
- Wartbar und langfristig betreibbar
- Mobile First
- Accessibility First
- Datenschutzfreundlich
- Möglichst geringe Betriebskosten
- Klare Trennung zwischen Frontend, Backend und Datenbank
- API-first zwischen Frontend und Backend

## Noch offene Entscheidungen

- Benutzer- und Rollenmodell im Detail
- Workflow für Ablehnung und Rückfragen zu Angeboten
- Mehrsprachigkeitskonzept
- Import- und Exportmöglichkeiten
- Moderations- und Prüfprozesse
- Playwright-Setup
- Deployment-Strategie zu Cyon