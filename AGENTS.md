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
