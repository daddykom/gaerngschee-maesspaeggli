<?php

declare(strict_types=1);

namespace App\Auth\Actions;

use App\Auth\Services\PasswordResetTokenService;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use App\Shared\Mail\EmailSender;
use App\Shared\Mail\EmailSenderInterface;
use App\Users\Data\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class PasswordResetRequestAction
{
    public function __construct(
        private readonly ?UserRepository $users = null,
        private readonly ?PasswordResetTokenService $tokens = null,
        private readonly ?EmailSenderInterface $emails = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $email = JsonRequest::string(JsonRequest::body($request), 'email', 254);
        if ($email === null || filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
            return JsonResponse::error($response, 'INVALID_EMAIL', 422);
        }

        $user = ($this->users ?? new UserRepository())->findByEmail($email);
        if ($user !== null) {
            try {
                $token = ($this->tokens ?? new PasswordResetTokenService())->issue((string) $user['id'])['token'];
                $frontendBaseUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');
                ($this->emails ?? new EmailSender())->sendPasswordReset(
                    (string) $user['email'],
                    $frontendBaseUrl . '/password-reset?token=' . rawurlencode($token),
                );
            } catch (Throwable) {
                return JsonResponse::error($response, 'PASSWORD_RESET_REQUEST_FAILED', 503);
            }
        }

        return JsonResponse::success($response, ['sent' => true], 202);
    }
}
