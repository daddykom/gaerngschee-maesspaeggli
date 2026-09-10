<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetClientOrderAction
{
    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?FrontendConfigRepository $configs = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('user_id');
        if (!is_string($userId) || $userId === '') {
            return JsonResponse::error($response, 'NOT_FOUND', 404);
        }

        $configs = $this->configs ?? new FrontendConfigRepository(Database::getConnection());
        $order = ($this->orders ?? new OrderRepository(Database::getConnection()))
            ->findForYear($userId, $configs->findCampaignYear());

        return JsonResponse::success($response, ['order' => $order]);
    }

}
