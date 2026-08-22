<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use App\Services\AccessKeyService;
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
        ?AccessKeyService $accessKeyService = null,
    ): void {
        $app->group('/admin', function (RouteCollectorProxy $group) use ($userRepository, $accessKeyService): void {
            $route = $group->get('/users', function ($request, ResponseInterface $response) use ($userRepository) {
                $repository = $userRepository ?? new UserRepository();
                $users = $repository->findAll();
                $body = json_encode($users, JSON_THROW_ON_ERROR);
                $response->getBody()->write($body);
                return $response
                    ->withHeader('Content-Type', 'application/json');
            });

            $route->add(new GroupMiddleware(['admin', 'user'], $userRepository));
            $route->add(new AuthMiddleware());

            $accessKeyRoute = $group->post('/users/{userId}/access-key', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                array $args,
            ) use ($accessKeyService): ResponseInterface {
                $data = json_decode((string) $request->getBody(), true);
                $purpose = is_array($data) && is_string($data['purpose'] ?? null)
                    ? $data['purpose']
                    : null;

                if ($purpose === null) {
                    return self::error($response, 'INVALID_ACCESS_KEY_PURPOSE', 422);
                }

                try {
                    $generated = ($accessKeyService ?? new AccessKeyService())->generateForUser(
                        (string) ($args['userId'] ?? ''),
                        $purpose,
                    );
                } catch (Throwable) {
                    return self::error($response, 'INVALID_ACCESS_KEY_PURPOSE', 422);
                }

                if ($generated === null) {
                    return self::error($response, 'USER_NOT_FOUND', 404);
                }

                return self::json($response, $generated, 201);
            });

            $accessKeyRoute->add(new GroupMiddleware(['admin'], $userRepository));
            $accessKeyRoute->add(new AuthMiddleware());
        });
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
