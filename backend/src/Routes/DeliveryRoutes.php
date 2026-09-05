<?php

declare(strict_types=1);

namespace App\Routes;

use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use App\Registration\Actions\GetDeliveryOrderAction;
use App\Registration\Actions\UpdateDeliveryOrderStatusAction;
use App\Registration\Data\OrderRepository;
use App\Users\Data\UserRepository;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class DeliveryRoutes
{
    public static function register(App $app, ?OrderRepository $orders = null, ?UserRepository $users = null): void
    {
        $app->group('/delivery', function (RouteCollectorProxy $group) use ($orders, $users): void {
            $get = $group->get('/order', new GetDeliveryOrderAction($orders));
            $get->add(new GroupMiddleware(['user', 'admin'], $users))->add(new AuthMiddleware());

            $deliver = $group->post('/orders/{orderId}/deliver', new UpdateDeliveryOrderStatusAction('deliver', $orders));
            $deliver->add(new GroupMiddleware(['user', 'admin'], $users))->add(new AuthMiddleware());

            $undo = $group->post('/orders/{orderId}/undo', new UpdateDeliveryOrderStatusAction('undo', $orders));
            $undo->add(new GroupMiddleware(['user', 'admin'], $users))->add(new AuthMiddleware());
        });
    }
}
