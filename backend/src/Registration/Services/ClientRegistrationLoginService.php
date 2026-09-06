<?php

declare(strict_types=1);

namespace App\Registration\Services;

use App\Fairgate\Services\FairgateContactProvider;
use App\Users\Data\UserRepository;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class ClientRegistrationLoginService
{
    public function __construct(
        private readonly RegistrationTokenService $tokens,
        private readonly UserRepository $users,
        private readonly FairgateContactProvider $fairgate,
    ) {
    }

    /** @return array<string, mixed>|null */
    public function login(string $token): ?array
    {
        $email = $this->tokens->consume($token, new DateTimeImmutable('now', new DateTimeZone('UTC')));
        if ($email === null) {
            return null;
        }

        $user = $this->users->findByEmail($email);
        if ($user !== null && ($user['group'] ?? null) !== 'client') {
            return null;
        }

        if ($user === null) {
            $user = $this->users->createUser($email, bin2hex(random_bytes(24)), 'client');
        }

        $fairgateResponse = $this->fairgate->findContactDataByEmail($email);
        $data = $fairgateResponse['data'] ?? null;
        $fairgateUserExists = is_array($data);

        return [
            'user' => $user,
            'fairgateUserExists' => $fairgateUserExists,
            'childrenCount' => $this->childrenCount($data),
            'adultsCount' => $this->adultsCount($data),
            'salutation' => $this->salutation($data),
        ];
    }

    /** @param array<string, mixed>|null $data */
    private function childrenCount(?array $data): int
    {
        if ($data === null) {
            return 0;
        }

        $count = 0;
        for ($index = 1; $index <= 10; $index++) {
            if (trim((string) ($data['name_und_vorname_kind' . $index] ?? '')) !== '') {
                $count++;
            }
        }

        return $count;
    }

    /** @param array<string, mixed>|null $data */
    private function adultsCount(?array $data): int
    {
        return ($data['wohnt_im_gleichen_haushalt'] ?? null) === 'Ja' ? 2 : 1;
    }

    /** @param array<string, mixed>|null $data */
    private function salutation(?array $data): string
    {
        if ($data === null) {
            return 'Guten Tag';
        }

        $language = strtolower((string) ($data['correspondence_lang'] ?? 'de'));
        $gender = strtolower((string) ($data['gender'] ?? ''));
        $informal = strtolower((string) ($data['salutation'] ?? '')) === 'informal';

        if ($informal) {
            return match ($language) {
                'fr' => 'Bonjour',
                'it' => 'Buongiorno',
                'en' => 'Hello',
                default => 'Hallo',
            };
        }

        return match ($language) {
            'fr' => $gender === 'female' ? 'Madame' : ($gender === 'male' ? 'Monsieur' : 'Bonjour'),
            'it' => $gender === 'female' ? 'Signora' : ($gender === 'male' ? 'Signor' : 'Buongiorno'),
            'en' => $gender === 'female' ? 'Ms.' : ($gender === 'male' ? 'Mr.' : 'Hello'),
            default => $gender === 'female' ? 'Frau' : ($gender === 'male' ? 'Herr' : 'Guten Tag'),
        };
    }
}
