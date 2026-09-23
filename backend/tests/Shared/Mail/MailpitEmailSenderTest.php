<?php

declare(strict_types=1);

namespace Tests\Shared\Mail;

use App\Registration\Services\AnmeldungMailVariant;
use App\Shared\Mail\EmailSender;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;

final class MailpitEmailSenderTest extends TestCase
{
    private const API_URL = 'http://localhost:8025/api/v1';

    public function testAllEmailTypesAreDeliveredToMailpit(): void
    {
        if (!$this->mailpitIsAvailable()) {
            self::markTestSkipped('Mailpit is not available on localhost:8025.');
        }

        $suffix = bin2hex(random_bytes(4));
        $sender = new EmailSender(
            new Mailer(Transport::fromDsn(getenv('MAILER_DSN') ?: 'smtp://localhost:1025')),
            'integration@example.invalid',
            'Integration Tests',
        );

        $sender->sendAnmeldung(
            "registration-$suffix@example.com",
            AnmeldungMailVariant::ClientOrder,
            'de',
            'http://localhost:4300/client-login?token=registration-token',
        );
        foreach (['toDeliver', 'qrcode', 'delivered'] as $status) {
            $sender->sendOrderStatus("status-$status-$suffix@example.com", $status);
        }
        $sender->sendUserCreated("created-$suffix@example.com", 'temporary-secret');
        $sender->sendUserEmailChanged("changed-$suffix@example.com");
        $sender->sendPasswordReset("reset-$suffix@example.com", 'http://localhost:4300/password-reset?token=reset-token');
        $sender->sendOrderConfirmation("confirmation-definitive-$suffix@example.com", $this->order('definitive'));
        $sender->sendOrderConfirmation("confirmation-provisional-$suffix@example.com", $this->order('provisional'));

        $delivery = $sender->renderDeliveryNotification(
            $this->order('definitive'),
            'http://localhost:4300/delivery?token=delivery-token',
            'data:image/png;base64,qr-code',
        );
        $sender->sendStoredEmail(
            "delivery-$suffix@example.com",
            $delivery['subject'],
            $delivery['html'],
            $delivery['text'],
        );

        $expected = [
            "registration-$suffix@example.com" => 'Dein Link zur Mässpäggli-Bestellung',
            "status-toDeliver-$suffix@example.com" => 'Information zu deiner Mässpäggli-Bestellung',
            "status-qrcode-$suffix@example.com" => 'Information zu deiner Mässpäggli-Bestellung',
            "status-delivered-$suffix@example.com" => 'Information zu deiner Mässpäggli-Bestellung',
            "created-$suffix@example.com" => 'Dein Benutzerkonto wurde erstellt',
            "changed-$suffix@example.com" => 'Deine E-Mail-Adresse wurde geändert',
            "reset-$suffix@example.com" => 'Passwort zurücksetzen',
            "confirmation-definitive-$suffix@example.com" => 'Deine Mässpäggli-Anmeldung ist definitiv bestätigt',
            "confirmation-provisional-$suffix@example.com" => 'Deine Mässpäggli-Anmeldung wurde noch nicht bestätigt',
            "delivery-$suffix@example.com" => 'Das Mässpäggli ist bereit zur Abholung!',
        ];

        foreach ($expected as $recipient => $subject) {
            $message = $this->findMessage($recipient, $subject);
            self::assertNotNull($message, "Mailpit message not found for $recipient");
            self::assertSame($subject, $message['Subject']);
            self::assertNotSame('', trim((string) ($message['HTML'] ?? '')));
            self::assertNotSame('', trim((string) ($message['Text'] ?? '')));
        }
    }

    /** @return array<string, mixed> */
    private function order(string $status): array
    {
        return [
            'year' => 2026,
            'status' => $status,
            'adultsCount' => 1,
            'childrenCount' => 0,
            'items' => [
                ['personType' => 'adult', 'category' => 'catA', 'quantity' => 1],
            ],
        ];
    }

    /** @return array<string, mixed>|null */
    private function findMessage(string $recipient, string $subject): ?array
    {
        $deadline = microtime(true) + 5;
        do {
            $response = @file_get_contents(self::API_URL . '/messages');
            $data = is_string($response) ? json_decode($response, true) : null;
            foreach (($data['messages'] ?? []) as $message) {
                $recipients = array_map(
                    static fn (array $address): string => (string) ($address['Address'] ?? ''),
                    $message['To'] ?? [],
                );
                if (!in_array($recipient, $recipients, true) || ($message['Subject'] ?? '') !== $subject) {
                    continue;
                }

                $detail = @file_get_contents(self::API_URL . '/message/' . rawurlencode((string) $message['ID']));
                $decoded = is_string($detail) ? json_decode($detail, true) : null;
                return is_array($decoded) ? $decoded : null;
            }
            usleep(100_000);
        } while (microtime(true) < $deadline);

        return null;
    }

    private function mailpitIsAvailable(): bool
    {
        $context = stream_context_create(['http' => ['timeout' => 1]]);

        return @file_get_contents(self::API_URL . '/messages', false, $context) !== false;
    }
}
