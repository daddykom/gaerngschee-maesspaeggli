<?php

declare(strict_types=1);

namespace App\Configuration\Actions;

use App\Shared\Database\Database;
use App\Configuration\Data\FrontendConfigRepository;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class UpdateConfigurationAction
{
    public function __construct(private readonly ?FrontendConfigRepository $configs = null)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $data = JsonRequest::body($request);
        $value = $data['value'] ?? null;
        if (!is_string($value) && !$this->isStringArray($value)) {
            return JsonResponse::error($response, 'INVALID_CONFIGURATION_DATA', 422);
        }

        $user = $request->getAttribute('user');
        $configs = $this->configs ?? new FrontendConfigRepository(Database::getConnection());
        try {
            $config = $configs->update((string) ($args['configId'] ?? ''), $user['group'], $value);
        } catch (Throwable) {
            return JsonResponse::error($response, 'CONFIGURATION_UPDATE_FAILED', 500);
        }

        return $config === null
            ? JsonResponse::error($response, 'NOT_FOUND', 404)
            : ($config === false
                ? JsonResponse::error($response, 'FORBIDDEN', 403)
                : JsonResponse::success($response, $config));
    }

    private function isStringArray(mixed $value): bool
    {
        return is_array($value)
            && array_is_list($value)
            && count(array_filter($value, 'is_string')) === count($value);
    }
}
