# Architecture

## Overview

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend                             │
│  ┌─────────────┐  ┌─────────────┐                           │
│  │  Angular    │  │    NgRx     │                           │
│  │  Components │◄─┤    Store    │                           │
│  └─────────────┘  └──────┬──────┘                           │
└──────────────────────────┼──────────────────────────────────┘
                           │ REST API (JSON)
┌──────────────────────────┼──────────────────────────────────┐
│                        Backend                              │
│  ┌─────────────┐  ┌──────┴──────┐  ┌─────────────────────┐ │
│  │   Slim 4    │  │   Routes    │  │   MariaDB           │ │
│  │   PHP 8     │  │  (PSR-15)   │  │                     │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
                           │
              ┌────────────┴────────────┐
              │                         │
         ┌────▼────┐            ┌──────▼──────┐
         │ Payrexx │            │  Fairgate   │
         │(Payment)│            │(Eligibility)│
         └─────────┘            └─────────────┘
```

## Frontend Architecture

### View/Container Pattern

Components follow the View/Container pattern for clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                   ContainerComponent                        │
│  - Injects NgRx Store and services                         │
│  - Selects state via selectors                             │
│  - Dispatches actions                                      │
│  - Handles events from View                                │
└─────────────────────────────────────────────────────────────┘
                             │ @Input() / @Output()
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                     ViewComponent                          │
│  - Pure presentation (no side effects)                     │
│  - Receives data via @Input()                              │
│  - Emits events via @Output()                              │
│  - No service injections                                   │
└─────────────────────────────────────────────────────────────┘
```

### Naming Convention

| Type | Suffix | Example |
|------|--------|---------|
| View (pure) | `ViewComponent` | `RegistrationListViewComponent` |
| Container | `ContainerComponent` | `RegistrationListContainerComponent` |

### Functional Effects

Effects use the functional pattern with `createEffect` and `{ functional: true }`:

```typescript
export const loadRegistrationsEffect = createEffect(
    (actions$ = inject(Actions), store = inject(Store), registrationService = inject(RegistrationService)) => {
        return actions$.pipe(
            ofType(RegistrationsActions.loadRegistrations),
            switchMap(() =>
                registrationService.getRegistrations().pipe(
                    map((registrations) => RegistrationsActions.loadRegistrationsSuccess({ registrations })),
                    catchError((error) => of(RegistrationsActions.loadRegistrationsFailure({ error: error.message })))
                )
            )
        );
    },
    { functional: true }
);

export const registrationsEffects = [loadRegistrationsEffect];
```

Effects are registered in `app.config.ts`:
```typescript
provideEffects(registrationsEffects)
```

## Backend Architecture

### Slim Framework Structure

```
backend/
├── public/
│   └── index.php          # Entry point
├── src/
│   ├── Application.php    # App configuration
│   └── Routes/           # API route definitions
│       ├── RegistrationRoutes.php
│       ├── DonationRoutes.php
│       └── ...
└── vendor/                # Dependencies
```

### External Integrations

| Service | Purpose |
|---------|---------|
| Payrexx | Payment processing for donations |
| Fairgate | Eligibility verification for registrations |

## Technology Choices

### Why Angular?

- Component-based architecture fits View/Container pattern
- NgRx provides predictable state management
- Strong typing with TypeScript
- Good tooling and ecosystem

### Why Slim?

- Lightweight, suitable for shared hosting (Cyon)
- PSR-7/15 compliance
- FastRoute included for routing
- No ORM coupling

### Why MariaDB?

- Recommended in project requirements
- Cyon supports MariaDB
- Relational model fits registrations/children relationship

## Reactive Architecture

### Frontend

The frontend uses reactive patterns throughout:

- **NgRx Effects**: Handle side effects reactively using RxJS Observables
- **Store**: Centralized state with immutable updates
- **Components**: Use signals for synchronous state, observables for async

```
User Action → Action Dispatch → Effect (RxJS) → Service Call →
→ Success/Failure Action → Reducer (Immutable) → State Update → Component Re-render
```

### Backend

- Request/Response is synchronous (Slim handles this)
- Business logic uses functional style: array_map, array_filter, array_reduce
- Side effects (database, email) are isolated in dedicated functions
- Return immutable data structures (arrays, value objects)

### Data Flow Example

```
Frontend:  Action → Effect (switchMap) → HTTP → Success Action → Reducer → State
Backend:   Request → Route Handler → Pure Function → Response
```
