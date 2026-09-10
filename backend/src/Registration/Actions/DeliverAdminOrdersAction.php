<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DeliverAdminOrdersAction
{
    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?FrontendConfigRepository $configs = null,
    )
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $year = ($this->configs ?? new FrontendConfigRepository(Database::getConnection()))->findCampaignYear();
        $count = ($this->orders ?? new OrderRepository(Database::getConnection()))->markDefinitiveForDelivery($year);

        return JsonResponse::success($response, ['updated' => $count]);
    }
}
