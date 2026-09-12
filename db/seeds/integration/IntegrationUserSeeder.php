<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class IntegrationUserSeeder extends AbstractSeed
{
    public function run(): void
    {
        if ($this->query('SELECT id FROM users WHERE email = :email', ['email' => 'user@gaerngschee.ch'])->fetch() !== false) {
            return;
        }

        $this->query(
            'INSERT INTO users (id, email, password, `group`, required_password_reset)
             VALUES (:id, :email, :password, :group, :required_password_reset)',
            [
                'id' => '00000000-0000-4000-8000-000000000002',
                'email' => 'user@gaerngschee.ch',
                'password' => password_hash('secret', PASSWORD_DEFAULT),
                'group' => 'user',
                'required_password_reset' => 0,
            ],
        );
    }
}
