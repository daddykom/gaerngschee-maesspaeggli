<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Data\UserRepository;
use App\Application;
use App\Routes\AdminRoutes;
use App\Services\AnmeldungMailVariant;
use App\Services\EmailSenderInterface;
use App\Services\SessionService;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;

final class AdminRoutesTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $repository;

    protected function setUp(): void
    {
        (new SessionService())->clear();
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec(
            'CREATE TABLE users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                "group" TEXT NOT NULL,
                required_password_reset INTEGER NOT NULL DEFAULT 0,
                created_at TEXT,
                updated_at TEXT
            )',
        );
        $this->repository = new UserRepository($this->pdo);
    }

    protected function tearDown(): void
    {
        (new SessionService())->clear();
    }

    public function testAdminUsersRequiresAuthentication(): void
    {
        $response = Application::create()->handle(
            (new ServerRequestFactory())->createServerRequest('GET', '/admin/users'),
        );

        self::assertSame(404, $response->getStatusCode());
    }

    public function testAdminCanListUsersWithSession(): void
    {
        $admin = $this->repository->createUser('admin@example.com', 'secret', 'admin');
        $this->repository->createUser('user@example.com', 'secret', 'user');
        (new SessionService())->setUser($admin['id'], 'admin');
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AdminRoutes::register($app, $this->repository);

        $response = $app->handle((new ServerRequestFactory())->createServerRequest('GET', '/admin/users'));

        self::assertSame(200, $response->getStatusCode());
        self::assertCount(2, json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR));
    }

    public function testUserCannotListAdminUsers(): void
    {
        $user = $this->repository->createUser('user@example.com', 'secret', 'user');
        (new SessionService())->setUser($user['id'], 'user');
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AdminRoutes::register($app, $this->repository);

        $response = $app->handle((new ServerRequestFactory())->createServerRequest('GET', '/admin/users'));

        self::assertSame(404, $response->getStatusCode());
    }

    public function testClientCannotListAdminUsers(): void
    {
        $client = $this->repository->createUser('client@example.com', 'secret', 'client');
        (new SessionService())->setUser($client['id'], 'client');
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AdminRoutes::register($app, $this->repository);

        $response = $app->handle((new ServerRequestFactory())->createServerRequest('GET', '/admin/users'));

        self::assertSame(404, $response->getStatusCode());
    }

    public function testAccessKeyRouteIsNotAvailable(): void
    {
        $admin = $this->repository->createUser('admin@example.com', 'secret', 'admin');
        (new SessionService())->setUser($admin['id'], 'admin');
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AdminRoutes::register($app, $this->repository);

        self::expectException(HttpNotFoundException::class);

        $app->handle(
            (new ServerRequestFactory())->createServerRequest('POST', '/admin/users/user-id/access-key'),
        );
    }

    public function testAdminCanCreateUserAndEmailIsSentToNewAddress(): void
    {
        $admin = $this->repository->createUser('admin@example.com', 'secret', 'admin');
        (new SessionService())->setUser($admin['id'], 'admin');
        $emailSender = new RecordingEmailSender();
        $app = $this->createApp($emailSender);

        $response = $app->handle($this->request('POST', '/admin/users', [
            'email' => ' New@Example.com ',
            'group' => 'user',
        ]));
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('new@example.com', $data['user']['email']);
        self::assertTrue((bool) $data['user']['required_password_reset']);
        self::assertSame('new@example.com', $emailSender->createdRecipient);
        self::assertSame('new@example.com', $data['emailSentTo']);
    }

    public function testUserCanUpdateOwnEmailButNotAnotherUser(): void
    {
        $user = $this->repository->createUser('user@example.com', 'secret', 'user');
        $other = $this->repository->createUser('other@example.com', 'secret', 'user');
        (new SessionService())->setUser($user['id'], 'user');
        $emailSender = new RecordingEmailSender();
        $app = $this->createApp($emailSender);

        $ownResponse = $app->handle($this->request('PATCH', '/admin/users/' . $user['id'], [
            'email' => 'changed@example.com',
        ]));
        $otherResponse = $app->handle($this->request('PATCH', '/admin/users/' . $other['id'], [
            'email' => 'blocked@example.com',
        ]));

        self::assertSame(200, $ownResponse->getStatusCode());
        self::assertSame('changed@example.com', $emailSender->changedRecipient);
        self::assertSame(404, $otherResponse->getStatusCode());
    }

    public function testAdminCanDeleteUser(): void
    {
        $admin = $this->repository->createUser('admin@example.com', 'secret', 'admin');
        $user = $this->repository->createUser('user@example.com', 'secret', 'user');
        (new SessionService())->setUser($admin['id'], 'admin');
        $app = $this->createApp();

        $response = $app->handle($this->request('DELETE', '/admin/users/' . $user['id']));

        self::assertSame(200, $response->getStatusCode());
        self::assertNull($this->repository->findById($user['id']));
    }

    private function createApp(?EmailSenderInterface $emailSender = null): \Slim\App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AdminRoutes::register($app, $this->repository, $emailSender);

        return $app;
    }

    private function request(string $method, string $path, array $body = []): \Psr\Http\Message\ServerRequestInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, json_encode($body, JSON_THROW_ON_ERROR));
        rewind($stream);

        return (new ServerRequestFactory())
            ->createServerRequest($method, $path)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new Stream($stream));
    }
}

final class RecordingEmailSender implements EmailSenderInterface
{
    public ?string $createdRecipient = null;
    public ?string $changedRecipient = null;

    public function sendAnmeldung(string $recipient, AnmeldungMailVariant $variant, string $locale = 'de'): void
    {
    }

    public function sendUserCreated(string $recipient, string $temporaryPassword): void
    {
        $this->createdRecipient = $recipient;
    }

    public function sendUserEmailChanged(string $recipient): void
    {
        $this->changedRecipient = $recipient;
    }
}
