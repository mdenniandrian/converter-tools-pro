<?php

namespace App\Services;

class AuthService
{
    public static function initSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getUser(): ?array
    {
        self::initSession();
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, email, role, plan, plan_expires_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            unset($_SESSION['user_id']);
            return null;
        }

        // Check if plan expired
        if ($user['plan'] !== 'free' && $user['plan_expires_at']) {
            if (strtotime($user['plan_expires_at']) < time()) {
                $downgrade = $db->prepare("UPDATE users SET plan = 'free', plan_expires_at = NULL WHERE id = ?");
                $downgrade->execute([$user['id']]);
                $user['plan'] = 'free';
                $user['plan_expires_at'] = null;
            }
        }

        return $user;
    }

    public static function register(string $name, string $email, string $password): array
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if (!$name || !$email || strlen($password) < 6) {
            return ['error' => 'Please provide valid name, email, and password (min 6 chars).'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['error' => 'Email address is already registered.'];
        }

        $userId = Database::generateUuid();
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        $insert = $db->prepare("INSERT INTO users (id, name, email, password, role, plan) VALUES (?, ?, ?, ?, 'user', 'free')");
        $insert->execute([$userId, $name, $email, $hashedPass]);

        self::initSession();
        $_SESSION['user_id'] = $userId;

        TelegramService::send("👤 <b>New User Registered</b>\n\n<b>Name:</b> " . htmlspecialchars($name) . "\n<b>Email:</b> " . htmlspecialchars($email));

        return ['success' => true, 'message' => 'Registration successful'];
    }

    public static function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['error' => 'Invalid email address or password.'];
        }

        self::initSession();
        $_SESSION['user_id'] = $user['id'];

        return ['success' => true, 'message' => 'Login successful'];
    }

    public static function logout(): void
    {
        self::initSession();
        unset($_SESSION['user_id']);
        session_destroy();
    }
}
