<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DeliverAdminOrdersAction
{
    public function __construct(private readonly ?OrderRepository $orders = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $year = (int) (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y');
        $count = ($this->orders ?? new OrderRepository(Database::getConnection()))->markDefinitiveForDelivery($year);

        return JsonResponse::success($response, ['updated' => $count]);
    }
}
