# Frontend Conventions

## View/Container Pattern

### Principle

Separate pure presentation (View) from stateful logic (Container).

### ViewComponent

- **Pure** - no side effects
- Receives data via **signal inputs** (`input()`)
- Emits events via **signal outputs** (`output()`)
- No service injections
- No direct store access
- Uses Angular 21 control flow (`@if`, `@for`, `@switch`, `@defer`)

```typescript
@Component({
  selector: 'app-offer-list-view',
  standalone: true,
  templateUrl: './offer-list-view.component.html',
})
export class OfferListViewComponent {
  offers = input<Offer[]>([]);
  loading = input<boolean>(false);
  cardClick = output<Offer>();
}
```

**Template (`offer-list-view.component.html`):**
```html
@if (loading()) {
  <mat-spinner></mat-spinner>
} @else {
  @for (offer of offers(); track offer.id) {
    <app-offer-card [offer]="offer" (cardClick)="cardClick.emit($event)" />
  }
}
```

### ContainerComponent

- **Impure** - manages state
- Injects NgRx Store and services
- Selects state via **selectSignal()** (signals-based)
- Dispatches actions
- Passes data to View via **signal inputs** (`input()`)

```typescript
@Component({
  selector: 'app-offer-list-container',
  standalone: true,
  template: `<app-offer-list-view [offers]="offers()" (cardClick)="onCardClick($event)" />`
})
export class OfferListContainerComponent {
  private store = inject(Store);

  offers = this.store.selectSignal(selectOffers);
  loading = this.store.selectSignal(selectOffersLoading);

  onCardClick(offer: Offer) {
    this.store.dispatch(OfferActions.selectOffer({ offer }));
  }
}
```

### Signal Inputs (Required)

ViewComponents **must** use Angular's signal-based inputs instead of `@Input()`:

```typescript
// GOOD - Signal inputs
offers = input<Offer[]>([]);
loading = input<boolean>(false);
cardClick = output<Offer>();

// BAD - @Input() decorator
@Input() offers: Offer[] = [];
@Input() loading = false;
@Output() cardClick = new EventEmitter<Offer>();
```

**Rule:** Signals are preferred in components. For store access, use `selectSignal()` instead of `store.select()`. Effects must use RxJS Observables (this is intentional and correct).

## File Patterns

| Pattern | Description |
|---------|-------------|
| `*.component.ts` | Angular components |
| `*.component.html` | Component templates (always use separate file, never inline) |
| `*.component.scss` | Component styles |
| `*.service.ts` | Angular services |
| `*.actions.ts` | NgRx actions |
| `*.feature.ts` | NgRx feature (reducer + selectors) |
| `*.effects.ts` | NgRx effects (functional pattern) |
| `*.model.ts` | TypeScript interfaces |
| `*.util.ts` | Pure utility functions |
| `*.pipe.ts` | Angular pipes |

## NgRx Store Organization

```
store/
├── app.state.ts           # Root state interface
├── offers/
│   ├── offers.actions.ts  # ofType actions
│   ├── offers.feature.ts  # createFeature + selectors
│   ├── offers.effects.ts  # Side effects
│   └── offers.state.ts    # State interface + initialState
└── categories/
    └── ...
```

## State Interface Example

```typescript
interface OffersState {
  offers: Offer[];
  selectedOffer: Offer | null;
  loading: boolean;
  error: string | null;
  currentPosition: OfferLocation;
}

export const initialState: OffersState = {
  offers: [],
  selectedOffer: null,
  loading: false,
  error: null,
  currentPosition: {
    latitude: 47.556431,
    longitude: 7.591641,
    address: 'Münsterplatz, Basel',
  },
};
```

## Functional Effects Pattern

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

Registration in `app.config.ts`:
```typescript
provideEffects(offersEffects)
```

## Coding Style

### Prefer Pure Functions

```typescript
// BAD
@Component()
class OfferListComponent {
    filteredOffers = this.offers.filter(o => o.status === 'published');
}

// GOOD
export const filterPublishedOffers = (offers: Offer[]): Offer[] =>
    offers.filter(o => o.status === 'published');
```

### Use Strong Typing

- No `any` types
- Explicit return types
- Use interfaces for all data structures

### Immutable Data

- Use spread operators for updates
- Avoid mutation
- Use `readonly` where applicable

## Testing

- Jest for unit tests
- Components: test rendering with `@Input()` values
- Services: mock dependencies
- NgRx: test actions, reducers, selectors independently
- Pure functions: simple input/output tests