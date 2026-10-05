<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use App\Users\Data\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DeleteClientOrderAction
{
    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?FrontendConfigRepository $configs = null,
        private readonly ?UserRepository $users = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $userId = is_string($args['userId'] ?? null) ? $args['userId'] : '';
        $users = $this->users ?? new UserRepository();
        $client = $users->findById($userId);
        if ($client === null || $client['group'] !== 'client') {
            return JsonResponse::error($response, 'CLIENT_ORDER_NOT_FOUND', 404);
        }

        $configs = $this->configs ?? new FrontendConfigRepository(Database::getConnection());
        $year = $configs->findCampaignYear();
        $orders = $this->orders ?? new OrderRepository(Database::getConnection());
        $order = $orders->findForYear($userId, $year);
        if ($order === null) {
            return JsonResponse::error($response, 'CLIENT_ORDER_NOT_FOUND', 404);
        }
        if (!in_array($order['status'], ['provisional', 'definitive'], true)) {
            return JsonResponse::error($response, 'CLIENT_ORDER_NOT_DELETABLE', 409);
        }

        if (!$orders->deleteCurrentYearOrder($userId, $year)) {
            return JsonResponse::error($response, 'CLIENT_ORDER_NOT_DELETABLE', 409);
        }

        return JsonResponse::success($response, [
            'deleted' => true,
            'userId' => $userId,
            'year' => $year,
            'orderId' => $order['id'],
        ]);
    }
}
