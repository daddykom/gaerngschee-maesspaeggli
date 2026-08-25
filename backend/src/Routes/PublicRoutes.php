<?php

declare(strict_types=1);

namespace App\Routes;

use App\PublicApi\Actions\ApiInfoAction;
use Slim\App;

final class PublicRoutes
{
    public static function register(App $app): void
    {
        $app->get('/public', new ApiInfoAction());
    }
}
