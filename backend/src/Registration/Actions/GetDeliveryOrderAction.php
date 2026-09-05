<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetDeliveryOrderAction
{
    public function __construct(private readonly ?OrderRepository $orders = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();
        $token = is_string($query['token'] ?? null) ? trim($query['token']) : '';
        $email = is_string($query['email'] ?? null) ? trim($query['email']) : '';
        $orders = $this->orders ?? new OrderRepository(Database::getConnection());

        if ($token !== '') {
            $order = $orders->findDeliveryOrderByToken($token);
            $viaToken = true;
        } elseif ($email !== '') {
            $order = $orders->findDeliveryOrderByEmail($email);
            $viaToken = false;
        } else {
            return JsonResponse::error($response, 'DELIVERY_SEARCH_REQUIRED', 422);
        }

        if ($order === null) {
            return JsonResponse::error($response, 'DELIVERY_ORDER_NOT_FOUND', 404);
        }

        return JsonResponse::success($response, ['order' => $order, 'viaToken' => $viaToken]);
    }
}
