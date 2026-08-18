<?php

declare(strict_types=1);

namespace App\Services;

use App\Data\UserRepository;
use InvalidArgumentException;

final class AnmeldungService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly FairgateContactProvider $fairgate,
        private readonly EmailSenderInterface $emailSender,
    ) {
    }

    public function sendInformationEmail(string $email): void
    {
        $email = strtolower(trim($email));
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Invalid email address.');
        }

        // Both checks determine the future mail template. The first version uses one proforma template.
        $this->userRepository->findByEmail($email);
        $this->fairgate->findContactByEmail($email);
        $this->emailSender->sendProforma($email);
    }
}
