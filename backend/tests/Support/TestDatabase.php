<?php

declare(strict_types=1);

namespace Tests\Support;

use PDO;

final class TestDatabase
{
    public static function create(bool $withAccessKeys = false): PDO
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->exec(
            'CREATE TABLE users (
                id TEXT PRIMARY KEY,
                email TEXT UNIQUE NOT NULL,
                password TEXT NOT NULL,
                "group" TEXT NOT NULL,
                required_password_reset INTEGER NOT NULL DEFAULT 0,
                created_at TEXT,
                updated_at TEXT
            )',
        );

        if ($withAccessKeys) {
            $pdo->exec(
                'CREATE TABLE user_access_keys (
                    id TEXT PRIMARY KEY,
                    user_id TEXT NOT NULL,
                    purpose TEXT NOT NULL,
                    key_hash TEXT NOT NULL UNIQUE,
                    expires_at TEXT NOT NULL,
                    used_at TEXT NULL,
                    created_at TEXT,
                    updated_at TEXT
                )',
            );
        }

        $pdo->exec(
            'CREATE TABLE registration_tokens (
                id TEXT PRIMARY KEY,
                email TEXT NOT NULL,
                token_hash TEXT NOT NULL UNIQUE,
                expires_at TEXT NOT NULL,
                used_at TEXT NULL,
                created_at TEXT,
                updated_at TEXT
            )',
        );

        return $pdo;
    }
}
