<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/vendor/autoload.php';

use App\Registration\Services\RegistrationTokenService;

$email = $argv[1] ?? null;
if ($email === null || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    fwrite(STDERR, "A valid email address is required.\n");
    exit(1);
}

$now = ($argv[2] ?? '') === 'expired'
    ? new \DateTimeImmutable('-11 minutes', new \DateTimeZone('UTC'))
    : null;
$result = (new RegistrationTokenService())->issue($email, $now);
fwrite(STDOUT, $result['token']);
