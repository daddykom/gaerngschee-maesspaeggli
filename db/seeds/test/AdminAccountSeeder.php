<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

final class AdminAccountSeeder extends AbstractSeed
{
    private const DEFAULT_ADMIN_PASSWORD_HASH = '$2y$12$EXjOJ.51uXs1DyB8SR2sUO6SMCnh6RuwkR2M8XDMJhlHhKP.UCZ12';

    public function run(): void
    {
        $email = 'admin@gaerngschee.ch';
        $existingUser = $this->query(
            'SELECT id FROM users WHERE email = :email',
            ['email' => $email],
        )->fetch();

        if ($existingUser !== false) {
            $this->createRegularUser();
            return;
        }

        $this->query(
            'INSERT INTO users (id, email, password, `group`) VALUES (:id, :email, :password, :group)',
            [
                'id' => '00000000-0000-4000-8000-000000000001',
                'email' => $email,
                'password' => $this->adminPasswordHash(),
                'group' => 'admin',
            ],
        );

        $this->createRegularUser();
    }

    private function adminPasswordHash(): string
    {
        $password = getenv('ADMIN_SEED_PASSWORD');
        if (is_string($password) && $password !== '') {
            return password_hash($password, PASSWORD_DEFAULT);
        }

        return self::DEFAULT_ADMIN_PASSWORD_HASH;
    }

    private function createRegularUser(): void
    {
        $email = 'user@gaerngschee.ch';
        if ($this->query(
            'SELECT id FROM users WHERE email = :email',
            ['email' => $email],
        )->fetch() !== false) {
            return;
        }

        $this->query(
            'INSERT INTO users (id, email, password, `group`) VALUES (:id, :email, :password, :group)',
            [
                'id' => '00000000-0000-4000-8000-000000000002',
                'email' => $email,
                'password' => password_hash('secret', PASSWORD_DEFAULT),
                'group' => 'user',
            ],
        );
    }
}
