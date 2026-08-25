<?php

declare(strict_types=1);

namespace App\Shared\Mail;

use App\Registration\Services\AnmeldungMailVariant;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class EmailSender implements EmailSenderInterface
{
    private readonly MailerInterface $mailer;
    private readonly string $fromAddress;
    private readonly string $fromName;
    private readonly Environment $twig;
    private readonly TranslatorInterface $translator;

    public function __construct(
        ?MailerInterface $mailer = null,
        ?string $fromAddress = null,
        ?string $fromName = null,
        ?string $mailerDsn = null,
        ?Environment $twig = null,
        ?TranslatorInterface $translator = null,
    ) {
        $this->mailer = $mailer ?? new Mailer(Transport::fromDsn(
            $mailerDsn ?? $this->requiredEnvironment('MAILER_DSN'),
        ));
        $this->fromAddress = $fromAddress ?? $this->requiredEnvironment('MAIL_FROM_ADDRESS');
        $this->fromName = $fromName ?? getenv('MAIL_FROM_NAME') ?: 'Gärngschee-Mässpäggli';
        $this->twig = $twig ?? new Environment(
            new FilesystemLoader([
                dirname(__DIR__, 3) . '/resources/emails/anmeldung',
                dirname(__DIR__, 3) . '/resources/emails',
            ]),
        );
        $this->translator = $translator ?? $this->createTranslator();
        $this->twig->addExtension(new TranslationExtension($this->translator));
    }

    public function sendAnmeldung(string $recipient, AnmeldungMailVariant $variant, string $locale = 'de'): void
    {
        if ($this->translator instanceof LocaleAwareInterface) {
            $this->translator->setLocale($locale);
        }
        $frontendBaseUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');
        $html = $this->twig->render($variant->value . '.html.twig', [
            'LOGIN_URL' => $frontendBaseUrl . '/login',
            'REGISTRATION_URL' => $frontendBaseUrl . '/register',
            'FAIRGATE_URL' => getenv('FAIRGATE_URL') ?: 'https://www.fairgate.ch',
        ]);

        $message = (new Email())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($recipient)
            ->subject($this->translator->trans('anmeldung.' . $variant->value . '.subject', [], null, $locale))
            ->text($this->plainText($html))
            ->html($html);

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            throw new EmailDeliveryException('The email could not be sent.', 0, $exception);
        }
    }

    public function sendUserCreated(string $recipient, string $temporaryPassword): void
    {
        $frontendBaseUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');
        $html = $this->twig->render('user-created.html.twig', [
            'LOGIN_URL' => $frontendBaseUrl . '/login',
            'TEMPORARY_PASSWORD' => $temporaryPassword,
        ]);

        $this->sendUserEmail($recipient, 'Dein Benutzerkonto wurde erstellt', $html);
    }

    public function sendUserEmailChanged(string $recipient): void
    {
        $frontendBaseUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');
        $html = $this->twig->render('user-email-changed.html.twig', [
            'LOGIN_URL' => $frontendBaseUrl . '/login',
        ]);

        $this->sendUserEmail($recipient, 'Deine E-Mail-Adresse wurde geändert', $html);
    }

    private function sendUserEmail(string $recipient, string $subject, string $html): void
    {
        $message = (new Email())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($recipient)
            ->subject($subject)
            ->text($this->plainText($html))
            ->html($html);

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            throw new EmailDeliveryException('The email could not be sent.', 0, $exception);
        }
    }

    private function createTranslator(): TranslatorInterface
    {
        $translator = new Translator(getenv('APP_LOCALE') ?: 'de');
        $translator->addLoader('array', new ArrayLoader());
        $translator->addLoader('php', new PhpFileLoader());
        $translator->addResource(
            'php',
            dirname(__DIR__, 3) . '/translations/messages.de.php',
            'de',
        );

        return $translator;
    }

    private function plainText(string $html): string
    {
        return trim((string) preg_replace(
            '/\s+/',
            ' ',
            html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ));
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
