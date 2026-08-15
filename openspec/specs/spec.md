# Project Specification Index

## Project: Gratisangebote-Karte

Open-Source-Webapplikation zur Unterstützung von Menschen mit wenig finanziellen Mitteln bei der Suche nach kostenlosen Angeboten, sowie zur Hilfe für einsame Menschen beim Entdecken von Aktivitäten und sozialen Kontakten in ihrer Region.

---

## Capability Specs

| Spec | Description | Status |
|------|-------------|--------|
| [offers](./offers/spec.md) | Core offer management, search, filtering | Partial |
| [categories](./categories/spec.md) | Category filtering | Not started |
| [map](./map/spec.md) | MapLibre map display | Not started |
| [moderation](./moderation/spec.md) | Editorial workflow | Not started |
| [authentication](./authentication/spec.md) | User auth and roles | Not started |
| [platform](./platform/spec.md) | PWA, i18n, a11y, infrastructure | Not started |
| [deployment](./deployment/spec.md) | CI/CD deployment to test server | In progress |
| [ui-header-menu](./ui-header-menu/spec.md) | Header with burger menu navigation | Complete |

---

## Implementation Overview

```
┌─────────────────────────────────────────────────────────────┐
│  Offers     ████████████░░░░░░░░░░░░░░░  ~35%              │
│  Categories ░░░░░░░░░░░░░░░░░░░░░░░░░░░░  0%               │
│  Map        ░░░░░░░░░░░░░░░░░░░░░░░░░░░░  0%               │
│  Moderation ░░░░░░░░░░░░░░░░░░░░░░░░░░░░  0%               │
│  Auth       ░░░░░░░░░░░░░░░░░░░░░░░░░░░░  0%               │
│  Platform   ░░░░░░░░░░░░░░░░░░░░░░░░░░░░  0%               │
└─────────────────────────────────────────────────────────────┘
```

---

## Architecture Summary

```
┌─────────────────────────────────────────────────────────────┐
│                        Frontend                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────────────┐ │
│  │  Angular    │  │    NgRx     │  │   MapLibre +        │ │
│  │  Components │◄─┤    Store    │  │   OpenFreeMap       │ │
│  └─────────────┘  └──────┬──────┘  └─────────────────────┘ │
└──────────────────────────┼──────────────────────────────────┘
                           │ REST API
┌──────────────────────────┼──────────────────────────────────┐
│                        Backend                              │
│  ┌─────────────┐  ┌──────┴──────┐  ┌─────────────────────┐ │
│  │   Slim 4    │  │  Routes     │  │   MariaDB           │ │
│  │   PHP 8     │  │  (PSR-15)   │  │   (planned)         │ │
│  └─────────────┘  └─────────────┘  └─────────────────────┘ │
└─────────────────────────────────────────────────────────────┘
```

---

## Data Model

### Offer

```typescript
interface Offer {
  id: string;
  title: string;
  description: string;
  category: 'essen' | 'freizeit' | 'kultur' | 'sport' | 'beratung' | 'treffpunkte' | 'bildung';
  location: { address: string; longitude: number; latitude: number };
  status: 'draft' | 'published' | 'archived';
  createdAt: string;
  updatedAt: string;
  contact: { name: string; email?: string; phone?: string };
  imageUrl: string | null;
}
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/offers` | List offers (currently returns all, should filter by status) |
| GET | `/api/offers/{id}` | Get single offer |
| POST | `/api/offers` | Create offer |
| PUT | `/api/offers/{id}` | Update offer |
| DELETE | `/api/offers/{id}` | Delete offer |
| GET | `/api/categories` | List categories (planned) |

---

## Technology Stack

| Layer | Technology |
|-------|------------|
| Frontend | Angular 19, NgRx, TypeScript |
| Backend | PHP 8, Slim 4 |
| Database | MariaDB (planned, currently JSON) |
| Maps | MapLibre GL, OpenFreeMap |
| Hosting | Cyon.ch |
| CI/CD | GitHub Actions (planned) |

---

## Open Decisions

See individual specs for details. Key unresolved questions:

1. **Auth**: Session vs JWT? Library choice?
2. **Database**: Schema design not started
3. **i18n**: Which additional languages?
4. **Moderation**: Rejection flow details
5. **Notifications**: Push, email, or both?

---

## Last Updated

2026-06-13