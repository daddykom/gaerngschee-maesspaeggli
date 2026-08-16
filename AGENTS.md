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
- NICHT: Annahmen treffen und handeln
- NICHT: Mehrere Optionen gleichzeitig implementieren

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
