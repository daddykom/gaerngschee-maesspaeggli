<?php

declare(strict_types=1);

namespace App\Shared\Mail;

final class MailConfiguration
{
    /** @return array{mailer_dsn: string, from_address: string, from_name: string} */
    public static function load(): array
    {
        $configuration = [
            'mailer_dsn' => getenv('MAILER_DSN') ?: '',
            'from_address' => getenv('MAIL_FROM_ADDRESS') ?: '',
            'from_name' => getenv('MAIL_FROM_NAME') ?: '',
        ];

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
