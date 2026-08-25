<?php

declare(strict_types=1);

namespace App\Shared\Mail;

final class MailConfiguration
{
    /** @return array{mailer_dsn: string, from_address: string, from_name: string} */
    public static function load(): array
    {
        $path = dirname(__DIR__, 3) . '/config/mail.local.php';
        if (!is_file($path)) {
            throw new EmailDeliveryException('Missing local mail configuration.');
        }

        $configuration = require $path;
        if (!is_array($configuration)) {
            throw new EmailDeliveryException('Invalid local mail configuration.');
        }

        foreach (['mailer_dsn', 'from_address', 'from_name'] as $key) {
            if (!isset($configuration[$key]) || !is_string($configuration[$key]) || trim($configuration[$key]) === '') {
                throw new EmailDeliveryException('Incomplete local mail configuration.');
            }
        }

        return [
            'mailer_dsn' => $configuration['mailer_dsn'],
            'from_address' => $configuration['from_address'],
            'from_name' => $configuration['from_name'],
        ];
    }
}
