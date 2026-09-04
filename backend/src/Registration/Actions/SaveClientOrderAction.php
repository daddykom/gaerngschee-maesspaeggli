<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Auth\Services\SessionService;
use App\Registration\Data\OrderRepository;
use App\Shared\Database\Database;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use App\Shared\Mail\EmailSender;
use App\Shared\Mail\EmailSenderInterface;
use App\Users\Data\UserRepository;
use DateTimeImmutable;
use DateTimeZone;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class SaveClientOrderAction
{
    private const CATEGORIES = ['catA', 'catB', 'catC', 'catD', 'catE', 'catF', 'catG'];

    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?SessionService $session = null,
        private readonly ?UserRepository $users = null,
        private readonly ?EmailSenderInterface $emails = null,
    ) {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userId = $request->getAttribute('user_id');
        if (!is_string($userId) || $userId === '') {
            return JsonResponse::error($response, 'NOT_FOUND', 404);
        }

        $data = JsonRequest::body($request);
        $adultsCount = $this->count($data['adultsCount'] ?? null);
        $childrenCount = $this->count($data['childrenCount'] ?? null);
        $adults = $this->categories($data['adults'] ?? null);
        $children = $this->categories($data['children'] ?? null);
        if ($adultsCount === null || $childrenCount === null || $adults === null || $children === null
            || $adultsCount < 1 || count($adults) !== $adultsCount || count($children) !== $childrenCount) {
            return JsonResponse::error($response, 'INVALID_ORDER_DATA', 422);
        }

        $items = [];
        foreach ([['adult', $adults], ['child', $children]] as [$personType, $values]) {
            foreach (array_count_values($values) as $category => $quantity) {
                $items[] = [
                    'personType' => $personType,
                    'category' => $category,
                    'quantity' => $quantity,
                ];
            }
        }

        $user = ($this->users ?? new UserRepository())->findById($userId);
        if (!is_array($user) || !is_string($user['email'] ?? null)) {
            return JsonResponse::error($response, 'NOT_FOUND', 404);
        }

        $session = $this->session ?? new SessionService();
        $status = $session->getFairgateUserExists() === true ? 'definitive' : 'provisional';
        try {
            $order = ($this->orders ?? new OrderRepository(Database::getConnection()))->saveForYear(
                $userId,
                $this->currentYear(),
                $status,
                $adultsCount,
                $childrenCount,
                $items,
            );
        } catch (Throwable) {
            return JsonResponse::error($response, 'ORDER_SAVE_FAILED', 500);
        }

        $emailSent = false;
        if (is_array($user) && is_string($user['email'] ?? null)) {
            try {
                ($this->emails ?? new EmailSender())->sendOrderConfirmation($user['email'], $order);
                ($this->orders ?? new OrderRepository(Database::getConnection()))
                    ->markConfirmationEmailSent((string) $order['id']);
                $order = ($this->orders ?? new OrderRepository(Database::getConnection()))
                    ->findForYear($userId, $this->currentYear()) ?? $order;
                $emailSent = true;
            } catch (Throwable) {
                $emailSent = false;
            }
        }

        return JsonResponse::success($response, ['order' => $order, 'emailSent' => $emailSent]);
    }

    private function count(mixed $value): ?int
    {
        return is_int($value) ? $value : null;
    }

    /** @return list<string>|null */
    private function categories(mixed $value): ?array
    {
        if (!is_array($value) || array_filter($value, static fn (mixed $category): bool => !is_string($category) || !in_array($category, self::CATEGORIES, true)) !== []) {
            return null;
        }

        return array_values($value);
    }

    private function currentYear(): int
    {
        return (int) (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format('Y');
    }
}
