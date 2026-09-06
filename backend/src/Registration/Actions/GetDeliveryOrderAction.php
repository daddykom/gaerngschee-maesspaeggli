<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Registration\Data\OrderRepository;
use App\Fairgate\Services\FairgateContactProvider;
use App\Fairgate\Services\FairgateContactProviderFactory;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetDeliveryOrderAction
{
    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?FairgateContactProvider $fairgate = null,
    )
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

        $fairgate = null;
        if (!$viaToken) {
            try {
                $fairgate = ($this->fairgate ?? FairgateContactProviderFactory::create())
                    ->findContactDataByEmail($email)['data'] ?? null;
            } catch (\Throwable) {
                $fairgate = null;
            }
        }

        $children = [];
        for ($index = 1; $index <= 10; $index++) {
            $name = trim((string) ($fairgate['name_und_vorname_kind' . $index] ?? ''));
            if ($name !== '') {
                $children[] = [
                    'name' => $name,
                    'birthDate' => $fairgate['geburtsdatum_kind' . $index] ?? null,
                ];
            }
        }

        $firstName = trim((string) ($fairgate['first_name'] ?? ''));
        $lastName = trim((string) ($fairgate['last_name'] ?? ''));
        $clientName = trim($firstName . ' ' . $lastName);

        return JsonResponse::success($response, [
            'order' => $order,
            'viaToken' => $viaToken,
            'clientName' => $clientName !== '' ? $clientName : null,
            'children' => $children,
        ]);
    }
}
