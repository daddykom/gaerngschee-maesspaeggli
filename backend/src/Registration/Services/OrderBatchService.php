<?php

declare(strict_types=1);

namespace App\Registration\Services;

use App\Configuration\Data\FrontendConfigRepository;
use App\Fairgate\Services\FairgateContactProvider;
use App\Fairgate\Services\FairgateBatchContactProvider;
use App\Registration\Data\OrderEmailQueueRepository;
use App\Registration\Data\OrderRepository;
use App\Registration\Data\RegistrationTokenRepository;
use App\Registration\Services\QrCodeGenerator;
use App\Shared\Mail\EmailSenderInterface;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class OrderBatchService
{
    private const CATEGORIES = ['catA', 'catB', 'catC', 'catD', 'catE', 'catF', 'catG'];
    private const INTERVAL_CONFIG = 'fairgate_email_interval_days';
    private const TOKEN_RETENTION_CONFIG = 'registration_token_retention_days';

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderEmailQueueRepository $queue,
        private readonly FrontendConfigRepository $config,
        private readonly FairgateContactProvider $fairgate,
        private readonly EmailSenderInterface $emails,
        private readonly RegistrationTokenRepository $tokens,
        private readonly ?QrCodeGenerator $qrCodes = null,
    ) {
    }

    /** @return array{loaded: int, sent: int, queued: int, failed: int, tokensDeleted: int} */
    public function run(): array
    {
        $this->intervalDays();
        $retentionDays = $this->tokenRetentionDays();
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $result = [
            'loaded' => 0,
            'sent' => 0,
            'queued' => 0,
            'failed' => 0,
            'tokensDeleted' => $this->tokens->deleteExpiredBefore($now->modify('-' . $retentionDays . ' days')),
        ];
        $fairgateContacts = $this->fairgateContacts();

        foreach ($this->queue->pending() as $email) {
            try {
                $this->emails->sendStoredEmail($email['recipient'], $email['subject'], $email['html_body'], $email['text_body']);
                $this->queue->remove($email['id']);
                if ($email['email_type'] === 'order_confirmation') {
                    $this->orders->markBatchEmailSent($email['order_id']);
                } elseif ($email['email_type'] === 'delivery_qrcode') {
                    $this->orders->markQrCodeSent($email['order_id']);
                } else {
                    $this->orders->markFairgateReminderSent($email['order_id']);
                }
                $result['sent']++;
            } catch (Throwable $exception) {
                $this->queue->recordFailure($email['id'], $exception->getMessage());
                $this->log('Queued email failed', $email['order_id'], $exception);
                $result['failed']++;
            }
        }

        foreach ($this->orders->findProvisionalWithUsers() as $entry) {
            $result['loaded']++;
            $order = $entry['order'];
            if ($this->queue->hasPendingForOrder($order['id'])) {
                continue;
            }

            try {
                $data = $this->contactData($entry['email'], $fairgateContacts);
                if (!is_array($data)) {
                    $this->processMissingFairgate($order, $entry['email'], $result);
                    continue;
                }

                $adults = $this->adultsCount($data);
                $children = $this->childrenCount($data);
                $corrected = $adults !== $order['adultsCount'] || $children !== $order['childrenCount'];
                if ($corrected) {
                    $items = $this->correctItems($order['items'], $adults, $children);
                    $this->orders->updateFromBatch($order['id'], $adults, $children, $items);
                    $order = $this->orders->findForYear($order['userId'], $order['year']) ?? $order;
                    $order['correctionNotice'] = true;
                    $order['startUrl'] = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/') . '/start';
                }
                $this->sendOrQueueConfirmation($order, $entry['email'], $result);
            } catch (Throwable $exception) {
                $this->log('Order batch failed', $order['id'], $exception);
                $result['failed']++;
            }
        }

        foreach ($this->orders->findToDeliverWithUsers() as $entry) {
            $result['loaded']++;
            $order = $entry['order'];
            if ($this->queue->hasPendingForOrder($order['id'])) {
                continue;
            }

            try {
                $token = is_string($order['deliveryToken']) && $order['deliveryToken'] !== ''
                    ? $order['deliveryToken']
                    : $this->createDeliveryToken($order['id']);
                $deliveryUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/') . '/delivery?token=' . rawurlencode($token);
                $qrDataUri = ($this->qrCodes ?? new QrCodeGenerator())->generateDataUri($deliveryUrl);
                $message = $this->emails->renderDeliveryNotification($order, $deliveryUrl, $qrDataUri);
                try {
                    $this->emails->sendStoredEmail($entry['email'], $message['subject'], $message['html'], $message['text']);
                    $this->orders->markQrCodeSent($order['id']);
                    $result['sent']++;
                } catch (Throwable $exception) {
                    $this->queue->enqueue($order['id'], 'delivery_qrcode', $entry['email'], $message, $exception->getMessage());
                    $this->log('Delivery notification failed and queued', $order['id'], $exception);
                    $result['queued']++;
                }
            } catch (Throwable $exception) {
                $this->log('Delivery batch failed', $order['id'], $exception);
                $result['failed']++;
            }
        }

        return $result;
    }

    private function createDeliveryToken(string $orderId): string
    {
        $token = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $this->orders->setDeliveryToken($orderId, $token);
        return $token;
    }

    /** @return array<string, string>|null */
    private function fairgateContacts(): ?array
    {
        if (!$this->fairgate instanceof FairgateBatchContactProvider) {
            return null;
        }

        $contacts = [];
        foreach ($this->fairgate->findAllContacts() as $contact) {
            $contacts[strtolower(trim($contact['email']))] = $contact['contactId'];
        }

        return $contacts;
    }

    /** @param array<string, string>|null $contacts */
    private function contactData(string $email, ?array $contacts): ?array
    {
        if ($contacts === null) {
            $data = $this->fairgate->findContactDataByEmail($email)['data'] ?? null;
            return is_array($data) ? $data : null;
        }

        $contactId = $contacts[strtolower(trim($email))] ?? null;
        if ($contactId === null) {
            return null;
        }

        $data = $this->fairgate->findContactDataById($contactId)['data'] ?? null;
        return is_array($data) ? $data : null;
    }

    /** @param array<string, mixed> $order */
    private function sendOrQueueConfirmation(array $order, string $email, array &$result): void
    {
        $message = $this->emails->renderOrderConfirmation($order);
        try {
            $this->emails->sendStoredEmail($email, $message['subject'], $message['html'], $message['text']);
            $this->orders->markBatchEmailSent($order['id']);
            $result['sent']++;
        } catch (Throwable $exception) {
            $this->queue->enqueue($order['id'], 'order_confirmation', $email, $message, $exception->getMessage());
            $this->log('Order confirmation failed and queued', $order['id'], $exception);
            $result['queued']++;
        }
    }

    /** @param array<string, mixed> $order */
    private function processMissingFairgate(array $order, string $email, array &$result): void
    {
        $last = $order['fairgateReminderEmailSentAt'] ?? null;
        if (!is_string($last) || trim($last) === '') {
            return;
        }
        $interval = $this->intervalDays();
        $due = new DateTimeImmutable($last, new DateTimeZone('UTC')) < new DateTimeImmutable('now', new DateTimeZone('UTC'))->modify('-' . $interval . ' days');
        if (!$due) {
            return;
        }
        $message = [
            'subject' => 'Bitte melde dich bei Fairgate an',
            'html' => sprintf(
                '<p>Bitte melde dich bei Fairgate an, damit wir deine Bestellung prüfen können.</p><p><a href="%s">Jetzt bei Fairgate anmelden</a></p>',
                htmlspecialchars($this->fairgateUrl(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            ),
            'text' => 'Bitte melde dich bei Fairgate an, damit wir deine Bestellung prüfen können: ' . $this->fairgateUrl(),
        ];
        try {
            $this->emails->sendStoredEmail($email, $message['subject'], $message['html'], $message['text']);
            $this->orders->markFairgateReminderSent($order['id']);
            $result['sent']++;
        } catch (Throwable $exception) {
            $this->queue->enqueue($order['id'], 'fairgate_reminder', $email, $message, $exception->getMessage());
            $this->log('Fairgate reminder failed and queued', $order['id'], $exception);
            $result['queued']++;
        }
    }

    /** @param array<string, mixed> $data */
    private function adultsCount(array $data): int
    {
        return ($data['wohnt_im_gleichen_haushalt'] ?? null) === 'Ja' ? 2 : 1;
    }

    /** @param array<string, mixed> $data */
    private function childrenCount(array $data): int
    {
        return count(array_filter(array_map(fn (int $i): string => trim((string) ($data['name_und_vorname_kind' . $i] ?? '')), range(1, 10))));
    }

    /** @param list<array{personType: string, category: string, quantity: int}> $items */
    private function correctItems(array $items, int $adults, int $children): array
    {
        $groups = ['adult' => [], 'child' => []];
        foreach ($items as $item) {
            $groups[$item['personType']][$item['category']] = $item['quantity'];
        }
        foreach (['adult' => $adults, 'child' => $children] as $type => $target) {
            $current = array_sum($groups[$type]);
            while ($current < $target) {
                $groups[$type]['catA'] = ($groups[$type]['catA'] ?? 0) + 1;
                $current++;
            }
            while ($current > $target) {
                foreach (array_reverse(self::CATEGORIES) as $category) {
                    if (($groups[$type][$category] ?? 0) > 0) {
                        $groups[$type][$category]--;
                        $current--;
                        break;
                    }
                }
            }
        }
        $result = [];
        foreach ($groups as $type => $categories) {
            foreach ($categories as $category => $quantity) {
                if ($quantity > 0) {
                    $result[] = ['personType' => $type, 'category' => $category, 'quantity' => $quantity];
                }
            }
        }
        return $result;
    }

    private function log(string $message, string $orderId, Throwable $exception): void
    {
        error_log(sprintf('%s: order=%s error=%s', $message, $orderId, $exception->getMessage()));
    }

    private function intervalDays(): int
    {
        $value = $this->config->findValueByVariableName(self::INTERVAL_CONFIG);
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
            throw new \RuntimeException('Invalid Fairgate email interval configuration.');
        }
        return (int) $value;
    }

    private function fairgateUrl(): string
    {
        $value = $this->config->findValueByVariableName('fairgate_url');
        if (!is_string($value) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw new \RuntimeException('Invalid Fairgate URL configuration.');
        }
        return $value;
    }

    private function tokenRetentionDays(): int
    {
        $value = $this->config->findValueByVariableName(self::TOKEN_RETENTION_CONFIG);
        if (!is_string($value) || !ctype_digit($value) || (int) $value < 1) {
            throw new \RuntimeException('Invalid registration token retention configuration.');
        }
        return (int) $value;
    }
}
