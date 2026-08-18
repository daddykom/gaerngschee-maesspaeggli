<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Services\EmailSender;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use PHPUnit\Framework\TestCase;

final class EmailSenderTest extends TestCase
{
    public function testSendProformaSendsGermanEmailToRecipient(): void
    {
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects(self::once())
            ->method('send')
            ->with(self::callback(static function (Email $email): bool {
                self::assertSame('person@example.com', $email->getTo()[0]->getAddress());
                self::assertSame('Deine Anfrage für ein Mässpäggli', $email->getSubject());
                self::assertStringContainsString('Wir haben deine Anfrage erhalten', $email->getTextBody());

                return true;
            }));

        $sender = new EmailSender(
            $mailer,
            'noreply@example.com',
            'Gärngschee-Mässpäggli',
        );

        $sender->sendProforma('person@example.com');
    }
}
