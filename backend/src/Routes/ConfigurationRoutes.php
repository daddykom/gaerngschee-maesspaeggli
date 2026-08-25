<?php

declare(strict_types=1);

namespace App\Routes;

use App\Configuration\Actions\ListConfigurationAction;
use App\Configuration\Actions\UpdateConfigurationAction;
use App\Configuration\Data\FrontendConfigRepository;
use App\Users\Data\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class ConfigurationRoutes
{
    public static function register(
        App $app,
        ?FrontendConfigRepository $configRepository = null,
        ?UserRepository $userRepository = null,
    ): void {
        $app->group('/admin/configuration', function (RouteCollectorProxy $group) use ($configRepository, $userRepository): void {
            $list = $group->get('', new ListConfigurationAction($configRepository));
            $list->add(new GroupMiddleware(['admin', 'user'], $userRepository))->add(new AuthMiddleware());

            $update = $group->patch('/{configId}', new UpdateConfigurationAction($configRepository));
            $update->add(new GroupMiddleware(['admin', 'user'], $userRepository))->add(new AuthMiddleware());
        });
    }
}
