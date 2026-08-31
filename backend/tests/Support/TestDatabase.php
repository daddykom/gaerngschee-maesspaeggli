<?php

declare(strict_types=1);

namespace Tests\Support;

use PDO;

final class TestDatabase
{
    public static function create(): PDO
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
        $pdo->exec(
            'CREATE TABLE frontend_config (
                id TEXT PRIMARY KEY,
                variable_name TEXT UNIQUE NOT NULL,
                value TEXT,
                description TEXT,
                access_group TEXT NOT NULL,
                update_group TEXT NOT NULL,
                label TEXT NOT NULL,
                created_at TEXT,
                updated_at TEXT
            )',
        );

        return $pdo;
    }
}
