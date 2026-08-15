# Platform Capability Spec

## Overview

Cross-cutting platform concerns: PWA, i18n, accessibility, responsive design.

## Features

### F1: Progressive Web App (PWA)

- Installable on desktop and mobile devices
- Offline capability (read cached offers)
- Background sync when connection restored
- Push notifications (optional)

### F2: Responsive Design

- Mobile-first approach
- Breakpoints:
  - Mobile: < 768px
  - Tablet: 768px - 1024px
  - Desktop: > 1024px
- Touch-friendly controls

### F3: Internationalization (i18n)

**Languages:**
- German (primary)
- Additional languages TBD

**Translatable content:**
- UI labels and buttons
- Error messages
- Email templates
- Category names (already stored per-language in spec)

### F4: Accessibility (a11y)

- WCAG 2.1 AA compliance target
- Keyboard navigation
- Screen reader support
- Color contrast compliance
- Focus indicators
- Skip links
- ARIA labels

### F5: Privacy & Data Protection

- No tracking cookies
- Minimal data collection
- Clear privacy policy
- GDPR-compliant (EU users)
- Data export capability (for users)

### F6: Database Migrations

All database schema changes SHALL be managed via Phinx migrations.

- All migrations stored in `backend/migrations/`
- Migrations are reversible via `phinx rollback`
- Environment-based database credentials
- No direct SQL modifications in production

## Technical Requirements

| Requirement | Implementation |
|-------------|----------------|
| PWA | Service Worker, manifest.json |
| i18n | @angular/localize or similar |
| a11y | Angular CDK a11y, semantic HTML |
| Privacy | No third-party analytics, minimal cookies |

## Infrastructure

| Component | Technology |
|-----------|------------|
| Hosting | Cyon.ch (Webhosting Double) |
| Database | MariaDB |
| CI/CD | GitHub Actions |
| Domain | TBD |

## Quality Assurance

### Testing Strategy

| Test Type | Scope |
|-----------|-------|
| Unit Tests | Components, Services, NgRx, Pure Functions |
| Integration Tests | API endpoints, data flow |
| E2E Tests | Playwright (planned) |
| Accessibility Tests | axe-core integration |

### CI/CD Pipeline

```
Push → Lint → Build → Test → Deploy to staging → (approval) → Deploy to production
```

## Implementation Status

| Component | Status |
|-----------|--------|
| PWA setup | ✗ Not implemented |
| i18n | ✗ Not implemented |
| Accessibility | ✗ Not implemented |
| Privacy features | ✗ Not implemented |
| GitHub Actions CI | ✗ Not implemented |

## Open Decisions

- Which additional languages?
- Notification strategy (push, email)?
- Backup strategy for MariaDB?
- Monitoring/alerting solution?