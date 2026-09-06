<?php

declare(strict_types=1);

namespace App\Users\Actions;

use App\Users\Data\UserRepository;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ListUsersAction
{
    public function __construct(private readonly ?UserRepository $users = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        return JsonResponse::success($response, ($this->users ?? new UserRepository())->findAll());
    }
}
