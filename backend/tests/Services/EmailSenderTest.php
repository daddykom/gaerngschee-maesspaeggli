<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\AnmeldungMailVariant;
use App\Services\EmailSender;
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
