<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

final class EmailSender implements EmailSenderInterface
{
    private readonly MailerInterface $mailer;
    private readonly string $fromAddress;
    private readonly string $fromName;

    public function __construct(
        ?MailerInterface $mailer = null,
        ?string $fromAddress = null,
        ?string $fromName = null,
        ?string $mailerDsn = null,
    ) {
        $this->mailer = $mailer ?? new Mailer(Transport::fromDsn(
            $mailerDsn ?? $this->requiredEnvironment('MAILER_DSN'),
        ));
        $this->fromAddress = $fromAddress ?? $this->requiredEnvironment('MAIL_FROM_ADDRESS');
        $this->fromName = $fromName ?? getenv('MAIL_FROM_NAME') ?: 'Gärngschee-Mässpäggli';
    }

    public function sendProforma(string $recipient): void
    {
        $message = (new Email())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($recipient)
            ->subject('Deine Anfrage für ein Mässpäggli')
            ->text(
                "Hallo\n\n"
                . "Dies ist eine automatische Nachricht. Wir haben deine Anfrage erhalten "
                . "und melden uns bald mit den nächsten Informationen.\n\n"
                . "Freundliche Grüsse\n"
                . "Gärngschee-Mässpäggli",
            );

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            throw new EmailDeliveryException('The email could not be sent.', 0, $exception);
        }
    }

    private function requiredEnvironment(string $name): string
    {
        $value = getenv($name);
        if ($value === false || trim($value) === '') {
            throw new EmailDeliveryException(sprintf('Missing required mail configuration: %s.', $name));
        }

        return $value;
    }
}
