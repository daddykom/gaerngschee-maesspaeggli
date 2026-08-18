# Backend Conventions

## PHP Framework

Using **Slim Framework 4** with PSR-15 middleware patterns.

## Project Structure

```
backend/
├── public/
│   └── index.php          # Entry point
├── src/
│   ├── Application.php    # App factory and middleware
│   └── Routes/           # API routes
│       ├── RegistrationRoutes.php
│       ├── DonationRoutes.php
│       ├── WaitlistRoutes.php
│       └── PickupRoutes.php
├── vendor/                # Composer dependencies
└── composer.json
```

## Entry Point (public/index.php)

```php
<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Application;
use Slim\Factory\ServerRequestCreatorFactory;

$app = Application::create();
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

$response = $app->handle($request);

$response->getBody()->rewind();
header(sprintf(
    'HTTP/%s %s %s',
    $response->getProtocolVersion(),
    $response->getStatusCode(),
    $response->getReasonPhrase()
));

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
```

## Application Setup (src/Application.php)

```php
<?php
declare(strict_types=1);

namespace App;

use App\Routes\RegistrationRoutes;
use App\Routes\DonationRoutes;
use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Factory\AppFactory;

final class Application
{
    public static function create(): App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();

        $app->add(function ($request, $handler): ResponseInterface {
            $response = $handler->handle($request);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Access-Control-Allow-Origin', '*');
        });

        $app->get('/', function ($request, ResponseInterface $response) {
            $response->getBody()->write(json_encode(['message' => 'Gärngschee Mässpäggli API']));
            return $response->withHeader('Content-Type', 'application/json');
        });

        RegistrationRoutes::register($app);
        DonationRoutes::register($app);
        WaitlistRoutes::register($app);
        PickupRoutes::register($app);

        return $app;
    }
}
```

## Testumgebung und Fairgate-Fake

Die lokale Docker-Umgebung verwendet:

```env
APP_ENV=test
```

In der Testumgebung wird kein echter Fairgate-Service aufgerufen. Stattdessen
entscheidet `FakeFairgateClient` anhand der E-Mail-Adresse:

| E-Mail-Adresse | Fairgate-Ergebnis |
|---|---|
| `person+fair@example.com` | `true` |
| `person+FAIR-test@example.com` | `true` |
| `person+test@example.com` | `false` |
| `person@example.com` | `false` |

Die Buchstabenfolge `fair` muss im lokalen Teil der E-Mail-Adresse nach dem
`+` vorkommen. Die Prüfung ist unabhängig von Gross-/Kleinschreibung.

Für die Produktionsumgebung muss gesetzt werden:

```env
APP_ENV=prod
```

Dann wird der echte `FairgateClient` verwendet. Ein fehlender oder unbekannter
Wert von `APP_ENV` führt zu einem Konfigurationsfehler.

## API Endpoints

### Registrations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/registrations` | List all registrations |
| GET | `/api/registrations/{id}` | Get single registration |
| POST | `/api/registrations` | Create new registration |
| PUT | `/api/registrations/{id}` | Update registration |
| DELETE | `/api/registrations/{id}` | Delete registration |

### Donations

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/donations` | List all donations |
| POST | `/api/donations` | Create donation (from Payrexx webhook) |

### Waitlist

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/waitlist` | List waitlisted registrations |
| POST | `/api/waitlist/{registrationId}/qualify` | Qualify from waitlist |

### Pickup

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/pickup/verify` | Verify QR code |
| POST | `/api/pickup/confirm` | Confirm pickup |

## Coding Standards

### Strict Types

Always declare strict types at the top of PHP files:

```php
<?php
declare(strict_types=1);
```

### Namespaces

- Use PSR-4 autoloading
- Namespace matches directory structure
- Final classes by default (prevent extension)

### Error Handling

- Use exceptions for error conditions
- JSON encode errors with appropriate HTTP status codes
- Don't expose internal details in production

## Functional Style

### Principles

- Prefer pure functions over procedures
- Avoid side effects where possible
- Use immutable data structures
- Compose small functions into larger logic

### Examples

```php
// GOOD - Pure function
function calculateAvailableMaspaeggli(int $total, int $reserved): int
{
    return max(0, $total - $reserved);
}

// GOOD - Using array_map for transformation
$donationAmounts = array_map(
    fn(Donation $donation) => $donation->getAmount(),
    $donations
);

// GOOD - Using array_filter
$completedDonations = array_filter(
    $donations,
    fn(Donation $donation) => $donation->isCompleted()
);

// GOOD - Using array_reduce for aggregation
$totalAmount = array_reduce(
    $donations,
    fn(int $carry, Donation $donation) => $carry + $donation->getAmount(),
    0
);

// GOOD - Using array_column for key extraction
$registrationIds = array_column($registrations, 'id');

// GOOD - Method chaining with arrow functions
$result = array_values(array_filter($array, fn($item) => $item['active']));

// BAD - Procedural style with side effects
foreach ($donations as $donation) {
    if ($donation->status === 'completed') {
        $total += $donation->amount;
    }
}
```

### Collections (PHP 8+)

Use iterators and generator functions for large datasets:

```php
// Generator for memory efficiency
function getRegistrationsGenerator(PDO $pdo): \Generator
{
    $stmt = $pdo->query('SELECT * FROM registrations');
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        yield Registration::fromArray($row);
    }
}

// Using array_map with arrow functions
$emails = array_map(
    fn(Registration $r) => $r->getEmail(),
    array_filter($registrations, fn(Registration $r) => $r->isQualified())
);
```

## Database

See [database-conventions.md](./database-conventions.md) for MariaDB/Phinx setup and migration guidelines.
