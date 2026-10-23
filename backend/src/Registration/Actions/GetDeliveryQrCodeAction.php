<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Data\OrderRepository;
use App\Registration\Services\QrCodeGenerator;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetDeliveryQrCodeAction
{
    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?FrontendConfigRepository $configs = null,
        private readonly ?QrCodeGenerator $qrCodes = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $query = $request->getQueryParams();
        $token = is_string($query['token'] ?? null) ? trim($query['token']) : '';
        if ($token === '') {
            return JsonResponse::error($response, 'DELIVERY_TOKEN_REQUIRED', 422);
        }

        $configs = $this->configs ?? new FrontendConfigRepository(Database::getConnection());
        $order = ($this->orders ?? new OrderRepository(Database::getConnection()))
            ->findDeliveryOrderByToken($token, $configs->findCampaignYear());
        if ($order === null) {
            return JsonResponse::error($response, 'DELIVERY_ORDER_NOT_FOUND', 404);
        }

        $deliveryUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/')
            . '/delivery?token=' . rawurlencode($token);
        $response->getBody()->write(($this->qrCodes ?? new QrCodeGenerator())->generatePng($deliveryUrl));

        return $response
            ->withHeader('Content-Type', 'image/png')
            ->withHeader('Cache-Control', 'no-store, private');
    }
}
