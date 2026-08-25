# AGENTS.md

Project information for AI assistants. This file is an **index** - see linked documents for details.

## Project Overview

**Gärngschee-Mässpäggli** - Open-source Webplattform für die Verwaltung von Spenden und die Verteilung von Mässpäggli an Menschen mit knappen finanziellen Mitteln.

→ See: [openspec/specs/maesspaeggli.md](./openspec/specs/maesspaeggli.md)

## Documentation Structure

```
gaerngschee/
├── AGENTS.md                    # This file (index)
├── documents/                   # Human-readable documentation
│   ├── project.md              # Project overview
│   ├── architecture.md         # System architecture
│   ├── frontend-conventions.md # Angular patterns (View/Container)
│   ├── backend-conventions.md  # PHP patterns
│   └── database-conventions.md # Database migrations
└── openspec/
    └── specs/                   # Capability specifications
```

## Quick Links

### For Humans
- [openspec/specs/maesspaeggli.md](./openspec/specs/maesspaeggli.md) - Fachkonzept
- [documents/project.md](./documents/project.md) - Project overview
- [documents/architecture.md](./documents/architecture.md) - System architecture
- [documents/directory-structure.md](./documents/directory-structure.md) - Aktuelle Verzeichnisstruktur

### For AI Assistants
- [openspec/specs/authentication/spec.md](./openspec/specs/authentication/spec.md) - User authentication & roles
- [openspec/specs/registrations/spec.md](./openspec/specs/registrations/spec.md) - Anmeldungen
- [openspec/specs/donations/spec.md](./openspec/specs/donations/spec.md) - Spenden
- [openspec/specs/platform/spec.md](./openspec/specs/platform/spec.md) - PWA, i18n, a11y

## View/Container Pattern

Angular components follow the View/Container pattern:

| Pattern | Description |
|---------|-------------|
| **ViewComponent** | Pure presentation, `@Input()`/`@Output()` only, no services |
| **ContainerComponent** | Manages state, injects Store/services, dispatches actions |

