<?php

declare(strict_types=1);

namespace App\Routes;

use App\Configuration\Actions\ListPublicConfigurationAction;
use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Actions\StartRegistrationAction;
use App\Registration\Services\AnmeldungService;
use App\Registration\Services\RegistrationTokenService;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class PublicRoutes
{
    public static function register(
        App $app,
        ?AnmeldungService $anmeldungService = null,
        ?RegistrationTokenService $registrationTokens = null,
        ?FrontendConfigRepository $configRepository = null,
    ): void
    {
        $app->group('/public', function (RouteCollectorProxy $group) use ($anmeldungService, $registrationTokens, $configRepository): void {
            $group->post('/start', new StartRegistrationAction($anmeldungService, $registrationTokens));
            $group->get('/configuration', new ListPublicConfigurationAction($configRepository));
        });
    }
}
