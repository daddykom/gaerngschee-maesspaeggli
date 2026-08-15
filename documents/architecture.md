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
│  ┌─────────────┐  ┌──────┴──────┐  ┌─────────────────────┐  │
│  │   Slim 4    │  │   Routes    │  │   MariaDB           │  │
│  │   PHP 8     │  │  (PSR-15)   │  │   (planned)         │  │
│  └─────────────┘  └─────────────┘  └─────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

## Frontend Architecture

### View/Container Pattern

Components follow the View/Container pattern for clear separation of concerns:

```
┌─────────────────────────────────────────────────────────────┐
│                   ContainerComponent                        │
│  - Injects NgRx Store and services                          │
│  - Selects state via selectors                              │
│  - Dispatches actions                                       │
│  - Handles events from View                                 │
└─────────────────────────────────────────────────────────────┘
                            │ @Input() / @Output()
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                     ViewComponent                           │
│  - Pure presentation (no side effects)                      │
│  - Receives data via @Input()                               │
│  - Emits events via @Output()                               │
│  - No service injections                                    │
└─────────────────────────────────────────────────────────────┘
```

### Naming Convention

| Type | Suffix | Example |
|------|--------|---------|
| View (pure) | `ViewComponent` | `OfferListViewComponent` |
| Container | `ContainerComponent` | `OfferListContainerComponent` |


### Functional Effects

Effects use the functional pattern with `createEffect` and `{ functional: true }`:

```typescript
export const loadOffersEffect = createEffect(
    (actions$ = inject(Actions), store = inject(Store), offersService = inject(OffersService)) => {
        return actions$.pipe(
            ofType(OffersActions.loadOffers),
            withLatestFrom(store.select(selectCurrentPosition)),
            switchMap(([, currentPosition]) =>
                offersService.getOffers().pipe(
                    map((offers) => OffersActions.loadOffersSuccess({ offers })),
                    catchError((error) => of(OffersActions.loadOffersFailure({ error: error.message })))
                )
            )
        );
    },
    { functional: true }
);

export const offersEffects = [loadOffersEffect];
```

Effects are registered in `app.config.ts`:
```typescript
provideEffects(offersEffects)
```

## Backend Architecture

### Slim Framework Structure

```
backend/
├── public/
│   └── index.php          # Entry point
├── src/
│   ├── Application.php    # App configuration
│   ├── Routes/            # API route definitions
│   │   └── OfferRoutes.php
│   └── Data/              # JSON data files
│       └── offers.json
└── vendor/                # Dependencies
```

### API Design

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

### Why MapLibre + OpenFreeMap?

- Open-source (no licensing costs)
- OpenFreeMap provides free vector tiles
- MapTiler for geocoding

### Why MariaDB?

- Recommended in project requirements
- Cyon supports MariaDB
- Relational model fits offer/category relationship