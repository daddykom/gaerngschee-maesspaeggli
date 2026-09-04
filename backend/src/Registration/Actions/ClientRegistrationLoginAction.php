<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Auth\Services\JwtService;
use App\Auth\Services\SessionService;
use App\Registration\Services\ClientRegistrationLoginService;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class ClientRegistrationLoginAction
{
    public function __construct(
        private readonly ?ClientRegistrationLoginService $loginService = null,
        private readonly ?JwtService $jwt = null,
        private readonly ?SessionService $session = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $token = JsonRequest::string(JsonRequest::body($request), 'token');
        if ($token === null) {
            return JsonResponse::error($response, 'INVALID_REGISTRATION_TOKEN', 401);
        }

        try {
            $result = ($this->loginService ?? self::createService())->login($token);
        } catch (Throwable) {
            return JsonResponse::error($response, 'REGISTRATION_LOGIN_FAILED', 503);
        }

        if ($result === null) {
            return JsonResponse::error($response, 'INVALID_REGISTRATION_TOKEN', 401);
        }

        $user = $result['user'];
        $token = ($this->jwt ?? new JwtService())->createToken($user['id']);
        ($this->session ?? new SessionService())->setUser($user['id'], 'client', $result['fairgateUserExists']);

        return JsonResponse::success($response, [
            'user' => $user,
            'token' => $token,
            'group' => 'client',
            'requiredPasswordReset' => false,
            'fairgateUserExists' => $result['fairgateUserExists'],
            'childrenCount' => $result['childrenCount'],
            'adultsCount' => $result['adultsCount'],
            'salutation' => $result['salutation'],
        ]);
    }

    private static function createService(): ClientRegistrationLoginService
    {
        $tokens = new \App\Registration\Services\RegistrationTokenService();
        $users = new \App\Users\Data\UserRepository();

        return new ClientRegistrationLoginService(
            $tokens,
            $users,
            \App\Fairgate\Services\FairgateContactProviderFactory::create(),
        );
    }
}
