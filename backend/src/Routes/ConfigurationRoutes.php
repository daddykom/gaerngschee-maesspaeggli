<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\FrontendConfigRepository;
use App\Data\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Throwable;

final class ConfigurationRoutes
{
    public static function register(
        App $app,
        ?FrontendConfigRepository $configRepository = null,
        ?UserRepository $userRepository = null,
    ): void {
        $app->group('/admin/configuration', function (RouteCollectorProxy $group) use ($configRepository, $userRepository): void {
            $listRoute = $group->get('', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
            ) use ($configRepository): ResponseInterface {
                $user = $request->getAttribute('user');
                $repository = $configRepository ?? new FrontendConfigRepository(\App\Data\Database::getConnection());

                return self::json($response, $repository->findVisibleForGroup($user['group']));
            });
            $listRoute->add(new GroupMiddleware(['admin', 'user'], $userRepository));
            $listRoute->add(new AuthMiddleware());

            $updateRoute = $group->patch('/{configId}', function (
                ServerRequestInterface $request,
                ResponseInterface $response,
                array $args,
            ) use ($configRepository): ResponseInterface {
                $data = json_decode((string) $request->getBody(), true);
                $value = is_array($data) && array_key_exists('value', $data) ? $data['value'] : null;
                if (!is_string($value) && !self::isStringArray($value)) {
                    return self::error($response, 'INVALID_CONFIGURATION_DATA', 422);
                }

                $user = $request->getAttribute('user');
                $repository = $configRepository ?? new FrontendConfigRepository(\App\Data\Database::getConnection());

                try {
                    $config = $repository->update((string) ($args['configId'] ?? ''), $user['group'], $value);
                } catch (Throwable) {
                    return self::error($response, 'CONFIGURATION_UPDATE_FAILED', 500);
                }

                return $config === null
                    ? self::error($response, 'NOT_FOUND', 404)
                    : ($config === false
                        ? self::error($response, 'FORBIDDEN', 403)
                        : self::json($response, $config));
            });
            $updateRoute->add(new GroupMiddleware(['admin', 'user'], $userRepository));
            $updateRoute->add(new AuthMiddleware());
        });
    }

    private static function isStringArray(mixed $value): bool
    {
        return is_array($value)
            && array_is_list($value)
            && count(array_filter($value, 'is_string')) === count($value);
    }

    private static function json(ResponseInterface $response, mixed $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private static function error(ResponseInterface $response, string $code, int $status): ResponseInterface
    {
        return self::json($response, ['error' => ['code' => $code, 'details' => []]], $status);
    }
}
