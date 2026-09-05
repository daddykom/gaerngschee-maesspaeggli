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
        $pdo->exec(
            'CREATE TABLE orders (
                id TEXT PRIMARY KEY,
                user_id TEXT NOT NULL,
                year INTEGER NOT NULL,
                status TEXT NOT NULL,
                adults_count INTEGER NOT NULL DEFAULT 0,
                children_count INTEGER NOT NULL DEFAULT 0,
                 confirmation_email_sent_at TEXT NULL,
                 fairgate_reminder_email_sent_at TEXT NULL,
                 delivery_token TEXT NULL UNIQUE,
                 created_at TEXT,
                updated_at TEXT,
                UNIQUE (user_id, year)
            )',
        );
        $pdo->exec(
            'CREATE TABLE order_items (
                id TEXT PRIMARY KEY,
                order_id TEXT NOT NULL,
                person_type TEXT NOT NULL,
                category TEXT NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 0,
                created_at TEXT,
                updated_at TEXT,
                UNIQUE (order_id, person_type, category)
            )',
        );
        $pdo->exec(
            'CREATE TABLE order_email_queue (
                id TEXT PRIMARY KEY,
                order_id TEXT NOT NULL,
                email_type TEXT NOT NULL,
                recipient TEXT NOT NULL,
                subject TEXT NOT NULL,
                html_body TEXT NOT NULL,
                text_body TEXT NOT NULL,
                last_error TEXT NULL,
                created_at TEXT,
                updated_at TEXT,
                UNIQUE (order_id, email_type)
            )',
        );

        return $pdo;
    }
}
