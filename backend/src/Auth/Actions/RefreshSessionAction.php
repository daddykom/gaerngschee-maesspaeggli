<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Services\SessionService;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RefreshSessionAction
{
    public function __construct(
        private readonly ?SessionService $session = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $session = $this->session ?? new SessionService();
        $userId = $session->getUserId();
        if ($userId === null) {
            return JsonResponse::error($response, 'SESSION_EXPIRED', 401);
        }

        $session->touchActivity();
        $status = $session->getStatus();
        if ($status === null) {
            return JsonResponse::error($response, 'SESSION_EXPIRED', 401);
        }

        return JsonResponse::success($response, [
            ...$status,
        ]);
    }
}
