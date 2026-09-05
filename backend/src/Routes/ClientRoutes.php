<?php

declare(strict_types=1);

namespace App\Routes;

use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use App\Registration\Actions\GetClientOrderAction;
use App\Registration\Actions\SaveClientOrderAction;
use App\Registration\Data\OrderRepository;
use App\Registration\Data\OrderEmailQueueRepository;
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
        ?OrderEmailQueueRepository $emailQueue = null,
    ): void {
        $app->group('/client', function (RouteCollectorProxy $group) use ($orderRepository, $userRepository, $emailSender, $emailQueue): void {
            $get = $group->get('/order', new GetClientOrderAction($orderRepository));
            $get->add(new GroupMiddleware(['client'], $userRepository))->add(new AuthMiddleware());

            $save = $group->put('/order', new SaveClientOrderAction(
                $orderRepository,
                null,
                $userRepository,
                $emailSender,
                $emailQueue,
            ));
            $save->add(new GroupMiddleware(['client'], $userRepository))->add(new AuthMiddleware());
        });
    }
}
