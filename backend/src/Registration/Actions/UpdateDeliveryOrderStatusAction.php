<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UpdateDeliveryOrderStatusAction
{
    public function __construct(
        private readonly string $transition,
        private readonly ?OrderRepository $orders = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $orderId = (string) $request->getAttribute('orderId', '');
        $orders = $this->orders ?? new OrderRepository(Database::getConnection());
        $updated = $this->transition === 'deliver'
            ? $orders->markDelivered($orderId)
            : $orders->undoDelivery($orderId);

        if (!$updated) {
            return JsonResponse::error($response, 'DELIVERY_STATUS_INVALID', 409);
        }

        return JsonResponse::success($response, ['status' => $this->transition === 'deliver' ? 'delivered' : 'qrcode']);
    }
}
