<?php

declare(strict_types=1);

namespace App\Fairgate\Actions;

use Closure;
use App\Fairgate\Services\FairgateClient;
use App\Fairgate\Services\FairgateConfiguration;
use App\Fairgate\Services\FairgateException;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FairgateTestAction
{
    private const TEST_EMAIL = 'isabelle.joss@gaerngschee.ch';

    /** @param callable(string): array<string, mixed>|null $lookup */
    public function __construct(?callable $lookup = null)
    {
        $this->lookup = $lookup === null ? null : Closure::fromCallable($lookup);
    }

    /** @var Closure(string): array<string, mixed>|null */
    private readonly ?Closure $lookup;

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $lookup = $this->lookup;
            if ($lookup === null) {
                $configuration = FairgateConfiguration::load();
                $client = new FairgateClient(
                    baseUrl: $configuration['base_url'],
                    organizationId: $configuration['organization_id'],
                    accessKey: $configuration['access_key'],
                    publicKey: $configuration['public_key'],
                );
                $lookup = $client->findContactDataByEmail(...);
            }

            return JsonResponse::success($response, [
                'email' => self::TEST_EMAIL,
                'fairgate' => $lookup(self::TEST_EMAIL),
            ]);
        } catch (FairgateException) {
            return JsonResponse::error($response, 'FAIRGATE_TEST_FAILED', 502);
        }
    }
}
