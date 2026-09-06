<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Registration\Services\AnmeldungService;
use App\Registration\Services\RegistrationTokenService;
use App\Shared\Mail\EmailSender;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class StartRegistrationAction
{
    private const SUPPORTED_LOCALES = ['de'];

    public function __construct(
        private readonly ?AnmeldungService $anmeldung = null,
        private readonly ?RegistrationTokenService $tokens = null,
    )
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = JsonRequest::body($request);
        $email = JsonRequest::string($data, 'email');
        $locale = strtolower(JsonRequest::string($data, 'language') ?? 'de');
        if ($email === null || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
            return JsonResponse::error($response, 'INVALID_EMAIL', 422);
        }
        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return JsonResponse::error($response, 'UNSUPPORTED_LANGUAGE', 422);
        }

        try {
            $token = ($this->tokens ?? new RegistrationTokenService())->issue(strtolower(trim($email)))['token'];
            $frontendBaseUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');
            $loginUrl = $frontendBaseUrl . '/client-login?token=' . rawurlencode($token);
            ($this->anmeldung ?? self::createService())->sendRegistrationLink($email, $loginUrl, $locale);
        } catch (Throwable) {
            return JsonResponse::error($response, 'REQUEST_FAILED', 503);
        }

        return JsonResponse::success($response, ['sent' => true], 202);
    }

    private static function createService(): AnmeldungService
    {
        return new AnmeldungService(new EmailSender());
    }
}
