<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Data\AccessKeyRepository;
use App\Data\UserRepository;
use App\Application;
use App\Routes\AuthRoutes;
use App\Services\AccessKeyService;
use App\Services\JwtService;
use App\Services\SessionService;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;
use Slim\Factory\AppFactory;

final class AuthRoutesTest extends TestCase
{
    private PDO $pdo;
    private UserRepository $repository;
    private AccessKeyService $accessKeyService;

    protected function setUp(): void
    {
        putenv('JWT_SECRET=01234567890123456789012345678901');
        putenv('JWT_ALGORITHM=HS256');
        (new SessionService())->clear();

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->exec(
            'CREATE TABLE users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                "group" TEXT NOT NULL,
                created_at TEXT,
                updated_at TEXT
            )',
        );
        $this->repository = new UserRepository($this->pdo);
        $this->pdo->exec(
            'CREATE TABLE user_access_keys (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                purpose TEXT NOT NULL,
                key_hash TEXT NOT NULL UNIQUE,
                expires_at TEXT NOT NULL,
                used_at TEXT NULL,
                created_at TEXT,
                updated_at TEXT
            )',
        );
        $this->accessKeyService = new AccessKeyService(
            $this->pdo,
            $this->repository,
            new AccessKeyRepository($this->pdo),
        );
    }

    protected function tearDown(): void
    {
        (new SessionService())->clear();
        putenv('JWT_SECRET');
        putenv('JWT_ALGORITHM');
    }

    public function testInvalidLoginReturnsUnauthorized(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/auth/login')
            ->withBody((new \Slim\Psr7\Stream(fopen('php://temp', 'r+'))));

        $response = Application::create()->handle($request);

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(
            '{"error":{"code":"INVALID_CREDENTIALS","details":[]}}',
            (string) $response->getBody(),
        );
    }

    public function testAllowedUserCanLoginWithJwtAndSession(): void
    {
        $user = $this->repository->createUser('user@example.com', 'secret', 'user');
        $app = $this->createAuthApp();

        $response = $app->handle($this->request('/auth/login', [
            'email' => 'user@example.com',
            'password' => 'secret',
        ]));
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame($user['id'], $data['user']['id']);
        self::assertSame('user', $data['group']);
        self::assertSame($user['id'], (new JwtService())->getUserIdFromToken($data['token']));
        self::assertSame($user['id'], (new SessionService())->getUserId());
        self::assertSame('user', (new SessionService())->getGroup());
    }

    public function testClientCannotLoginEvenWithCorrectPassword(): void
    {
        $this->repository->createUser('client@example.com', 'secret', 'client');

        $response = $this->createAuthApp()->handle($this->request('/auth/login', [
            'email' => 'client@example.com',
            'password' => 'secret',
        ]));

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(
            '{"error":{"code":"INVALID_CREDENTIALS","details":[]}}',
            (string) $response->getBody(),
        );
        self::assertNull((new SessionService())->getUserId());
    }

    public function testWrongPasswordCannotLogin(): void
    {
        $this->repository->createUser('user@example.com', 'secret', 'user');

        $response = $this->createAuthApp()->handle($this->request('/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong',
        ]));

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(
            '{"error":{"code":"INVALID_CREDENTIALS","details":[]}}',
            (string) $response->getBody(),
        );
    }

    public function testInvalidRegistrationReturnsValidationError(): void
    {
        $request = (new ServerRequestFactory())
            ->createServerRequest('POST', '/auth/register')
            ->withBody((new \Slim\Psr7\Stream(fopen('php://temp', 'r+'))));

        $response = Application::create()->handle($request);

        self::assertSame(422, $response->getStatusCode());
    }

    public function testPasswordChangeWorksWithValidPublicAccessKey(): void
    {
        $user = $this->repository->createUser('client@example.com', 'old-secret', 'client');
        $generated = $this->accessKeyService->generateForUser($user['id'], AccessKeyService::PASSWORD_RESET);
        self::assertNotNull($generated);

        $response = $this->createAuthApp()->handle($this->request('/auth/password-change', [
            'accessKey' => $generated['accessKey'],
            'password' => 'new-secret',
        ]));

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($this->repository->verifyPassword('client@example.com', 'new-secret'));
    }

    public function testClientLoginWorksWithoutAnExistingSession(): void
    {
        $user = $this->repository->createUser('client@example.com', 'secret', 'client');
        $generated = $this->accessKeyService->generateForUser($user['id'], AccessKeyService::CLIENT_LOGIN);
        self::assertNotNull($generated);

        $response = $this->createAuthApp()->handle($this->request('/auth/client-login', [
            'accessKey' => $generated['accessKey'],
        ]));
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('client', $data['group']);
        self::assertSame($user['id'], (new SessionService())->getUserId());
        self::assertSame('client', (new SessionService())->getGroup());
    }

    public function testInvalidAccessKeyIsRejected(): void
    {
        $response = $this->createAuthApp()->handle($this->request('/auth/client-login', [
            'accessKey' => 'invalid-key',
        ]));

        self::assertSame(401, $response->getStatusCode());
        self::assertSame(
            '{"error":{"code":"INVALID_ACCESS_KEY","details":[]}}',
            (string) $response->getBody(),
        );
    }

    private function createAuthApp(): \Slim\App
    {
        $app = AppFactory::create();
        $app->addRoutingMiddleware();
        AuthRoutes::register(
            $app,
            $this->repository,
            new JwtService(),
            new SessionService(),
            $this->accessKeyService,
        );

        return $app;
    }

    private function request(string $path, array $body): \Psr\Http\Message\ServerRequestInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, json_encode($body, JSON_THROW_ON_ERROR));
        rewind($stream);

        return (new ServerRequestFactory())
            ->createServerRequest('POST', $path)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new Stream($stream));
    }
}
