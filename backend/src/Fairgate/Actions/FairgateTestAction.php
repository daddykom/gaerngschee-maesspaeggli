<?php

declare(strict_types=1);

namespace App\Fairgate\Actions;

use App\Fairgate\Services\FairgateClient;
use App\Fairgate\Services\FairgateConfiguration;
use App\Fairgate\Services\FairgateException;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class FairgateTestAction
{
    private const TEST_EMAIL = 'isabelle.joss@gaerngschee.ch';

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $configuration = FairgateConfiguration::load();
            $client = new FairgateClient(
                baseUrl: $configuration['base_url'],
                organizationId: $configuration['organization_id'],
                accessKey: $configuration['access_key'],
                publicKey: $configuration['public_key'],
            );

            return JsonResponse::success($response, [
                'email' => self::TEST_EMAIL,
                'fairgate' => $client->findContactDataByEmail(self::TEST_EMAIL),
            ]);
        } catch (FairgateException) {
            return JsonResponse::error($response, 'FAIRGATE_TEST_FAILED', 502);
        }
    }
}
