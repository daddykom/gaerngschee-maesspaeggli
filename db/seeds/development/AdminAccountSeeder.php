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
            'INSERT INTO users (id, email, password, `group`, required_password_reset) VALUES (:id, :email, :password, :group, :required_password_reset)',
            [
                'id' => '00000000-0000-4000-8000-000000000001',
                'email' => $email,
                // Precomputed with password_hash(..., PASSWORD_DEFAULT).
                'password' => '$2y$12$EXjOJ.51uXs1DyB8SR2sUO6SMCnh6RuwkR2M8XDMJhlHhKP.UCZ12',
                'group' => 'admin',
                'required_password_reset' => 0,
            ],
        );
    }
}
