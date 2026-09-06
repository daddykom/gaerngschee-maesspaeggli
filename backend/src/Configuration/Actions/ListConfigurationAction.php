<?php

declare(strict_types=1);

namespace App\Configuration\Actions;

use App\Shared\Database\Database;
use App\Configuration\Data\FrontendConfigRepository;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ListConfigurationAction
{
    public function __construct(private readonly ?FrontendConfigRepository $configs = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $user = $request->getAttribute('user');
        $configs = $this->configs ?? new FrontendConfigRepository(Database::getConnection());

        return JsonResponse::success($response, $configs->findVisibleForGroup($user['group']));
    }
}
