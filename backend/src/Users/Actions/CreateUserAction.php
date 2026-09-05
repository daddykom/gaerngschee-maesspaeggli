<?php

declare(strict_types=1);

namespace App\Users\Actions;

use App\Users\Data\UserRepository;
use App\Shared\Mail\EmailSender;
use App\Shared\Mail\EmailSenderInterface;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class CreateUserAction
{
    public function __construct(
        private readonly ?UserRepository $users = null,
        private readonly ?EmailSenderInterface $emails = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $data = JsonRequest::body($request);
        $email = JsonRequest::string($data, 'email');
        $group = JsonRequest::string($data, 'group');
        $email = $email === null ? null : trim($email);
        if ($email === null || filter_var($email, FILTER_VALIDATE_EMAIL) === false || !in_array($group, ['admin', 'user', 'client'], true)) {
            return JsonResponse::error($response, 'INVALID_USER_DATA', 422);
        }

        $repository = $this->users ?? new UserRepository();
        if ($repository->findByEmail($email) !== null) {
            return JsonResponse::error($response, 'EMAIL_ALREADY_REGISTERED', 409);
        }

        $temporaryPassword = rtrim(strtr(base64_encode(random_bytes(18)), '+/', '-_'), '=');
        $user = null;
        try {
            $user = $repository->createUser($email, $temporaryPassword, $group, true);
            ($this->emails ?? new EmailSender())->sendUserCreated($user['email'], $temporaryPassword);
        } catch (PDOException $exception) {
            return JsonResponse::error($response, $exception->getCode() === '23000' ? 'EMAIL_ALREADY_REGISTERED' : 'USER_CREATION_FAILED', $exception->getCode() === '23000' ? 409 : 500);
        } catch (Throwable) {
            if (is_array($user) && isset($user['id'])) {
                $repository->deleteUser($user['id']);
            }
            return JsonResponse::error($response, 'EMAIL_DELIVERY_FAILED', 503);
        }

        return JsonResponse::success($response, ['user' => $user, 'emailSentTo' => $user['email']], 201);
    }
}
