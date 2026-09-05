<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Services\SessionService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class LogoutAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        (new SessionService())->clear();

        return $response->withStatus(204);
    }
}
