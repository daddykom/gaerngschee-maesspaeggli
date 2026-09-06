<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AdminAccountSeeder extends AbstractSeed
{
    public function run(): void
    {
        $email = 'admin@gaerngschee.ch';
        $existingUser = $this->query(
            'SELECT id FROM users WHERE email = :email',
            ['email' => $email],
        )->fetch();

        if ($existingUser !== false) {
            return;
        }

        $this->query(
            'INSERT INTO users (id, email, password, `group`) VALUES (:id, :email, :password, :group)',
            [
                'id' => '00000000-0000-4000-8000-000000000001',
                'email' => $email,
                // Precomputed with password_hash(..., PASSWORD_DEFAULT).
                'password' => '$2y$12$QRXc.mAz2RfJTBnOtZL9VOSddjwpe6OSOawq.6LbUmCjBcAk8bp.2',
                'group' => 'admin',
            ],
        );
    }
}
