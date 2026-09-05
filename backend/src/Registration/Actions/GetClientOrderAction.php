<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Registration\Data\OrderRepository;
use App\Shared\Http\JsonResponse;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetClientOrderAction
{
    public function __construct(private readonly ?OrderRepository $orders = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('user_id');
        if (!is_string($userId) || $userId === '') {
            return JsonResponse::error($response, 'NOT_FOUND', 404);
        }

        $order = ($this->orders ?? new OrderRepository(\App\Shared\Database\Database::getConnection()))
            ->findForYear($userId, $this->currentYear());

        return JsonResponse::success($response, ['order' => $order]);
    }

    private function currentYear(): int
    {
        return (int) (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y');
    }
}
