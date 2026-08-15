# Design Document

This document captures key architectural and design decisions.

---

## Architecture Decisions

### Frontend State Management

**Decision:** NgRx with functional effects pattern

**Rationale:**
- AGENTS.md specifies NgRx with functional effects
- Provides predictable state management
- Enables time-travel debugging
- Clear separation of concerns

**Implementation:**
- Feature-based stores (offers, categories, ui)
- `createEffect` with `{ functional: true }` option
- Effects exported as arrays for easy registration
- Pure functions for business logic

**Status:** Partially implemented (offers store done)

---

### Backend Framework

**Decision:** Slim 4 with PSR-15

**Rationale:**
- Lightweight, suitable for shared hosting (Cyon)
- PSR-7/15 compliance ensures interoperability
- FastRoute for routing (included in Slim)
- No ORM强迫 (flexibility for schema design)

**Status:** Implemented (basic structure)

---

### Map Solution

**Decision:** MapLibre GL + OpenFreeMap

**Rationale:**
- Open-source stack (no licensing costs)
- OpenFreeMap provides free vector tiles
- MapLibre is well-maintained
- MapTiler for geocoding (address search)

**Status:** Planned, not implemented

---

### Data Storage

**Decision:** MariaDB

**Rationale:**
- Recommended in README
- Cyon supports MariaDB
- Relational model fits offer/category relationship
- JSON for initial development, migrate later

**Status:** Planned (currently using JSON file)

---

## Security Decisions

### Authentication

- **Approach:** Session-based with secure cookies
- **Password:** bcrypt hashing
- **CSRF:** Token-based protection
- **Rate limiting:** On auth endpoints

**Status:** Not implemented

### CORS

- Backend allows `Access-Control-Allow-Origin: *` for development
- Restrict to specific origin in production

---

## Code Style Decisions

From AGENTS.md:

| Rule | Implementation |
|------|----------------|
| Functional style | Prefer pure functions over classes |
| Immutable data | Use readonly, spread operators |
| Explicit types | Strict TypeScript, no `any` |
| Side effects | Clearly bounded in Effects/Services |
| Business logic | Extracted to testable pure functions |

---

## Testing Strategy

| Type | Tool | Status |
|------|------|--------|
| Unit (Angular) | Jest | Planned |
| Unit (PHP) | PHPUnit | Planned |
| E2E | Playwright | Planned |
| Accessibility | axe-core | Planned |

---

## Open Questions

1. **API Design:** Should there be pagination? Cursor-based or offset?
2. **Image Storage:** S3? Local filesystem? Base64 in DB?
3. **Caching:** Redis for session? API response caching?
4. **Multi-tenancy:** Multiple communities on one instance?

---

## Change Log

| Date | Decision | Notes |
|------|----------|-------|
| 2026-06-13 | Initial design documented | Based on README and AGENTS.md |