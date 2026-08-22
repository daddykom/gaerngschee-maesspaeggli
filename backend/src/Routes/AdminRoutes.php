<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use App\Services\EmailSender;
use App\Services\EmailSenderInterface;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Throwable;

final class AdminRoutes
{
    public static function register(
        App $app,
        ?UserRepository $userRepository = null,
        ?EmailSenderInterface $emailSender = null,
    ): void {
        $app->group('/admin', function (RouteCollectorProxy $group) use ($userRepository, $emailSender): void {
            $listRoute = $group->get('/users', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
            ) use ($userRepository): ResponseInterface {
                $users = ($userRepository ?? new UserRepository())->findAll();

                return self::json($response, $users);
            });
            $listRoute->add(new GroupMiddleware(['admin'], $userRepository));
            $listRoute->add(new AuthMiddleware());

            $detailRoute = $group->get('/users/{userId}', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                array $args,
            ) use ($userRepository): ResponseInterface {
                $userId = (string) ($args['userId'] ?? '');
                if (!self::canAccessUser($request, $userId)) {
                    return self::error($response, 'NOT_FOUND', 404);
                }

                $user = ($userRepository ?? new UserRepository())->findById($userId);

                return $user === null
                    ? self::error($response, 'NOT_FOUND', 404)
                    : self::json($response, ['user' => $user]);
            });
            $detailRoute->add(new GroupMiddleware(['admin', 'user'], $userRepository));
            $detailRoute->add(new AuthMiddleware());

            $createRoute = $group->post('/users', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
            ) use ($userRepository, $emailSender): ResponseInterface {
                $data = self::readJsonBody($request);
                $email = self::readEmail($data);
                $group = self::readGroup($data);

                if ($email === null || $group === null) {
                    return self::error($response, 'INVALID_USER_DATA', 422);
                }

                $repository = $userRepository ?? new UserRepository();
                if ($repository->findByEmail($email) !== null) {
                    return self::error($response, 'EMAIL_ALREADY_REGISTERED', 409);
                }

                $temporaryPassword = self::createTemporaryPassword();
                $user = null;
                try {
                    $user = $repository->createUser($email, $temporaryPassword, $group, true);
                    ($emailSender ?? new EmailSender())->sendUserCreated($user['email'], $temporaryPassword);
                } catch (PDOException $exception) {
                    if (self::isDuplicateEmail($exception)) {
                        return self::error($response, 'EMAIL_ALREADY_REGISTERED', 409);
                    }

                    return self::error($response, 'USER_CREATION_FAILED', 500);
                } catch (Throwable) {
                    if (is_array($user) && isset($user['id'])) {
                        $repository->deleteUser($user['id']);
                    }

                    return self::error($response, 'EMAIL_DELIVERY_FAILED', 503);
                }

                return self::json($response, [
                    'user' => $user,
                    'emailSentTo' => $user['email'],
                ], 201);
            });
            $createRoute->add(new GroupMiddleware(['admin'], $userRepository));
            $createRoute->add(new AuthMiddleware());

