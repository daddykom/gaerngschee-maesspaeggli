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
│   ├── Routes/            # API routes
│   │   └── OfferRoutes.php
│   └── Data/              # JSON data files
│       └── offers.json
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

use App\Routes\OfferRoutes;
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
            $response->getBody()->write(json_encode(['message' => 'Gaerngschee API']));
            return $response->withHeader('Content-Type', 'application/json');
        });

        OfferRoutes::register($app);

        return $app;
    }
}
```

## Route Definition

```php
<?php
declare(strict_types=1);

namespace App\Routes;

use Psr\Http\Message\ResponseInterface;
use Slim\App;
use Slim\Psr7\Response;

final class OfferRoutes
{
    private const DATA_FILE = __DIR__ . '/../Data/offers.json';

    public static function register(App $app): void
    {
        $app->get('/api/offers', function ($request, ResponseInterface $response) {
            $json = file_get_contents(self::DATA_FILE);
            $offers = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

            $body = json_encode($offers, JSON_THROW_ON_ERROR);
            $response->getBody()->write($body);
            return $response->withHeader('Content-Type', 'application/json');
        });
    }
}
```

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

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/offers` | List all offers |
| GET | `/api/offers/{id}` | Get single offer |
| POST | `/api/offers` | Create new offer |
| PUT | `/api/offers/{id}` | Update offer |
| DELETE | `/api/offers/{id}` | Delete offer |

## Data Format

All API responses use JSON:

```json
{
  "id": "1",
  "title": "Free Meals",
  "description": "Every Thursday...",
  "category": "essen",
  "location": {
    "address": "Münsterplatz, Basel",
    "longitude": 7.591641,
    "latitude": 47.556431
  },
  "status": "published",
  "createdAt": "2024-01-15T10:00:00Z",
  "updatedAt": "2024-01-15T10:00:00Z",
  "contact": {
    "name": "Food Bank Basel",
    "email": "info@foodbank.ch",
    "phone": "+41 61 123 45 67"
  },
  "imageUrl": null
}
```

## Database

See [database-conventions.md](./database-conventions.md) for MariaDB/Phinx setup and migration guidelines.