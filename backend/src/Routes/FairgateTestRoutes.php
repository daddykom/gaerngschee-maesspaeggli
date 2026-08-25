<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use App\Services\FairgateClient;
use App\Services\FairgateConfiguration;
use App\Services\FairgateException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

final class FairgateTestRoutes
{
    private const TEST_EMAIL = 'isabelle.joss@gaerngschee.ch';

    public static function register(App $app, ?UserRepository $userRepository = null): void
    {
        $route = $app->get('/admin/fairgate/test', function (
            ServerRequestInterface $request,
            ResponseInterface $response,
        ): ResponseInterface {
            try {
                $configuration = FairgateConfiguration::load();
                $client = new FairgateClient(
                    baseUrl: $configuration['base_url'],
                    organizationId: $configuration['organization_id'],
                    accessKey: $configuration['access_key'],
                    publicKey: $configuration['public_key'],
                );

                return self::json($response, [
                    'email' => self::TEST_EMAIL,
                    'fairgate' => $client->findContactDataByEmail(self::TEST_EMAIL),
                ]);
            } catch (FairgateException) {
                return self::error($response, 'FAIRGATE_TEST_FAILED', 502);
            }
        });
        $route->add(new GroupMiddleware(['admin'], $userRepository));
        $route->add(new AuthMiddleware());
    }

    private static function json(ResponseInterface $response, array $data, int $status = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private static function error(ResponseInterface $response, string $code, int $status): ResponseInterface
    {
        return self::json($response, ['error' => ['code' => $code, 'details' => []]], $status);
    }
}
