# CI-Checks fuer `main`

Diese Checks sollen vor einem Merge in `main` erfolgreich abgeschlossen sein.

| Check-Name | Zweck | Ausfuehrung |
| --- | --- | --- |
| `frontend-tests` | Frontend-Jest-Tests | `cd frontend && npm test -- --runInBand` |
| `frontend-build` | Frontend-Production-Build | `cd frontend && npm run build` |
| `backend-tests` | Backend-PHPUnit-Tests | `cd backend && vendor/bin/phpunit` |
| `e2e-tests` | Playwright-End-to-End-Tests | `cd frontend && npm run test:e2e:cli` |

## Merge-Regel

Ein Pull Request nach `main` darf erst gemergt werden, wenn alle vier Checks erfolgreich sind.

Die Checks werden als Required Status Checks in der Branch-Protection-Regel fuer `main` verwendet.

## Bewusste Abgrenzung

- Integrationstests sind zunächst kein Required Check.
- Linting ist zunächst kein Required Check.
- Force-Pushes bleiben auf nicht geschuetzten Feature-Branches erlaubt.
- Force-Pushes auf `main` werden deaktiviert.
