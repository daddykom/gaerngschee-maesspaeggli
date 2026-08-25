<?php

declare(strict_types=1);

namespace App\Routes;

use App\Users\Data\UserRepository;
use App\Fairgate\Actions\FairgateTestAction;
use App\Middleware\AuthMiddleware;
use App\Middleware\GroupMiddleware;
use App\Shared\Mail\EmailSenderInterface;
use App\Users\Actions\CreateUserAction;
use App\Users\Actions\DeleteUserAction;
use App\Users\Actions\GetUserAction;
use App\Users\Actions\ListUsersAction;
use App\Users\Actions\UpdateUserAction;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

final class AdminRoutes
{
    public static function register(
        App $app,
        ?UserRepository $userRepository = null,
        ?EmailSenderInterface $emailSender = null,
        ?FairgateTestAction $fairgateTestAction = null,
    ): void {
        $app->group('/admin', function (RouteCollectorProxy $group) use ($userRepository, $emailSender): void {
            $list = $group->get('/users', new ListUsersAction($userRepository));
            $list->add(new GroupMiddleware(['admin'], $userRepository))->add(new AuthMiddleware());

            $detail = $group->get('/users/{userId}', new GetUserAction($userRepository));
            $detail->add(new GroupMiddleware(['admin', 'user'], $userRepository))->add(new AuthMiddleware());

            $create = $group->post('/users', new CreateUserAction($userRepository, $emailSender));
            $create->add(new GroupMiddleware(['admin'], $userRepository))->add(new AuthMiddleware());

            $update = $group->patch('/users/{userId}', new UpdateUserAction($userRepository, $emailSender));
            $update->add(new GroupMiddleware(['admin', 'user'], $userRepository))->add(new AuthMiddleware());

            $delete = $group->delete('/users/{userId}', new DeleteUserAction($userRepository));
            $delete->add(new GroupMiddleware(['admin'], $userRepository))->add(new AuthMiddleware());

            $fairgateTest = $group->get('/fairgate/test', $fairgateTestAction ?? new FairgateTestAction());
            $fairgateTest->add(new GroupMiddleware(['admin'], $userRepository))->add(new AuthMiddleware());
        });
    }
}
