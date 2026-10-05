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

final class GetClientDeletionOrderAction
{
    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?FrontendConfigRepository $configs = null,
        private readonly ?UserRepository $users = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();
        $email = is_string($query['email'] ?? null) ? trim($query['email']) : '';
        if ($email === '') {
            return JsonResponse::error($response, 'CLIENT_EMAIL_REQUIRED', 422);
        }

        $configs = $this->configs ?? new FrontendConfigRepository(Database::getConnection());
        $year = $configs->findCampaignYear();
        $orders = $this->orders ?? new OrderRepository(Database::getConnection());
        $order = $orders->findClientOrderByEmail($email, $year);
        if ($order === null) {
            return JsonResponse::error($response, 'CLIENT_ORDER_NOT_FOUND', 404);
        }

        $client = ($this->users ?? new UserRepository())->findById((string) $order['userId']);
        if ($client === null || $client['group'] !== 'client') {
            return JsonResponse::error($response, 'CLIENT_ORDER_NOT_FOUND', 404);
        }

        return JsonResponse::success($response, [
            'client' => [
                'id' => $client['id'],
                'email' => $client['email'],
            ],
            'year' => $year,
            'order' => $order,
            'canDelete' => in_array($order['status'], ['provisional', 'definitive'], true),
        ]);
    }
}
