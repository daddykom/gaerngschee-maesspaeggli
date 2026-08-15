# AGENTS.md

Project information for AI assistants. This file is an **index** - see linked documents for details.

## Project Overview

**Gratisangebote-Karte** - Open-source web application helping people with limited financial means find free offers and discover activities and social contacts in their region.

→ See: [documents/project.md](./documents/project.md)

## Documentation Structure

```
gaerngschee/
├── AGENTS.md                    # This file (index)
├── documents/                   # Human-readable documentation
│   ├── project.md              # Project overview
│   ├── directory-structure.md  # Directory layout
│   ├── architecture.md         # Architecture decisions
│   ├── frontend-conventions.md # Angular patterns (View/Container)
│   └── backend-conventions.md  # PHP patterns
└── openspec/
    └── specs/                   # Capability specifications
```

## Quick Links

### For Humans
- [documents/project.md](./documents/project.md) - Project goals and features
- [documents/architecture.md](./documents/architecture.md) - System architecture
- [documents/frontend-conventions.md](./documents/frontend-conventions.md) - Angular development
- [documents/backend-conventions.md](./documents/backend-conventions.md) - PHP + Database development

### For AI Assistants
- [openspec/specs/authentication/spec.md](./openspec/specs/authentication/spec.md) - User authentication
- [openspec/specs/platform/spec.md](./openspec/specs/platform/spec.md) - PWA, i18n, a11y

## View/Container Pattern

Angular components follow the View/Container pattern:

| Pattern | Description |
|---------|-------------|
| **ViewComponent** | Pure presentation, `@Input()`/`@Output()` only, no services |
| **ContainerComponent** | Manages state, injects Store/services, dispatches actions |

See: [documents/frontend-conventions.md](./documents/frontend-conventions.md#viewcontainer-pattern)

## OpenSpec Workflow

Use these commands to work with changes:

| Command | Purpose |
|---------|---------|
| `/opsx-explore [topic]` | Explore ideas, investigate problems |
| `/opsx-propose <name>` | Create new change proposal |
| `/opsx-apply [name]` | Implement change tasks |
| `/opsx-archive [name]` | Archive completed change |
| `/opsx-sync-specs [name]` | Sync delta specs to main specs |

See: [openspec/specs/agents-md-workflow/spec.md](./openspec/specs/agents-md-workflow/spec.md)

## Active Changes

Run `openspec list --json` to see active changes.

## Key Principles

- Open Source
- Mobile First / Accessibility First
- Privacy-friendly
- Low operating costs
- API-first between Frontend and Backend
- Clear separation of layers