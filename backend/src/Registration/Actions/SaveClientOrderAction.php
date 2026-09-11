<?php

declare(strict_types=1);

namespace App\Registration\Actions;

use App\Auth\Services\SessionService;
use App\Configuration\Data\FrontendConfigRepository;
use App\Registration\Data\OrderRepository;
use App\Registration\Data\OrderCategories;
use App\Registration\Data\OrderEmailQueueRepository;
use App\Registration\Data\OrderNotEditableException;
use App\Shared\Database\Database;
use App\Shared\Http\JsonRequest;
use App\Shared\Http\JsonResponse;
use App\Shared\Mail\EmailSender;
use App\Shared\Mail\EmailSenderInterface;
use App\Users\Data\UserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class SaveClientOrderAction
{
    public function __construct(
        private readonly ?OrderRepository $orders = null,
        private readonly ?SessionService $session = null,
        private readonly ?UserRepository $users = null,
        private readonly ?EmailSenderInterface $emails = null,
        private readonly ?OrderEmailQueueRepository $emailQueue = null,
        private readonly ?FrontendConfigRepository $configs = null,
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
        $adults = $this->categories($data['adults'] ?? null, OrderCategories::ADULT);
        $children = $this->categories($data['children'] ?? null, OrderCategories::CHILD);
        if ($adultsCount === null || $childrenCount === null || $adults === null || $children === null
            || $adultsCount < 1 || $adultsCount > 20 || $childrenCount < 0 || $childrenCount > 20
            || ($childrenCount === 0 && (count($adults) !== $adultsCount || $children !== []))
            || ($childrenCount > 0 && ($adults !== [] || count($children) !== $childrenCount))) {
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
                $this->campaignYear(),
                $status,
                $adultsCount,
                $childrenCount,
                $items,
            );
        } catch (OrderNotEditableException) {
            return JsonResponse::error($response, 'ORDER_NOT_EDITABLE', 409);
        } catch (Throwable) {
            return JsonResponse::error($response, 'ORDER_SAVE_FAILED', 500);
        }

        $emailSent = false;
        if (is_array($user) && is_string($user['email'] ?? null)) {
            try {
                $emails = $this->emails ?? new EmailSender();
                $message = $emails->renderOrderConfirmation($order, (string) $order['status']);
                $emails->sendStoredEmail($user['email'], $message['subject'], $message['html'], $message['text']);
                ($this->orders ?? new OrderRepository(Database::getConnection()))
                    ->markConfirmationEmailSent((string) $order['id']);
                $order = ($this->orders ?? new OrderRepository(Database::getConnection()))
                    ->findForYear($userId, $this->campaignYear()) ?? $order;
                $emailSent = true;
            } catch (Throwable $exception) {
                try {
                    $emails ??= $this->emails ?? new EmailSender();
                    $message ??= $emails->renderOrderConfirmation($order, (string) $order['status']);
                    ($this->emailQueue ?? new OrderEmailQueueRepository(Database::getConnection()))->enqueue(
                        (string) $order['id'],
                        'order_confirmation',
                        $user['email'],
                        $message,
                        $exception->getMessage(),
                    );
                } catch (Throwable) {
                    // The original save response remains successful even if queue persistence fails.
                }
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
    private function categories(mixed $value, array $allowedCategories): ?array
    {
        if (!is_array($value) || count($value) > 20 || array_filter($value, static fn (mixed $category): bool => !is_string($category) || strlen($category) > 50 || !in_array($category, $allowedCategories, true)) !== []) {
            return null;
        }

        return array_values($value);
    }

    private function campaignYear(): int
    {
        return ($this->configs ?? new FrontendConfigRepository(Database::getConnection()))->findCampaignYear();
    }
}
