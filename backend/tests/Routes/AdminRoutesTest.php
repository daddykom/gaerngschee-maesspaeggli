<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Data\UserRepository;
use App\Application;
use App\Routes\AdminRoutes;
use App\Services\SessionService;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Psr7\Factory\ServerRequestFactory;

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

    public function testUserCanListAdminUsers(): void
    {
        $user = $this->repository->createUser('user@example.com', 'secret', 'user');
        (new SessionService())->setUser($user['id'], 'user');
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AdminRoutes::register($app, $this->repository);

        $response = $app->handle((new ServerRequestFactory())->createServerRequest('GET', '/admin/users'));

        self::assertSame(200, $response->getStatusCode());
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
}
