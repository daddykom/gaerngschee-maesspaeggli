<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonResponse;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class GetAdminOverviewAction
{
    private const RECENT_DAYS_CONFIG = 'provisional_order_recent_days';

    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?FrontendConfigRepository $configs = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $configs = $this->configs ?? new FrontendConfigRepository(Database::getConnection());
        $recentDays = $configs->findValueByVariableName(self::RECENT_DAYS_CONFIG);
        if (!is_string($recentDays) || !ctype_digit($recentDays) || (int) $recentDays < 1) {
            return JsonResponse::error($response, 'INVALID_OVERVIEW_CONFIGURATION', 500);
        }

        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $overview = ($this->orders ?? new OrderRepository(Database::getConnection()))
            ->findAdminOverview((int) $now->format('Y'), $now->modify('-' . $recentDays . ' days')->format('Y-m-d H:i:s'));

        return JsonResponse::success($response, [
            'year' => (int) $now->format('Y'),
            'recentDays' => (int) $recentDays,
            ...$overview,
        ]);
    }
}
