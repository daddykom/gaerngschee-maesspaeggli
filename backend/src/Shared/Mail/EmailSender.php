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
    private const LOGO_CID = 'gaerngschee-logo';

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
        $configuration = $mailer === null || $fromAddress === null || $fromName === null
            ? MailConfiguration::load()
            : null;
        $this->mailer = $mailer ?? new Mailer(Transport::fromDsn(
            $mailerDsn ?? $configuration['mailer_dsn'],
        ));
        $this->fromAddress = $fromAddress ?? $configuration['from_address'];
        $this->fromName = $fromName ?? $configuration['from_name'];
        $this->twig = $twig ?? new Environment(
            new FilesystemLoader([
                dirname(__DIR__, 3) . '/resources/emails/anmeldung',
                dirname(__DIR__, 3) . '/resources/emails',
            ]),
        );
        $this->translator = $translator ?? $this->createTranslator();
        $this->twig->addExtension(new TranslationExtension($this->translator));
    }

    public function sendAnmeldung(
        string $recipient,
        AnmeldungMailVariant $variant,
        string $locale = 'de',
        ?string $loginUrl = null,
    ): void
    {
        if ($this->translator instanceof LocaleAwareInterface) {
            $this->translator->setLocale($locale);
        }
        $frontendBaseUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');
        $html = $this->twig->render($variant->value . '.html.twig', [
            'LOGIN_URL' => $loginUrl ?? $frontendBaseUrl . '/login',
            'LOGO_CID' => 'cid:gaerngschee-logo',
            'REGISTRATION_URL' => $frontendBaseUrl . '/register',
            'FAIRGATE_URL' => getenv('FAIRGATE_URL') ?: 'https://www.fairgate.ch',
        ]);

        $message = (new Email())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($recipient)
            ->subject($this->translator->trans('anmeldung.' . $variant->value . '.subject', [], null, $locale))
            ->text($this->plainText($html))
            ->html($html)
            ->embedFromPath(
                dirname(__DIR__, 3) . '/resources/pictures/gaerngschee-logo.png',
                'gaerngschee-logo',
                'image/png',
            );

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
            'LOGO_CID' => 'cid:' . self::LOGO_CID,
            'TEMPORARY_PASSWORD' => $temporaryPassword,
        ]);

        $this->sendUserEmail($recipient, 'Dein Benutzerkonto wurde erstellt', $html);
    }

    public function sendUserEmailChanged(string $recipient): void
    {
        $frontendBaseUrl = rtrim(getenv('FRONTEND_BASE_URL') ?: 'http://localhost:4200', '/');
        $html = $this->twig->render('user-email-changed.html.twig', [
            'LOGIN_URL' => $frontendBaseUrl . '/login',
            'LOGO_CID' => 'cid:' . self::LOGO_CID,
        ]);

        $this->sendUserEmail($recipient, 'Deine E-Mail-Adresse wurde geändert', $html);
    }

    /** @param array<string, mixed> $order */
    public function sendOrderConfirmation(string $recipient, array $order): void
    {
        $message = $this->renderOrderConfirmation($order);
        $this->sendStoredEmail($recipient, $message['subject'], $message['html'], $message['text']);
    }

    /** @return array{subject: string, html: string, text: string} */
    public function renderOrderConfirmation(array $order): array
    {
        $status = $order['status'] === 'definitive' ? 'definitive' : 'provisional';
        $order['items'] = array_map(function (array $item): array {
            $item['categoryLabel'] = $this->translator->trans(
                'order.category.' . $item['category'],
                [],
                null,
                'de',
            );
            $item['personTypeLabel'] = $item['personType'] === 'adult' ? 'Erwachsene' : 'Kinder';
            return $item;
        }, $order['items'] ?? []);
        $html = $this->twig->render('order-confirmation-' . $status . '.html.twig', [
            'LOGO_CID' => 'cid:' . self::LOGO_CID,
            'ORDER' => $order,
        ]);
        $subject = $this->translator->trans('order.confirmation.' . $status . '.subject', [], null, 'de');

        return ['subject' => $subject, 'html' => $html, 'text' => $this->plainText($html)];
    }

    /** @param array<string, mixed> $order */
    public function renderDeliveryNotification(array $order, string $deliveryUrl, string $qrDataUri): array
    {
        $html = $this->twig->render('delivery-notification.html.twig', [
            'LOGO_CID' => 'cid:' . self::LOGO_CID,
            'QR_DATA_URI' => $qrDataUri,
            'DELIVERY_URL' => $deliveryUrl,
        ]);

        return [
            'subject' => 'Deine Mässpäggli sind bereit',
            'html' => $html,
            'text' => $this->plainText($html),
        ];
    }

    public function sendStoredEmail(string $recipient, string $subject, string $html, string $text): void
    {
        $this->sendUserEmail($recipient, $subject, $html, $text);
    }

    private function sendUserEmail(string $recipient, string $subject, string $html, ?string $text = null): void
    {
        $message = (new Email())
            ->from(new Address($this->fromAddress, $this->fromName))
            ->to($recipient)
            ->subject($subject)
            ->text($text ?? $this->plainText($html))
            ->html($html)
            ->embedFromPath(
                dirname(__DIR__, 3) . '/resources/pictures/gaerngschee-logo.png',
                self::LOGO_CID,
                'image/png',
            );

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
        $html = (string) preg_replace_callback(
            '/<a\b[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is',
            static fn (array $match): string => $match[2] . ' (' . $match[1] . ')',
            $html,
        );

        return trim((string) preg_replace(
            '/\s+/',
            ' ',
            html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
        ));
    }

}
