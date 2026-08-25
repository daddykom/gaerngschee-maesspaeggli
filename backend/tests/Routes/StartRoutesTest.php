<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Users\Data\UserRepository;
use App\Routes\StartRoutes;
use App\Registration\Services\AnmeldungService;
use App\Shared\Mail\EmailSenderInterface;
use App\Fairgate\Services\FairgateContactProvider;
use Tests\Support\TestDatabase;
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
        self::assertSame(
            '{"error":{"code":"INVALID_EMAIL","details":[]}}',
            (string) $response->getBody(),
        );
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

    public function testStartRequestRejectsUnsupportedLanguage(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        StartRoutes::register($app, $this->service());

        $response = $app->handle($this->request('person@example.com', 'fr'));

        self::assertSame(422, $response->getStatusCode());
        self::assertSame(
            '{"error":{"code":"UNSUPPORTED_LANGUAGE","details":[]}}',
            (string) $response->getBody(),
        );
    }

    public function testStartRequestReturnsServiceError(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        StartRoutes::register($app, $this->service(true));

        $response = $app->handle($this->request('person@example.com'));

        self::assertSame(503, $response->getStatusCode());
        self::assertSame(
            '{"error":{"code":"REQUEST_FAILED","details":[]}}',
            (string) $response->getBody(),
        );
    }

    private function service(bool $failToSend = false): AnmeldungService
    {
        $pdo = TestDatabase::create();

        return new AnmeldungService(
            new UserRepository($pdo),
            new class () implements FairgateContactProvider {
                public function hasContactByEmail(string $email): bool
                {
                    return false;
                }
            },
            $failToSend
                ? new class () implements EmailSenderInterface {
                    public function sendAnmeldung(string $recipient, \App\Registration\Services\AnmeldungMailVariant $variant, string $locale = 'de'): void
                    {
                        throw new \RuntimeException('SMTP failed');
                    }

                    public function sendUserCreated(string $recipient, string $temporaryPassword): void
                    {
                    }

                    public function sendUserEmailChanged(string $recipient): void
                    {
                    }
                }
                : new class () implements EmailSenderInterface {
                public function sendAnmeldung(string $recipient, \App\Registration\Services\AnmeldungMailVariant $variant, string $locale = 'de'): void
                {
                }

                public function sendUserCreated(string $recipient, string $temporaryPassword): void
                {
                }

                public function sendUserEmailChanged(string $recipient): void
                {
                }
            },
        );
    }

    private function request(string $email, string $language = 'de'): \Psr\Http\Message\ServerRequestInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, json_encode(['email' => $email, 'language' => $language], JSON_THROW_ON_ERROR));
        rewind($stream);

        return (new ServerRequestFactory())
            ->createServerRequest('POST', '/public/start')
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new Stream($stream));
    }
}
