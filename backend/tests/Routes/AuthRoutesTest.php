<?php

declare(strict_types=1);

namespace Tests\Routes;

use App\Auth\Data\AccessKeyRepository;
use App\Users\Data\UserRepository;
use App\Application;
use App\Routes\AuthRoutes;
use App\Auth\Services\AccessKeyService;
use App\Auth\Services\JwtService;
use App\Auth\Services\SessionService;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;
use Slim\Factory\AppFactory;
use Tests\Support\TestDatabase;

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

        $this->pdo = TestDatabase::create(withAccessKeys: true);
        $this->repository = new UserRepository($this->pdo);
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
        self::assertFalse($data['requiredPasswordReset']);
    }

    public function testFlaggedUserCanLoginButMustChangePassword(): void
    {
        $user = $this->repository->createUser('user@example.com', 'temporary-secret', 'user', true);

        $response = $this->createAuthApp()->handle($this->request('/auth/login', [
            'email' => $user['email'],
            'password' => 'temporary-secret',
        ]));
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($data['requiredPasswordReset']);
        self::assertSame($user['id'], (new SessionService())->getUserId());
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

    public function testRegistrationCreatesClientAndSession(): void
    {
        $response = $this->createAuthApp()->handle($this->request('/auth/register', [
            'email' => 'new-client@example.com',
            'password' => 'secret',
        ]));
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('client', $data['user']['group']);
        self::assertSame($data['user']['id'], (new SessionService())->getUserId());
        self::assertNotNull($this->repository->findByEmail('new-client@example.com'));
    }

    public function testLogoutClearsSession(): void
    {
        $user = $this->repository->createUser('user@example.com', 'secret', 'user');
        (new SessionService())->setUser($user['id'], 'user');

        $response = $this->createAuthApp()->handle(
            (new ServerRequestFactory())->createServerRequest('POST', '/auth/logout'),
        );

        self::assertSame(204, $response->getStatusCode());
        self::assertNull((new SessionService())->getUserId());
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
        self::assertFalse((bool) $this->repository->findById($user['id'])['required_password_reset']);
    }

    public function testAuthenticatedPasswordChangeClearsResetRequirement(): void
    {
        $user = $this->repository->createUser('user@example.com', 'temporary-secret', 'user', true);
        (new SessionService())->setUser($user['id'], 'user');

        $response = $this->createAuthApp()->handle($this->request('/auth/password-change-authenticated', [
            'password' => 'new-secret',
        ]));

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($this->repository->verifyPassword('user@example.com', 'new-secret'));
        self::assertFalse((bool) $this->repository->findById($user['id'])['required_password_reset']);
    }

    public function testAuthenticatedPasswordChangeRequiresAuthentication(): void
    {
        $response = $this->createAuthApp()->handle($this->request('/auth/password-change-authenticated', [
            'password' => 'new-secret',
        ]));

        self::assertSame(404, $response->getStatusCode());
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
