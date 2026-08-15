<?php

namespace App\Services;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $host = getenv('DB_HOST') ?: 'postgres';
            $port = getenv('DB_PORT') ?: '5432';
            $dbName = getenv('DB_DATABASE') ?: 'converter_db';
            $user = getenv('DB_USERNAME') ?: 'converter_user';
            $pass = getenv('DB_PASSWORD') ?: 'secret123';

            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
            try {
                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                self::migrateSchema(self::$pdo);
            } catch (PDOException $e) {
                header('Content-Type: application/json');
                http_response_code(500);
                echo json_encode(['error' => 'Database connection failure: ' . $e->getMessage()]);
                exit;
            }
        }
        return self::$pdo;
    }

    private static function migrateSchema(PDO $pdo): void
    {
        // 0. Essential column migrations (Executed FIRST to guarantee table columns exist)
        $migrations = [
            "ALTER TABLE jobs ADD COLUMN IF NOT EXISTS action_type VARCHAR(50) DEFAULT 'doc_convert';",
            "ALTER TABLE jobs ADD COLUMN IF NOT EXISTS output_filename VARCHAR(255);",
            "ALTER TABLE jobs ADD COLUMN IF NOT EXISTS original_filename VARCHAR(255);",
            "ALTER TABLE jobs ADD COLUMN IF NOT EXISTS downloaded_at TIMESTAMP NULL;",
            "ALTER TABLE jobs ALTER COLUMN original_filename DROP NOT NULL;",
            "ALTER TABLE users ALTER COLUMN id TYPE VARCHAR(255);",
            "ALTER TABLE jobs ALTER COLUMN id TYPE VARCHAR(255);",
            "ALTER TABLE jobs ALTER COLUMN user_id TYPE VARCHAR(255) USING user_id::VARCHAR;",
            "ALTER TABLE users ADD COLUMN IF NOT EXISTS email_verified_at TIMESTAMP NULL;",
            "ALTER TABLE users ADD COLUMN IF NOT EXISTS verification_code VARCHAR(64) NULL;",
            "ALTER TABLE activation_codes ALTER COLUMN id TYPE VARCHAR(255);"
        ];

        foreach ($migrations as $sql) {
            try {
                $pdo->exec($sql);
            } catch (\Throwable $t) {
                try {
                    $pdo->exec("ROLLBACK;");
                } catch (\Throwable $t2) {}
            }
        }

        // Users Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id VARCHAR(255) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role VARCHAR(20) DEFAULT 'user',
                plan VARCHAR(20) DEFAULT 'free',
                plan_expires_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Activation Codes Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS activation_codes (
                id VARCHAR(255) PRIMARY KEY,
                code VARCHAR(50) UNIQUE NOT NULL,
                plan_type VARCHAR(20) NOT NULL,
                duration_days INT DEFAULT 30,
                max_uses INT DEFAULT 1,
                used_count INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Jobs Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS jobs (
                id VARCHAR(255) PRIMARY KEY,
                user_id VARCHAR(255) NULL,
                action_type VARCHAR(50) DEFAULT 'doc_convert',
                original_filename VARCHAR(255) NULL,
                output_filename VARCHAR(255) NULL,
                target_format VARCHAR(20) NOT NULL,
                input_s3_key VARCHAR(500) NOT NULL,
                output_s3_key VARCHAR(500) NULL,
                status VARCHAR(50) DEFAULT 'pending',
                error_message TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // System Settings Table
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS system_settings (
                key VARCHAR(100) PRIMARY KEY,
                value TEXT NOT NULL,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Default System Settings Migration (with Pricing & Discounts)
        $defaults = [
            'enable_midtrans' => '1',
            'enable_whatsapp' => '1',
            'enable_sandbox_sim' => '1',
            'pro_price' => '49000',
            'pro_discount_percent' => '20',
            'enterprise_price' => '149000',
            'enterprise_discount_percent' => '25',
            'promo_code' => 'DISCOUNT20',
            'promo_discount_percent' => '10',
            'midtrans_server_key' => getenv('MIDTRANS_SERVER_KEY') ?: '',
            'midtrans_client_key' => getenv('MIDTRANS_CLIENT_KEY') ?: '',
            'midtrans_is_production' => getenv('MIDTRANS_IS_PRODUCTION') ?: '1',
            'wa_admin_number' => getenv('WA_ADMIN_NUMBER') ?: '6282113237920',
            'telegram_bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
            'telegram_chat_id' => getenv('TELEGRAM_CHAT_ID') ?: '',
            'enable_telegram_notif' => '1'
        ];

        foreach ($defaults as $k => $v) {
            $stmt = $pdo->prepare("INSERT INTO system_settings (key, value) VALUES (?, ?) ON CONFLICT (key) DO NOTHING");
            $stmt->execute([$k, $v]);
        }

        $adminCheck = $pdo->prepare("SELECT id FROM users WHERE email = 'admin@convertify.local'");
        $adminCheck->execute();
        if (!$adminCheck->fetch()) {
            $adminId = '00000000-0000-0000-0000-000000000001';
            $hashedPass = password_hash('admin123', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password, role, plan) VALUES (?, 'Super Admin', 'admin@convertify.local', ?, 'admin', 'enterprise')");
            $stmt->execute([$adminId, $hashedPass]);
        }
    }

    public static function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
