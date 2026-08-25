<?php

declare(strict_types=1);

namespace App\Routes;

use App\PublicApi\Actions\ApiInfoAction;
use App\Registration\Actions\StartRegistrationAction;
use App\Registration\Services\AnmeldungService;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class PublicRoutes
{
    public static function register(App $app, ?AnmeldungService $anmeldungService = null): void
    {
        $app->get('/public', new ApiInfoAction());

        $app->group('/public', function (RouteCollectorProxy $group) use ($anmeldungService): void {
            $group->post('/start', new StartRegistrationAction($anmeldungService));
        });
    }
}
