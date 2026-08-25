<?php

declare(strict_types=1);

namespace App\Registration\Services;

use App\Fairgate\Services\FairgateContactProvider;
use App\Shared\Mail\EmailSenderInterface;
use App\Users\Data\UserRepository;
use InvalidArgumentException;

final class AnmeldungService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FairgateContactProvider $fairgate,
        private readonly EmailSenderInterface $emailSender,
    ) {
    }

    public function sendInformationEmail(string $email, string $locale = 'de'): void
    {
        $email = strtolower(trim($email));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        $userExists = $this->userRepository->findByEmail($email) !== null;
        $fairgateExists = $this->fairgate->hasContactByEmail($email);
        $variant = AnmeldungMailVariant::fromChecks($userExists, $fairgateExists);

        $this->emailSender->sendAnmeldung($email, $variant, $locale);
    }
}