            $updateRoute = $group->patch('/users/{userId}', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                array $args,
            ) use ($userRepository, $emailSender): ResponseInterface {
                $userId = (string) ($args['userId'] ?? '');
                if (!self::canAccessUser($request, $userId)) {
                    return self::error($response, 'NOT_FOUND', 404);
                }

                $repository = $userRepository ?? new UserRepository();
                $currentUser = $repository->findById($userId);
                if ($currentUser === null) {
                    return self::error($response, 'NOT_FOUND', 404);
                }

                $data = self::readJsonBody($request);
                if (!self::isAdmin($request) && (array_key_exists('group', $data) || array_key_exists('requiredPasswordReset', $data))) {
                    return self::error($response, 'INVALID_USER_DATA', 422);
                }

                $email = array_key_exists('email', $data) ? self::readEmail($data) : $currentUser['email'];
                $group = array_key_exists('group', $data) ? self::readGroup($data) : $currentUser['group'];
                $requiredPasswordReset = array_key_exists('requiredPasswordReset', $data)
                    ? self::readBool($data, 'requiredPasswordReset')
                    : (bool) $currentUser['required_password_reset'];

                if (
                    $email === null
                    || $group === null
                    || $requiredPasswordReset === null
                    || (!self::isAdmin($request) && $group !== $currentUser['group'])
                ) {
                    return self::error($response, 'INVALID_USER_DATA', 422);
                }

                if ($email !== $currentUser['email'] && $repository->findByEmail($email) !== null) {
                    return self::error($response, 'EMAIL_ALREADY_REGISTERED', 409);
                }

                try {
                    $user = $repository->updateUser($userId, $email, $group, $requiredPasswordReset);
                    if ($user === null) {
                        return self::error($response, 'NOT_FOUND', 404);
                    }

                    if ($user['email'] !== $currentUser['email']) {
                        ($emailSender ?? new EmailSender())->sendUserEmailChanged($user['email']);
                    }
                } catch (PDOException $exception) {
                    if (self::isDuplicateEmail($exception)) {
                        return self::error($response, 'EMAIL_ALREADY_REGISTERED', 409);
                    }

                    return self::error($response, 'USER_UPDATE_FAILED', 500);
                } catch (Throwable) {
                    return self::error($response, 'EMAIL_DELIVERY_FAILED', 503);
                }

                $result = ['user' => $user];
                if ($user['email'] !== $currentUser['email']) {
                    $result['emailSentTo'] = $user['email'];
                }

                return self::json($response, $result);
            });
            $updateRoute->add(new GroupMiddleware(['admin', 'user'], $userRepository));
            $updateRoute->add(new AuthMiddleware());

            $deleteRoute = $group->delete('/users/{userId}', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                array $args,
            ) use ($userRepository): ResponseInterface {
                $userId = (string) ($args['userId'] ?? '');
                if (!self::isAdmin($request)) {
                    return self::error($response, 'NOT_FOUND', 404);
                }

                $deleted = ($userRepository ?? new UserRepository())->deleteUser($userId);

                return $deleted
                    ? self::json($response, ['deleted' => true, 'userId' => $userId])
                    : self::error($response, 'NOT_FOUND', 404);
            });
            $deleteRoute->add(new GroupMiddleware(['admin'], $userRepository));
            $deleteRoute->add(new AuthMiddleware());
        });
    }

    private static function readJsonBody(ServerRequestInterface $request): array
    {
        $data = json_decode((string) $request->getBody(), true);

        return is_array($data) ? $data : [];
    }

    private static function readEmail(array $data): ?string
    {
        $email = $data['email'] ?? null;
        if (!is_string($email)) {
            return null;
        }

        $email = strtolower(trim($email));

        return filter_var($email, FILTER_VALIDATE_EMAIL) === false ? null : $email;
    }

    private static function readGroup(array $data): ?string
    {
        $group = $data['group'] ?? null;

        return is_string($group) && in_array($group, ['admin', 'user', 'client'], true)
            ? $group
            : null;
    }

    private static function readBool(array $data, string $key): ?bool
    {
        return array_key_exists($key, $data) && is_bool($data[$key]) ? $data[$key] : null;
    }

    private static function canAccessUser(ServerRequestInterface $request, string $userId): bool
    {
        $actor = $request->getAttribute('user');

        return self::isAdmin($request) || (is_array($actor) && ($actor['id'] ?? null) === $userId);
    }

    private static function isAdmin(ServerRequestInterface $request): bool
    {
        $actor = $request->getAttribute('user');

        return is_array($actor) && ($actor['group'] ?? null) === 'admin';
    }

    private static function createTemporaryPassword(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
    }

    private static function isDuplicateEmail(PDOException $exception): bool
    {
        return $exception->getCode() === '23000'
            || str_contains(strtolower($exception->getMessage()), 'unique');
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
