<?php

declare(strict_types=1);

namespace App\Routes;

use App\Auth\Actions\AuthenticatedPasswordChangeAction;
use App\Auth\Actions\LoginAction;
use App\Auth\Actions\LogoutAction;
use App\Auth\Actions\PasswordResetAction;
use App\Auth\Actions\PasswordResetRequestAction;
use App\Auth\Actions\RefreshSessionAction;
use App\Auth\Actions\SessionStatusAction;
use App\Registration\Actions\ClientRegistrationLoginAction;
use App\Registration\Services\ClientRegistrationLoginService;
use App\Users\Data\UserRepository;
use App\Middleware\AuthMiddleware;
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
        ?ClientRegistrationLoginService $registrationLoginService = null,
    ): void {
        $app->group('/auth', function (RouteCollectorProxy $group) use (
            $userRepository,
            $jwtService,
            $sessionService,
            $registrationLoginService,
        ): void {
            $authenticatedPasswordChange = $group->post(
                '/password-change-authenticated',
                new AuthenticatedPasswordChangeAction($userRepository),
            );
            $authenticatedPasswordChange->add(new AuthMiddleware());

            $group->post('/password-reset-request', new PasswordResetRequestAction());
            $group->post('/password-reset', new PasswordResetAction());

            $group->post('/registration-login', new ClientRegistrationLoginAction(
                $registrationLoginService,
                $jwtService,
                $sessionService,
            ));
            $group->post('/login', new LoginAction($userRepository, $jwtService, $sessionService));
            $group->post('/logout', new LogoutAction());
            $group->get('/session-status', new SessionStatusAction($sessionService));
            $group->post('/session-refresh', new RefreshSessionAction($sessionService, $jwtService));
        });
    }
}
