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

    public static function isValidEmailDomain(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return false;
        }

        $domain = trim($parts[1]);
        if (empty($domain) || !str_contains($domain, '.')) {
            return false;
        }

        // Validate top level domain syntax (e.g. .com, .id, .org)
        $tld = strtolower(pathinfo($domain, PATHINFO_EXTENSION));
        if (strlen($tld) < 2 || preg_match('/[^a-z0-9]/i', $tld)) {
            return false;
        }

        // Check DNS MX or A records if function exists
        if (function_exists('checkdnsrr')) {
            if (!@checkdnsrr($domain, 'MX') && !@checkdnsrr($domain, 'A')) {
                return false;
            }
        }

        return true;
    }

    public static function register(string $name, string $email, string $password): array
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if (!$name || !$email || strlen($password) < 6) {
            return ['error' => 'Please provide valid name, email, and password (min 6 chars).'];
        }

        if (!self::isValidEmailDomain($email)) {
            return ['error' => 'Alamat email tidak valid atau domain email tidak terdaftar. Gunakan email yang benar (misal: user@gmail.com).'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['error' => 'Alamat email sudah terdaftar. Silakan login atau gunakan email lain.'];
        }

        $s = SettingsService::getAll();
        $requireVerification = !empty($s['enable_email_verification']);

        $userId = Database::generateUuid();
        $hashedPass = password_hash($password, PASSWORD_BCRYPT);
        $otpCode = sprintf('%06d', mt_rand(100000, 999999));

        if ($requireVerification) {
            $insert = $db->prepare("INSERT INTO users (id, name, email, password, role, plan, verification_code) VALUES (?, ?, ?, ?, 'user', 'free', ?)");
            $insert->execute([$userId, $name, $email, $hashedPass, $otpCode]);

            $mailRes = EmailService::sendVerificationOtp($email, $name, $otpCode);
            $emailSent = $mailRes['success'];

            return [
                'success' => true,
                'requires_verification' => true,
                'email' => $email,
                'message' => $emailSent 
                    ? "Registrasi berhasil! Kode OTP verifikasi 6 digit telah dikirim ke {$email}." 
                    : "Registrasi berhasil! Namun email verifikasi gagal terkirim ({$mailRes['error']}). Masukkan kode OTP: {$otpCode}"
            ];
        } else {
            $insert = $db->prepare("INSERT INTO users (id, name, email, password, role, plan, email_verified_at) VALUES (?, ?, ?, ?, 'user', 'free', NOW())");
            $insert->execute([$userId, $name, $email, $hashedPass]);

            self::initSession();
            $_SESSION['user_id'] = $userId;

            TelegramService::send("👤 <b>New User Registered</b>\n\n<b>Name:</b> " . htmlspecialchars($name) . "\n<b>Email:</b> " . htmlspecialchars($email));

            return ['success' => true, 'requires_verification' => false, 'message' => 'Registration successful'];
        }
    }

    public static function verifyEmail(string $email, string $otpCode): array
    {
        $email = strtolower(trim($email));
        $otpCode = trim($otpCode);

        if (!$email || !$otpCode) {
            return ['error' => 'Masukkan email dan kode OTP 6 digit.'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, verification_code, email_verified_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['error' => 'Akun tidak ditemukan.'];
        }

        if ($user['email_verified_at']) {
            self::initSession();
            $_SESSION['user_id'] = $user['id'];
            return ['success' => true, 'message' => 'Email sudah terverifikasi sebelumnya.'];
        }

        if (empty($user['verification_code']) || $user['verification_code'] !== $otpCode) {
            return ['error' => 'Kode OTP verifikasi salah. Silakan periksa kembali email Anda.'];
        }

        $update = $db->prepare("UPDATE users SET email_verified_at = NOW(), verification_code = NULL WHERE id = ?");
        $update->execute([$user['id']]);

        self::initSession();
        $_SESSION['user_id'] = $user['id'];

        TelegramService::send("👤 <b>New Verified User</b>\n\n<b>Name:</b> " . htmlspecialchars($user['name']) . "\n<b>Email:</b> " . htmlspecialchars($email));

        return ['success' => true, 'message' => 'Verifikasi email berhasil! Selamat datang di Convertify Pro.'];
    }

    public static function resendCode(string $email): array
    {
        $email = strtolower(trim($email));
        if (!$email) {
            return ['error' => 'Masukkan alamat email Anda.'];
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, email_verified_at FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['error' => 'Akun tidak ditemukan.'];
        }

        if ($user['email_verified_at']) {
            return ['error' => 'Email ini sudah terverifikasi. Silakan langsung login.'];
        }

        $otpCode = sprintf('%06d', mt_rand(100000, 999999));
        $update = $db->prepare("UPDATE users SET verification_code = ? WHERE id = ?");
        $update->execute([$otpCode, $user['id']]);

        $mailRes = EmailService::sendVerificationOtp($email, $user['name'], $otpCode);

        return [
            'success' => true,
            'message' => $mailRes['success'] 
                ? "Kode OTP verifikasi baru telah dikirim ke {$email}." 
                : "Kode OTP baru dibuat ({$otpCode}). Pengiriman email gagal: {$mailRes['error']}"
        ];
    }

    public static function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, name, password, email_verified_at, verification_code FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return ['error' => 'Invalid email address or password.'];
        }

        $s = SettingsService::getAll();
        if (!empty($s['enable_email_verification']) && empty($user['email_verified_at'])) {
            // Re-generate OTP if missing
            if (empty($user['verification_code'])) {
                $otpCode = sprintf('%06d', mt_rand(100000, 999999));
                $update = $db->prepare("UPDATE users SET verification_code = ? WHERE id = ?");
                $update->execute([$otpCode, $user['id']]);
                EmailService::sendVerificationOtp($email, $user['name'] ?: 'User', $otpCode);
            }
            return [
                'error' => 'Email Anda belum diverifikasi. Silakan masukkan kode OTP yang telah dikirim ke email Anda.',
                'requires_verification' => true,
                'email' => $email
            ];
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
