<?php

declare(strict_types=1);

namespace App\Routes;

use App\Configuration\Actions\ListPublicConfigurationAction;
use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Actions\StartRegistrationAction;
use App\Registration\Data\OrderRepository;
use App\Registration\Services\AnmeldungService;
use App\Registration\Services\RegistrationTokenService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class PublicRoutes
{
    public static function register(
        App $app,
        ?AnmeldungService $anmeldungService = null,
        ?RegistrationTokenService $registrationTokens = null,
        ?FrontendConfigRepository $configRepository = null,
        ?OrderRepository $orderRepository = null,
    ): void
    {
        $app->get('/public/php-test', static function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
            $response->getBody()->write('PHP backend is reachable.');

            return $response->withHeader('Content-Type', 'text/plain; charset=utf-8');
        });

        $app->group('/public', function (RouteCollectorProxy $group) use ($anmeldungService, $registrationTokens, $configRepository, $orderRepository): void {
            $group->post('/start', new StartRegistrationAction($anmeldungService, $registrationTokens, $configRepository, null, $orderRepository));
            $group->get('/configuration', new ListPublicConfigurationAction($configRepository));
        });
    }
}
