<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\UserRepository;
use App\Services\AccessKeyService;
use App\Services\JwtService;
use App\Services\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Throwable;

final class AuthRoutes
{
    public static function register(
        App $app,
        ?UserRepository $userRepository = null,
        ?JwtService $jwtService = null,
        ?SessionService $sessionService = null,
        ?AccessKeyService $accessKeyService = null,
    ): void {
        $app->group('/auth', function (RouteCollectorProxy $group) use ($userRepository, $jwtService, $sessionService, $accessKeyService): void {
            $group->post('/password-change', function (ServerRequestInterface $request, ResponseInterface $response) use ($accessKeyService) {
                $data = self::readJsonBody($request);
                $accessKey = self::readString($data, 'accessKey');
                $password = self::readString($data, 'password');

                if ($accessKey === null || $password === null) {
                    return self::error($response, 'INVALID_PASSWORD', 422);
                }

                $user = ($accessKeyService ?? new AccessKeyService())->resetPassword($accessKey, $password);
                if ($user === null) {
                    return self::error($response, 'INVALID_ACCESS_KEY', 401);
                }

                return self::json($response, ['user' => $user]);
            });

            $group->post('/client-login', function (ServerRequestInterface $request, ResponseInterface $response) use ($jwtService, $sessionService, $accessKeyService) {
                $data = self::readJsonBody($request);
                $accessKey = self::readString($data, 'accessKey');

                if ($accessKey === null) {
                    return self::error($response, 'INVALID_ACCESS_KEY', 401);
                }

                $user = ($accessKeyService ?? new AccessKeyService())->loginClient($accessKey);
                if ($user === null) {
                    return self::error($response, 'INVALID_ACCESS_KEY', 401);
                }

                $token = ($jwtService ?? new JwtService())->createToken($user['id']);
                ($sessionService ?? new SessionService())->setUser($user['id'], $user['group']);

                return self::json($response, [
                    'user' => $user,
                    'token' => $token,
                    'group' => $user['group'],
                ]);
            });

            $group->post('/register', function (ServerRequestInterface $request, ResponseInterface $response) use ($userRepository, $jwtService, $sessionService) {
                $data = self::readJsonBody($request);
                $email = self::readString($data, 'email');
                $password = self::readString($data, 'password');

                if (!self::isEmailAndPasswordValid($email, $password)) {
                    return self::error($response, 'INVALID_CREDENTIALS', 422);
                }

                $repository = $userRepository ?? new UserRepository();
                if ($repository->findByEmail($email) !== null) {
                    return self::error($response, 'EMAIL_ALREADY_REGISTERED', 409);
                }

                try {
                    $user = $repository->createUser($email, $password, 'client');
                    $token = ($jwtService ?? new JwtService())->createToken($user['id']);
                    ($sessionService ?? new SessionService())->setUserId($user['id']);
                } catch (Throwable) {
                    return self::error($response, 'REGISTRATION_FAILED', 500);
                }

                return self::json($response, ['user' => $user, 'token' => $token], 201);
            });

            $group->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) use ($userRepository, $jwtService, $sessionService) {
                $data = self::readJsonBody($request);
                $email = self::readString($data, 'email');
                $password = self::readString($data, 'password');

                if ($email === null || $password === null) {
                    return self::error($response, 'INVALID_CREDENTIALS', 401);
                }

                $user = ($userRepository ?? new UserRepository())->verifyPassword($email, $password);
                if ($user === null || !in_array($user['group'] ?? null, ['admin', 'user'], true)) {
                    return self::error($response, 'INVALID_CREDENTIALS', 401);
                }

                $token = ($jwtService ?? new JwtService())->createToken($user['id']);
                ($sessionService ?? new SessionService())->setUser($user['id'], $user['group']);

                return self::json($response, [
                    'user' => $user,
                    'token' => $token,
                    'group' => $user['group'],
                ]);
            });

            $group->post('/logout', function (ServerRequestInterface $request, ResponseInterface $response) {
                (new SessionService())->clear();

                return $response->withStatus(204);
            });

            $group->get('/me', function (ServerRequestInterface $request, ResponseInterface $response) {
                $sessionService = new SessionService();
                $userId = $sessionService->getUserId();

                if ($userId === null) {
                    $token = (new JwtService())->getBearerToken($request);
                    $userId = $token === null ? null : (new JwtService())->getUserIdFromToken($token);
                }

                if ($userId === null) {
                    return self::error($response, 'UNAUTHORIZED', 401);
                }

                $user = (new UserRepository())->findById($userId);
                if ($user === null) {
                    return self::error($response, 'UNAUTHORIZED', 401);
                }

                return self::json($response, ['user' => $user]);
            });
        });
    }

    private static function readJsonBody(ServerRequestInterface $request): array
    {
        $body = json_decode((string) $request->getBody(), true);

        return is_array($body) ? $body : [];
    }

    private static function readString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    private static function isEmailAndPasswordValid(?string $email, ?string $password): bool
    {
        return $email !== null
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false
            && $password !== null;
    }

    private static function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }

    private static function error(ResponseInterface $response, string $code, int $status): ResponseInterface
    {
        return self::json($response, ['error' => ['code' => $code, 'details' => []]], $status);
    }
}
