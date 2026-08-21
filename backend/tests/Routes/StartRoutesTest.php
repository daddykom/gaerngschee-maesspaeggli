<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Data\UserRepository;
use App\Routes\StartRoutes;
use App\Services\AnmeldungService;
use App\Services\EmailSenderInterface;
use App\Services\FairgateContactProvider;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;

final class StartRoutesTest extends TestCase
{
    public function testStartRequestReturnsValidationErrorForInvalidEmail(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        StartRoutes::register($app, $this->service());

        $response = $app->handle($this->request('not-an-email'));

        self::assertSame(422, $response->getStatusCode());
    }

    public function testStartRequestReturnsNeutralAcceptedResponse(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        StartRoutes::register($app, $this->service());

        $response = $app->handle($this->request('person@example.com'));

        self::assertSame(202, $response->getStatusCode());
        self::assertSame(
            '{"sent":true}',
            (string) $response->getBody(),
        );
    }

    private function service(): AnmeldungService
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->exec(
            'CREATE TABLE users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                "group" TEXT NOT NULL,
                created_at TEXT,
                updated_at TEXT
            )',
        );

        return new AnmeldungService(
            new UserRepository($pdo),
            new class () implements FairgateContactProvider {
                public function hasContactByEmail(string $email): bool
                {
                    return false;
                }
            },
            new class () implements EmailSenderInterface {
                public function sendAnmeldung(string $recipient, \App\Services\AnmeldungMailVariant $variant, string $locale = 'de'): void
                {
                }
            },
        );
    }

    private function request(string $email): \Psr\Http\Message\ServerRequestInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, json_encode(['email' => $email], JSON_THROW_ON_ERROR));
        rewind($stream);

        return (new ServerRequestFactory())
            ->createServerRequest('POST', '/public/start')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new Stream($stream));
    }
}
