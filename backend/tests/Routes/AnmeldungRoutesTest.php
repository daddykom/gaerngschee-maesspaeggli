<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Data\UserRepository;
use App\Routes\AnmeldungRoutes;
use App\Services\AnmeldungService;
use App\Services\EmailSenderInterface;
use App\Services\FairgateContactProvider;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;

final class AnmeldungRoutesTest extends TestCase
{
    public function testAnmeldungRequestReturnsValidationErrorForInvalidEmail(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AnmeldungRoutes::register($app, $this->service());

        $response = $app->handle($this->request('not-an-email'));

        self::assertSame(422, $response->getStatusCode());
    }

    public function testAnmeldungRequestReturnsNeutralAcceptedResponse(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AnmeldungRoutes::register($app, $this->service());

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
                public function sendProforma(string $recipient): void
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
            ->createServerRequest('POST', '/public/anmeldung')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new Stream($stream));
    }
}
