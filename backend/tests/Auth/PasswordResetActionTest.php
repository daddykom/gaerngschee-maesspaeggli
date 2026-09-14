<?php

declare(strict_types=1);

namespace Tests\Auth;

use App\Auth\Actions\PasswordResetAction;
use App\Auth\Services\PasswordResetTokenService;
use App\Users\Data\UserRepository;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Response;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Stream;
use Tests\Support\TestDatabase;

final class PasswordResetActionTest extends TestCase
{
    public function testValidTokenChangesPassword(): void
    {
        $pdo = TestDatabase::create();
        $users = new UserRepository($pdo);
        $user = $users->createUser('user@example.com', 'old-secret', 'user', true);
        $tokens = new PasswordResetTokenService($pdo);
        $issued = $tokens->issue($user['id']);

        $response = (new PasswordResetAction($tokens, $users))(
            $this->request(['token' => $issued['token'], 'password' => 'new-long-secret']),
            new Response(),
        );

        self::assertSame(200, $response->getStatusCode());
        self::assertNotNull($users->verifyPassword('user@example.com', 'new-long-secret'));
        self::assertFalse((bool) $users->findById($user['id'])['required_password_reset']);
        self::assertNull($tokens->consume($issued['token']));
    }

    public function testExpiredOrUnknownTokenIsRejected(): void
    {
        $pdo = TestDatabase::create();
        $users = new UserRepository($pdo);
        $tokens = new PasswordResetTokenService($pdo);

        $response = (new PasswordResetAction($tokens, $users))(
            $this->request(['token' => 'unknown-token', 'password' => 'new-long-secret']),
            new Response(),
        );

        self::assertSame(401, $response->getStatusCode());
        self::assertSame('INVALID_PASSWORD_RESET', json_decode((string) $response->getBody(), true)['error']['code']);
    }

    public function testWeakPasswordIsRejectedWithoutConsumingToken(): void
    {
        $pdo = TestDatabase::create();
        $users = new UserRepository($pdo);
        $user = $users->createUser('user@example.com', 'old-secret', 'user');
        $tokens = new PasswordResetTokenService($pdo);
        $issued = $tokens->issue($user['id']);

        $response = (new PasswordResetAction($tokens, $users))(
            $this->request(['token' => $issued['token'], 'password' => 'short']),
            new Response(),
        );

        self::assertSame(422, $response->getStatusCode());
        self::assertSame('WEAK_PASSWORD', json_decode((string) $response->getBody(), true)['error']['code']);
        self::assertSame($user['id'], $tokens->consume($issued['token']));
    }

    private function request(array $body): \Psr\Http\Message\ServerRequestInterface
    {
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, json_encode($body, JSON_THROW_ON_ERROR));
        rewind($stream);

        return (new ServerRequestFactory())
            ->createServerRequest('POST', '/auth/password-reset')
            ->withBody(new Stream($stream));
    }
}
