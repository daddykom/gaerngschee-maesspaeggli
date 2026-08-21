<?php

declare(strict_types=1);

namespace App\Routes;

use App\Data\UserRepository;
use App\Services\AnmeldungService;
use App\Services\EmailSender;
use App\Services\FairgateContactProviderFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Throwable;

final class StartRoutes
{
    private const SUPPORTED_LOCALES = ['de'];

    public static function register(App $app, ?AnmeldungService $anmeldungService = null): void
    {
        $app->group('/public', function (RouteCollectorProxy $group) use ($anmeldungService): void {
            $group->post('/start', function (ServerRequestInterface $request, ResponseInterface $response) use ($anmeldungService) {
                $data = json_decode((string) $request->getBody(), true);
                $email = is_array($data) && is_string($data['email'] ?? null) ? $data['email'] : null;
                $locale = is_array($data) && is_string($data['language'] ?? null)
                    ? strtolower($data['language'])
                    : 'de';

                if ($email === null || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
                    return self::json($response, ['error' => 'Invalid email address.'], 422);
                }

                if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
                    return self::json($response, ['error' => 'Unsupported language.'], 422);
                }

                try {
                    ($anmeldungService ?? self::createAnmeldungService())->sendInformationEmail($email, $locale);
                } catch (Throwable) {
                    return self::json($response, ['error' => 'The request could not be processed.'], 503);
                }

                return self::json(
                    $response,
                    ['sent' => true],
                    202,
                );
            });
        });
    }

    private static function createAnmeldungService(): AnmeldungService
    {
        return new AnmeldungService(
            new UserRepository(),
            FairgateContactProviderFactory::create(),
            new EmailSender(),
        );
    }

    private static function json(ResponseInterface $response, array $data, int $status): ResponseInterface
    {
        $response->getBody()->write(json_encode($data, JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
