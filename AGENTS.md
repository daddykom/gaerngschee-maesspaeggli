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
