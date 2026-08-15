# Directory Structure

## Project Root

```
gaerngschee-maesspaeggli/
├── frontend/                  # Angular SPA
├── backend/                   # PHP Slim API
├── db/                        # Database migrations & seeds
├── documents/                 # Human-readable documentation
├── openspec/                  # OpenSpec capability specs
├── docker-compose.yml         # Docker configuration
└── README.md                  # Project documentation
```

## Frontend

```
frontend/
├── src/
│   ├── app/
│   │   ├── app.component.ts          # Root component
│   │   ├── app.config.ts             # App configuration
│   │   ├── app.routes.ts             # Routing configuration
│   │   ├── store/                   # NgRx store
│   │   │   ├── app.state.ts
│   │   │   ├── registrations/
│   │   │   ├── donations/
│   │   │   └── ...
│   │   ├── features/
│   │   │   ├── registrations/        # Registration feature
│   │   │   ├── donations/           # Donation feature
│   │   │   ├── waitlist/            # Waitlist feature
│   │   │   ├── pickup/              # Pickup feature
│   │   │   └── ...
│   │   └── shared/                  # Shared components, pipes, utils
│   ├── environments/
│   └── assets/
├── project.json               # Nx configuration
├── package.json
└── nx.json
```

## Backend

```
backend/
├── public/
│   └── index.php              # Entry point
├── src/
│   ├── Application.php        # App factory
│   ├── Routes/                # API routes
│   │   ├── RegistrationRoutes.php
│   │   ├── DonationRoutes.php
│   │   ├── WaitlistRoutes.php
│   │   └── PickupRoutes.php
│   └── ...
├── vendor/                    # Composer dependencies
├── composer.json
└── composer.lock
```

## Database

```
db/
├── phinx.php                  # Phinx configuration
├── migrations/               # Database migrations
│   ├── 20260614080000_create_registrations_table.php
│   ├── 20260614081000_create_children_table.php
│   └── ...
└── seeds/
    ├── development/           # Development seed data
    ├── test/                 # Test seed data
    └── production/           # Production seed data
```

## Documentation

```
documents/
├── project.md                # Project overview
├── architecture.md           # System architecture
├── frontend-conventions.md   # Angular conventions
├── backend-conventions.md    # PHP conventions
└── database-conventions.md   # Database conventions
```

## OpenSpec

```
openspec/
├── SPEC.md                   # OpenSpec index
└── specs/                    # Capability specifications
    ├── maesspaeggli.md       # Fachkonzept
    ├── maesspaeggli/         # Main capability spec
    ├── donations/
    ├── registrations/
    ├── eligibility/
    ├── waitlist/
    ├── qualification/
    ├── pickup/
    ├── notifications/
    ├── email-templates/
    ├── authentication/
    ├── platform/
    └── agents-md-workflow/
```
