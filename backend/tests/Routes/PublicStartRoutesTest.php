<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Users\Data\UserRepository;
use App\Configuration\Data\FrontendConfigRepository;
use App\Routes\PublicRoutes;
use App\Registration\Services\AnmeldungService;
use App\Registration\Services\RegistrationTokenService;
use App\Shared\Mail\EmailSenderInterface;
use App\Fairgate\Services\FairgateContactProvider;
use Tests\Support\TestDatabase;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;

final class PublicStartRoutesTest extends TestCase
{
    public function testPublicConfigurationReturnsClientValuesOnly(): void
    {
        $pdo = TestDatabase::create();
        $insert = $pdo->prepare(
            'INSERT INTO frontend_config
                (id, variable_name, value, description, access_group, update_group, label)
             VALUES (:id, :variable_name, :value, :description, :access_group, :update_group, :label)',
        );
        foreach ([
            ['id' => 'client-url', 'variable_name' => 'fairgate_url', 'value' => 'https://fairgate.example', 'access_group' => ['client']],
            ['id' => 'client-message', 'variable_name' => 'client_message', 'value' => 'Message', 'access_group' => ['admin', 'client']],
            ['id' => 'admin-email', 'variable_name' => 'fairgate_test_email', 'value' => 'admin@example.com', 'access_group' => ['admin']],
        ] as $config) {
            $insert->execute([
                ...$config,
                'value' => json_encode($config['value'], JSON_THROW_ON_ERROR),
                'access_group' => json_encode($config['access_group'], JSON_THROW_ON_ERROR),
                'description' => '',
                'update_group' => '[]',
                'label' => '',
            ]);
        }

        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        PublicRoutes::register($app, null, null, new FrontendConfigRepository($pdo));

        $response = $app->handle((new ServerRequestFactory())->createServerRequest('GET', '/public/configuration'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            '[{"variableName":"client_message","value":"Message"},{"variableName":"fairgate_url","value":"https:\/\/fairgate.example"}]',
            (string) $response->getBody(),
        );
    }

    public function testStartRequestReturnsValidationErrorForInvalidEmail(): void
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        PublicRoutes::register($app, $this->service(), new RegistrationTokenService(TestDatabase::create()));

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
        PublicRoutes::register($app, $this->service(), new RegistrationTokenService(TestDatabase::create()));

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
        PublicRoutes::register($app, $this->service(), new RegistrationTokenService(TestDatabase::create()));

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
        PublicRoutes::register($app, $this->service(true), new RegistrationTokenService(TestDatabase::create()));

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

                public function findContactDataByEmail(string $email): array
                {
                    return ['success' => true, 'data' => null];
                }
            },
            $failToSend
                ? new class () implements EmailSenderInterface {
                    public function sendAnmeldung(string $recipient, \App\Registration\Services\AnmeldungMailVariant $variant, string $locale = 'de', ?string $loginUrl = null): void
                    {
                        throw new \RuntimeException('SMTP failed');
                    }

                    public function sendUserCreated(string $recipient, string $temporaryPassword): void
                    {
                    }

                     public function sendUserEmailChanged(string $recipient): void
                     {
                     }

                     public function sendOrderConfirmation(string $recipient, array $order): void
                     {
                     }
                }
                : new class () implements EmailSenderInterface {
                public function sendAnmeldung(string $recipient, \App\Registration\Services\AnmeldungMailVariant $variant, string $locale = 'de', ?string $loginUrl = null): void
                {
                }

                public function sendUserCreated(string $recipient, string $temporaryPassword): void
                {
                }

                 public function sendUserEmailChanged(string $recipient): void
                 {
                 }

                 public function sendOrderConfirmation(string $recipient, array $order): void
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