See: [documents/frontend-conventions.md](./documents/frontend-conventions.md#viewcontainer-pattern)

## Backend-Struktur

Das Backend verwendet eine fachlich organisierte Struktur unter `backend/src/`:

```
src/
├── Auth/
│   ├── Actions/
│   ├── Data/
│   └── Services/
├── Configuration/
│   ├── Actions/
│   └── Data/
├── Fairgate/
│   ├── Actions/
│   └── Services/
├── Registration/
│   ├── Actions/
│   └── Services/
├── Users/
│   ├── Actions/
│   └── Data/
├── Shared/
│   ├── Database/
│   ├── Http/
│   └── Mail/
├── Middleware/
├── PublicApi/
├── Routes/
└── Application.php
```

- Routes registrieren HTTP-Methode, Pfad, Action und Middleware. Sie enthalten keine Fachlogik.
- Actions verarbeiten einzelne Anwendungsfälle und geben PSR-7-Responses zurück.
- Services enthalten fachliche oder externe Integrationen und liegen im zuständigen Modul.
- Datenbankzugriff liegt im zuständigen `Data/`-Verzeichnis; gemeinsame Datenbank-Infrastruktur liegt unter `Shared/Database/`.
- Wiederverwendbare HTTP- und Mail-Helfer liegen unter `Shared/`.
- Namespace und Verzeichnis müssen der PSR-4-Struktur entsprechen.
- Die Admin-Fairgate-Testroute `/admin/fairgate/test` wird in `Routes/AdminRoutes.php` registriert und verwendet `Fairgate/Actions/FairgateTestAction`.
- Die früheren Sammelpfade `App\Services` und `App\Data` werden nicht mehr verwendet.
- Lokale Fairgate- und Mail-Zugangsdaten werden über Dateien unter `backend/config/` geladen. Versionierte `.dist`-Vorlagen enthalten keine echten Zugangsdaten; lokale Dateien wie `fairgate.local.php` und `mail.local.php` bleiben ignoriert.

## Backend-Tests

Backend-Tests folgen ebenfalls der fachlichen Struktur:

```
tests/
├── Auth/
├── Fairgate/
├── Registration/
├── Shared/
├── Users/
├── Middleware/
├── Routes/
└── Support/
```

Gemeinsames SQLite-Testsetup und Test-Doubles liegen unter `tests/Support/` und werden über `tests/bootstrap.php` geladen. Fachlogik soll direkt auf Action- oder Service-Ebene getestet werden; Route-Tests prüfen primär Registrierung und Middleware-Verhalten.

## Fairgate-Datenstruktur

Die Fairgate-Abfrage liefert die erweiterten Kontaktdaten in folgender Struktur:

```json
{
  "email": "jane@doe.ch",
  "fairgate": {
    "success": true,
    "message": "Data retrieved successfully",
    "data": {
      "contactType": "single_person",
      "contactId": 1,
      "salutation": "Informal",
      "first_name": "Jane",
      "last_name": "Doe",
      "gender": "Female",
      "correspondence_lang": "de",
      "wohnt_im_gleichen_haushalt": "Ja",
      "name_und_vorname_kind1": "Baby Doe",
      "name_und_vorname_kind2": "Jonny Doe",
      "name_und_vorname_kind3": "",
      "geburtsdatum_kind1": "2019-03-28T00:00:00Z",
      "geburtsdatum_kind2": null
    }
  }
}
```

Die Kinderfelder reichen von `name_und_vorname_kind1` bis `name_und_vorname_kind10` und von `geburtsdatum_kind1` bis `geburtsdatum_kind10`. Nicht vorhandene Kinder können als leerer String oder `null` geliefert werden.

Für die Weiterverarbeitung gelten folgende Regeln:

- Die Kinderanzahl entspricht der Anzahl nicht leerer `name_und_vorname_kindN`-Felder.
- `wohnt_im_gleichen_haushalt = "Ja"` ergibt zwei Erwachsene.
- Jeder andere Wert ergibt einen Erwachsenen.
- Die Anrede wird aus `correspondence_lang`, `gender` und `salutation` abgeleitet.
- Das Frontend erhält mindestens `fairgateUserExists`, `childrenCount`, `adultsCount` und `salutation`.

## Registrierungslink und Client-Login

- `POST /public/start` legt noch keinen Benutzer an.
- Die Route erstellt einen einmaligen Registrierungstoken und versendet einen Link an die angegebene E-Mail-Adresse.
- Registrierungstoken werden in der separaten Tabelle `registration_tokens` gespeichert, nur gehasht abgelegt und sind zehn Minuten gültig.
- `POST /auth/registration-login` konsumiert den Token.
- Erst beim erfolgreichen Token-Login wird ein neuer Benutzer mit der Gruppe `client` angelegt, falls noch keiner existiert.
- Bestehende `admin`- und `user`-Konten werden nicht in Client-Konten umgewandelt; die Antwort bleibt aus Sicherheitsgründen neutral.
- Nach dem Token-Login werden JWT, Client-Gruppe und die berechneten Fairgate-Werte an das Frontend geliefert.
- Das Frontend verarbeitet den Link unter `/client-login` und navigiert nach erfolgreichem Login zu `/order`.

## Arbeitsweise & Verhaltensregeln

### 1. Eigenständige Änderungen vermeiden

Du darfst Fehler oder Probleme im Code melden, aber DU SOLLST SIE NICHT EIGENMÄCHTIG KORRIGIEREN.

Immer erst fragen, bevor du:
- Tippfehler oder Syntaxfehler behebst
- Code umstellst oder restrukturierst
- Variablen oder Funktionen umbenennst
- Imports oder Dependencies änderst

**Beispiel:**
Du siehst einen Tippfehler in einer Variable. Frage nicht einfach "Fixed the typo", sondern:
> "Ich sehe einen Tippfehler in `userNme` → sollte das `userName` sein? Soll ich das korrigieren?"

---

### 2. Bestehende Tools verwenden

Für Konfiguration und Code-Generierung SOLLST DU IMMER die offiziellen Tools nutzen.

Konkrete Regeln:
- Nx: `nx generate`, `nx migrate`, `nx add` verwenden
- Angular: `ng generate`, `ng add`, `ng update` verwenden
- Niemals manuell Dateien ändern, wenn ein CLI-Tool existiert

### 2b. DB-Migrationen

Für alle Datenbank-Migrationen SOLL zwingend [Phinx](https://phinx.org/) verwendet werden. Niemals manuell SQL schreiben oder die DB-Struktur direkt ändern.

Wenn ein Agent eine Migration ausführt, darf ausschließlich die Developer-Datenbank (`development`) betroffen sein. Die Test- und Produktionsdatenbanken dürfen durch Agenten weder migriert noch zurückgerollt oder anderweitig strukturell verändert werden.

Siehe: [database-conventions.md](./documents/database-conventions.md)

**Beispiele:**

| Task | FALSCH | RICHTIG |
|------|--------|---------|
| Neue Component | Dateien manuell erstellen | `nx generate component` |
| Library hinzufügen | `package.json` manuell editieren | `nx add @nrwl/angular` |
| Migration | Manuelle File-Änderungen | `nx migrate` |

---

### 3. User-Änderungen respektieren

Wenn der User Code ändert, den du geschrieben hast, SOLLST DU DIESEN CODE NIE automatisch zurücksetzen oder überschreiben.

Bei Konflikten:
- NIEMALS automatisch "zurücksetzen" oder "wiederherstellen"
- IMMER erst nachfragen

**Beispiel:**
Du bemerkst, dass dein generierter Code geändert wurde. Frage:
> "Ich sehe, dass mein Code geändert wurde. Möchtest du, dass ich meine ursprüngliche Version wiederherstelle, oder soll ich mit deiner Version weiterarbeiten?"

---

### 4. Bei Unsicherheit fragen

Wenn du dir nicht sicher bist, was der User will:
- FRAGE. Stelle Clarifying Questions.
- Nummeriere mehrere Rückfragen eindeutig, damit der User jede Frage separat beantworten kann.
- NICHT: Annahmen treffen und handeln
- NICHT: Mehrere Optionen gleichzeitig implementieren

## API-Konventionen

### Fehlerantworten

API-Fehler werden als JSON mit einem standardisierten Fehlerobjekt zurückgegeben:

```json
{
  "error": {
    "code": "ERROR_CODE",
    "details": {}
  }
}
```

Wenn sinnvoll, wird zusätzlich der passende HTTP-Statuscode gesetzt.

### Login

Der Login verwendet `POST /auth/login` mit `email` und `password`.

- Die Zugangsdaten werden gegen einen Benutzer in der Datenbank geprüft.
- Das Passwort wird mit dem gespeicherten Hash verifiziert.
- Nur Benutzer mit der Gruppe `admin` oder `user` dürfen sich anmelden.
- Bei ungültigen Zugangsdaten oder nicht erlaubter Gruppe wird der Fehlercode `INVALID_CREDENTIALS` verwendet.
- Bei erfolgreichem Login werden `user_id` und `group` in die Session geschrieben.
- Die erfolgreiche Antwort enthält weiterhin ein JWT und die Benutzergruppe.

## Key Principles

- Open Source
- Mobile First / Accessibility First
- Privacy-friendly
- Low operating costs
- API-first between Frontend and Backend
- Clear separation of layers
- Lean Documentation - less, but correct and redundancy-free
- Reactive Frontend - NgRx with RxJS for async operations
- Functional Style - map/reduce/filter for data transformations

## Page Layout and E2E Conventions

### Page Layout

Alle Seiten SOLLEN dieselbe grundlegende Card-Struktur verwenden:

- Seitenüberschrift, Logo und Seiteninhalt liegen innerhalb derselben Card.
- Zwischen Header und Seiteninhalt steht ein `mat-divider`.
- Die horizontale Ausrichtung des Headers entspricht der Ausrichtung des Card-Inhalts.
- Die vertikalen Innenabstände von Header und Seiteninhalt sind ausgewogen und konsistent.
- Admin-Seiten unter `/admin` zeigen zusätzlich das Administrationsmenü im Header.
- Seiten ausserhalb von `/admin` zeigen keinen Menübutton.
- Die Desktop-Basisschrift beträgt `1.4em`; die mobile Darstellung bleibt davon unberührt.

### E2E-Teststruktur

Playwright-E2E-Specs testen jeweils nur eine Route:

- `overview.spec.ts` testet `/admin/overview`.
- `login.spec.ts` testet `/login`.
- `start.spec.ts` testet `/start`.
- Tests für `/` liegen in einer separaten `root.spec.ts`.

Eine Spec DARF grundsätzlich keine Tests für mehrere unterschiedliche Routen enthalten. Authentifizierungs- und Redirect-Lifecycle-Tests dürfen die dafür notwendigen Zielrouten durchlaufen. Der Login-Lifecycle in `login.spec.ts` darf deshalb `/login` und den geschützten Übergang zu `/admin/overview` testen.

## Entwicklungsphilosophie

Bei Architektur- und Implementierungsentscheidungen gelten folgende Grundsätze:

- **Einfachheit vor Cleverness:** Bevorzuge die einfachste Lösung, die das aktuelle Problem vollständig löst.
- **Lean:** Führe keine Abstraktionen, Schichten, Dependencies oder Framework-Mechanismen ein, solange sie keinen konkreten Nutzen haben.
- **Keine hypothetische Zukunft implementieren:** Code soll gut erweiterbar sein, aber Anforderungen, die noch nicht existieren, werden nicht vorweggenommen.
- **Kleine, klar abgegrenzte Einheiten:** Funktionen, Components, Effects und Services sollen eine klar erkennbare Verantwortung haben.
- **Expliziter Datenfluss:** Bevorzuge nachvollziehbare, explizite Abläufe gegenüber versteckten Seiteneffekten und implizitem Verhalten.
- **Funktional und deklarativ:** Bevorzuge Pure Functions, immutable Daten und `map`/`filter`/`reduce` gegenüber imperativer Mutation, sofern dies den Code einfacher macht.
- **Composition over Complexity:** Komplexeres Verhalten soll möglichst durch die Kombination kleiner, einfacher Bausteine entstehen.
- **Resilienz:** Änderungen und Fehler in einem Teil des Systems sollen möglichst wenig Auswirkungen auf andere Teile haben. Klare Grenzen und geringe Kopplung sind wichtiger als maximale Wiederverwendung.
- **Testbarkeit ist Teil des Designs:** Fachlogik soll möglichst unabhängig von UI, Datenbank, Netzwerk und Framework-Infrastruktur testbar sein.
- **Keine Abstraktion um der Abstraktion willen:** Wiederverwendung erst abstrahieren, wenn tatsächlich gemeinsame Funktionalität vorhanden ist.
- **Bestehendes respektieren:** Bei Änderungen zuerst die vorhandene Architektur und deren Absicht verstehen. Keine ungefragten Refactorings oder Modernisierungen.
- **Lesbarkeit vor Kürze:** Wenige Zeilen Code sind kein Ziel. Der Ablauf und die Absicht des Codes sollen leicht verständlich sein.
- **Kommentare erklären das Warum:** Kommentare sollen Entscheidungen und nicht offensichtliche Gründe dokumentieren, nicht den Code paraphrasieren.

Als Leitlinie gilt:

> **Design for change, but do not implement the future in advance.**
## Coding Rules

### 5. Immer Reactive Forms verwenden

Alle Formulare in Angular SOLLEN mit Reactive Forms (`FormGroup`, `FormControl`) erstellt werden, nicht mit Template-driven Forms.

Siehe: [Angular Reactive Forms](https://angular.io/guide/reactive-forms)

### 6. Immer globale Funktionen/Styles verwenden

Wiederverwendbare Funktionen, Services und Styles SOLLEN in globale Dateien ausgelagert werden (z.B. `styles.scss`, shared services), nicht in Komponenten dupliziert werden.

### 7. Immer Material-Template-Vorlagen prüfen

Bevor eigene Styles geschrieben werden, SOLL geprüft werden, ob Material-Components bereits Default-Styles mitbringen. Eigenes CSS ist nur zu schreiben, wenn Material keinen Default bietet.

Beispiele:
- ✅ `mat-form-field` hat keinen 100%-Width-Default → `width: 100%` ist nötig
- ❌ `mat-form-field appearance="outline"` bringt fertige Border-Styles mit → kein eigenes Border-CSS nötig
- ❌ Focus/Error-States von Material → keine eigenen Colors nötig

### 8. Eigene Verzeichnisse für Components

Jede Component SOLL in ein eigenes Verzeichnis mit gleichnamigen Files:

```
components/
├── component-name/
│   ├── component-name.ts
│   ├── component-name.html
│   └── component-name.scss
```

### 9. SCSS statt CSS

Alle Component-Styles SOLLEN als SCSS (`.scss`) statt CSS (`.css`) geschrieben werden.

### 10. Wiederverwendbare Componenten für gemeinsame UI-Patterns

Gemeinsame UI-Patterns SOLLEN als wiederverwendbare Componenten erstellt werden.

Diese Componenten SOLLEN im Verzeichnis `shared/components/` erstellt werden:

```
shared/components/
├── info-box/
│   ├── info-box.ts
│   ├── info-box.html
│   └── info-box.scss
├── loading-spinner/
│   └── ...
└── empty-state/
    └── ...
```

Beispiele:
- **InfoBoxComponent** mit Varianten: `info`, `warning`, `error`, `success`
- Loading-Spinner
- Empty-State
- Page-Header

### 11. Signals für reaktive Daten verwenden

Inputs SOLLEN als `input()` Signal definiert werden.
Berechnete Werte SOLLEN als `computed()` definiert werden.
Constructor Injection SOLL durch `inject()` ersetzt werden.

```typescript
// FALSCH
@Input() variant: string = 'info';
constructor(private router: Router) {}
get defaultIcon(): string { ... }

// RICHTIG
variant = input<'info' | 'warning' | 'error' | 'success'>('info');
private router = inject(Router);
defaultIcon = computed(() => { ... });
```

### 12. Standard HTML/CSS-Struktur

Für alle Pages mit Formularen:

```html
<div class="page-container">
  <div class="page-header">
    <img src="/android-chrome-512x512.png" alt="Logo" class="header-icon" />
    <h1 class="mat-headline-5">Titel</h1>
  </div>

  <mat-card appearance="outlined" class="standard-card">
    <h2>Section Title</h2>
    <mat-card-content>
      <form class="auth-form">
        <mat-form-field appearance="outline">
          <mat-label>Label</mat-label>
          <input matInput />
        </mat-form-field>
        <button mat-flat-button color="primary">Submit</button>
      </form>
    </mat-card-content>
  </mat-card>
</div>
```

**Regeln:**
- Globale CSS-Klassen verwenden: `.page-container`, `.page-header`, `.header-icon`, `.standard-card`, `.auth-form`
- Material Typography: `mat-headline-5`, `mat-body-1`, etc.
- `mat-card appearance="outlined"` mit Klasse `standard-card`
- `mat-form-field appearance="outline"`
- `mat-flat-button color="primary"`
- SCSS bleibt leer wenn keine komponentenspezifischen Styles nötig
