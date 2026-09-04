<?php

declare(strict_types=1);

namespace App\Routes;

use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use App\Registration\Actions\GetClientOrderAction;
use App\Registration\Actions\SaveClientOrderAction;
use App\Registration\Data\OrderRepository;
use App\Users\Data\UserRepository;
use App\Shared\Mail\EmailSenderInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class ClientRoutes
{
    public static function register(
        App $app,
        ?OrderRepository $orderRepository = null,
        ?UserRepository $userRepository = null,
        ?EmailSenderInterface $emailSender = null,
    ): void {
        $app->group('/client', function (RouteCollectorProxy $group) use ($orderRepository, $userRepository, $emailSender): void {
            $get = $group->get('/order', new GetClientOrderAction($orderRepository));
            $get->add(new GroupMiddleware(['client'], $userRepository))->add(new AuthMiddleware());

            $save = $group->put('/order', new SaveClientOrderAction(
                $orderRepository,
                null,
                $userRepository,
                $emailSender,
            ));
            $save->add(new GroupMiddleware(['client'], $userRepository))->add(new AuthMiddleware());
        });
    }
}
