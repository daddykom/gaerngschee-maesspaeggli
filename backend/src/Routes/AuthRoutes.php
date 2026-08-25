<?php

declare(strict_types=1);

namespace App\Routes;

use App\Auth\Actions\AuthenticatedPasswordChangeAction;
use App\Auth\Actions\ClientLoginAction;
use App\Auth\Actions\CurrentUserAction;
use App\Auth\Actions\LoginAction;
use App\Auth\Actions\LogoutAction;
use App\Auth\Actions\PasswordChangeAction;
use App\Auth\Actions\RegisterAction;
use App\Users\Data\UserRepository;
use App\Middleware\AuthMiddleware;
use App\Auth\Services\AccessKeyService;
use App\Auth\Services\JwtService;
use App\Auth\Services\SessionService;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class AuthRoutes
{
    public static function register(
        App $app,
        ?UserRepository $userRepository = null,
        ?JwtService $jwtService = null,
        ?SessionService $sessionService = null,
        ?AccessKeyService $accessKeyService = null,
    ): void {
        $app->group('/auth', function (RouteCollectorProxy $group) use (
            $userRepository,
            $jwtService,
            $sessionService,
            $accessKeyService,
        ): void {
            $group->post('/password-change', new PasswordChangeAction($accessKeyService));

            $authenticatedPasswordChange = $group->post(
                '/password-change-authenticated',
                new AuthenticatedPasswordChangeAction($userRepository),
            );
            $authenticatedPasswordChange->add(new AuthMiddleware());

            $group->post('/client-login', new ClientLoginAction($accessKeyService, $jwtService, $sessionService));
            $group->post('/register', new RegisterAction($userRepository, $jwtService, $sessionService));
            $group->post('/login', new LoginAction($userRepository, $jwtService, $sessionService));
            $group->post('/logout', new LogoutAction());
            $group->get('/me', new CurrentUserAction());
        });
    }
}
