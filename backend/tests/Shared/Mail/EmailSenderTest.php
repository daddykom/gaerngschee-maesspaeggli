<?php

declare(strict_types=1);

namespace Tests\Shared\Mail;

use App\Registration\Services\AnmeldungMailVariant;
use App\Shared\Mail\EmailSender;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class EmailSenderTest extends TestCase
{
    #[DataProvider('mailVariants')]
    public function testSendAnmeldungRendersVariant(
        AnmeldungMailVariant $variant,
        string $subject,
        string $content,
        string $link,
    ): void {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email) use ($subject, $content, $link): bool {
                self::assertSame('person@example.com', $email->getTo()[0]->getAddress());
                self::assertSame($subject, $email->getSubject());
                self::assertStringContainsString($content, $email->getHtmlBody());
                self::assertStringContainsString($link, $email->getHtmlBody());

                return true;
            }));

        $sender = new EmailSender($mailer, 'noreply@example.com', 'Gärngschee-Mässpäggli');

        $sender->sendAnmeldung('person@example.com', $variant);
    }

    public function testSendUserCreatedSendsTemporaryPasswordToNewUser(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email): bool {
                self::assertSame('new@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Dein Benutzerkonto wurde erstellt', $email->getSubject());
                self::assertStringContainsString('temporary-secret', $email->getHtmlBody());
                self::assertStringContainsString('/login', $email->getHtmlBody());

                return true;
            }));

        $sender = new EmailSender($mailer, 'noreply@example.com', 'Gärngschee-Mässpäggli');

        $sender->sendUserCreated('new@example.com', 'temporary-secret');
    }

    public function testSendRegistrationLinkUsesTheSingleUseOrderUrl(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email): bool {
                self::assertSame('Dein Link zur Mässpäggli-Bestellung', $email->getSubject());
                self::assertStringContainsString('Jetzt bestellen', $email->getHtmlBody());
                self::assertStringContainsString('http://localhost:4200/client-login?token=test-token', $email->getHtmlBody());
                self::assertStringContainsString('10 Minuten gültig', $email->getHtmlBody());
                self::assertStringContainsString('http://localhost:4200/client-login?token=test-token', $email->getTextBody());
                self::assertStringNotContainsString('fairgate.ch', $email->getHtmlBody());

                return true;
            }));

        $sender = new EmailSender($mailer, 'noreply@example.com', 'Gärngschee-Mässpäggli');

        $sender->sendAnmeldung(
            'person@example.com',
            AnmeldungMailVariant::ClientOrder,
            'de',
            'http://localhost:4200/client-login?token=test-token',
        );
    }

    public function testSendOrderConfirmationRendersDefinitiveOrder(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email): bool {
                self::assertSame('client@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Deine Mässpäggli-Bestellung ist definitiv bestätigt', $email->getSubject());
                self::assertStringContainsString('definitiv bestätigt', $email->getHtmlBody());
                self::assertStringContainsString('Erwachsene ruhig: 2', $email->getHtmlBody());
                self::assertStringContainsString('Barcode', $email->getHtmlBody());

                return true;
            }));

        $sender = new EmailSender($mailer, 'noreply@example.com', 'Gärngschee-Mässpäggli');

        $sender->sendOrderConfirmation('client@example.com', [
            'year' => 2026,
            'status' => 'definitive',
            'adultsCount' => 2,
            'childrenCount' => 0,
            'items' => [
                ['personType' => 'adult', 'category' => 'catA', 'quantity' => 2],
            ],
        ]);
    }

    public function testSendUserEmailChangedNotifiesNewAddress(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email): bool {
                self::assertSame('new@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Deine E-Mail-Adresse wurde geändert', $email->getSubject());
                self::assertStringContainsString('neue Adresse', $email->getHtmlBody());

                return true;
            }));

        $sender = new EmailSender($mailer, 'noreply@example.com', 'Gärngschee-Mässpäggli');

        $sender->sendUserEmailChanged('new@example.com');
    }

    public static function mailVariants(): array
    {
        return [
            [
                AnmeldungMailVariant::UserAndFairgate,
                'Dein Mässpäggli-Konto ist bereit',
                'Du hast bereits ein Konto',
                'http://localhost:4200/login',
            ],
            [
                AnmeldungMailVariant::UserMissingFairgate,
                'Erstelle dein Mässpäggli-Konto',
                'Erstelle jetzt dein Konto',
                'http://localhost:4200/register',
            ],
            [
                AnmeldungMailVariant::UserMissingFairgateMissing,
                'Registriere dich zuerst bei Fairgate',
                'Du bist noch nicht bei Fairgate registriert.',
                'https://www.fairgate.ch',
            ],
            [
                AnmeldungMailVariant::UserAndFairgateMissing,
                'Melde dich zuerst bei Fairgate an',
                'Dein Konto besteht bereits.',
                'https://www.fairgate.ch',
            ],
        ];
    }
}
