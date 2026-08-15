<?php

namespace App\Http\Controllers;

use App\Services\Database;
use App\Services\AuthService;
use App\Services\SettingsService;
use App\Services\TelegramService;

class AdminController
{
    public static function getData(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized admin access']);
            exit;
        }

        $db = Database::getConnection();
        $codes = $db->query("SELECT * FROM activation_codes ORDER BY created_at DESC LIMIT 100")->fetchAll();
        $users = $db->query("SELECT id, name, email, role, plan, plan_expires_at, created_at FROM users ORDER BY created_at DESC LIMIT 100")->fetchAll();
        $jobCount = $db->query("SELECT COUNT(*) as cnt FROM jobs")->fetch()['cnt'];

        echo json_encode([
            'stats' => ['total_jobs' => $jobCount, 'total_users' => count($users), 'total_codes' => count($codes)],
            'codes' => $codes,
            'users' => $users,
            'settings' => SettingsService::getAll()
        ]);
        exit;
    }

    public static function generateCodes(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized admin access']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $planType = strtolower($input['plan_type'] ?? 'pro');
        $durationDays = (int) ($input['duration_days'] ?? 30);
        $quantity = min(50, max(1, (int) ($input['quantity'] ?? 1)));

        $db = Database::getConnection();
        $generated = [];

        for ($i = 0; $i < $quantity; $i++) {
            $codeId = Database::generateUuid();
            $codeStr = self::generateCodeStr(strtoupper($planType));

            $stmt = $db->prepare("INSERT INTO activation_codes (id, code, plan_type, duration_days, is_active) VALUES (?, ?, ?, ?, TRUE)");
            $stmt->execute([$codeId, $codeStr, $planType, $durationDays]);

            $generated[] = $codeStr;
        }

        echo json_encode([
            'success' => true,
            'count' => count($generated),
            'codes' => $generated,
            'message' => count($generated) . " activation code(s) generated successfully"
        ]);
        exit;
    }

    public static function redeemCode(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user) {
            echo json_encode(['error' => 'Please login to your account before redeeming a code.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $codeStr = strtoupper(trim($input['code'] ?? ''));

        if (!$codeStr) {
            echo json_encode(['error' => 'Please enter a valid activation code.']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM activation_codes WHERE code = ? AND is_active = TRUE AND used_count < max_uses");
        $stmt->execute([$codeStr]);
        $code = $stmt->fetch();

        if (!$code) {
            echo json_encode(['error' => 'Invalid, expired, or already used activation code.']);
            exit;
        }

        $days = (int) $code['duration_days'];
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        // Update User Plan
        $upUser = $db->prepare("UPDATE users SET plan = ?, plan_expires_at = ? WHERE id = ?");
        $upUser->execute([$code['plan_type'], $expiresAt, $user['id']]);

        // Mark Code Used
        $upCode = $db->prepare("UPDATE activation_codes SET used_count = used_count + 1, is_active = FALSE WHERE id = ?");
        $upCode->execute([$code['id']]);

        TelegramService::send("🔑 <b>Serial Code Redeemed!</b>\n\n<b>User:</b> {$user['name']} ({$user['email']})\n<b>Plan Activated:</b> " . strtoupper($code['plan_type']) . " ({$days} Days)\n<b>Serial Code:</b> {$codeStr}");

        echo json_encode([
            'success' => true,
            'message' => "Congratulations! Your account has been upgraded to " . strtoupper($code['plan_type']) . " Plan for {$days} days!"
        ]);
        exit;
    }

    public static function updateUserPlan(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized admin access']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $targetUserId = $input['user_id'] ?? '';
        $newPlan = strtolower($input['plan'] ?? 'free');
        $days = (int) ($input['duration_days'] ?? 30);

        $expiresAt = ($newPlan === 'free') ? null : date('Y-m-d H:i:s', strtotime("+{$days} days"));

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET plan = ?, plan_expires_at = ? WHERE id = ?");
        $stmt->execute([$newPlan, $expiresAt, $targetUserId]);

        echo json_encode(['success' => true, 'message' => "User plan updated to " . strtoupper($newPlan)]);
        exit;
    }

    public static function updateUserRole(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized admin access']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $targetUserId = $input['user_id'] ?? '';
        $newRole = strtolower($input['role'] ?? 'user');

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$newRole, $targetUserId]);

        echo json_encode(['success' => true, 'message' => "User role updated to " . strtoupper($newRole)]);
        exit;
    }

    public static function deleteUser(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized admin access']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $targetUserId = $input['user_id'] ?? '';

        if ($targetUserId === $user['id']) {
            echo json_encode(['error' => 'You cannot delete your own admin account.']);
            exit;
        }

        $db = Database::getConnection();
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$targetUserId]);

        echo json_encode(['success' => true, 'message' => "User deleted successfully"]);
        exit;
    }

    public static function updateSettings(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized admin access']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        SettingsService::update($input);

        echo json_encode(['success' => true, 'message' => 'System & Integration settings updated successfully', 'settings' => SettingsService::getAll()]);
        exit;
    }

    public static function testTelegram(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user || $user['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized admin access']);
            exit;
        }

        $msg = "🤖 <b>Convertify Pro Telegram Bot Test</b>\n\nHello Admin! Your Telegram Bot integration is working perfectly! 🚀";
        $res = TelegramService::send($msg);

        if ($res) {
            echo json_encode(['success' => true, 'message' => 'Telegram test notification sent successfully!']);
        } else {
            echo json_encode(['error' => 'Failed to send Telegram message. Please verify Bot Token & Chat ID in Backoffice.']);
        }
        exit;
    }

    public static function simulatePayment(): void
    {
        header('Content-Type: application/json');
        $user = AuthService::getUser();
        if (!$user) {
            echo json_encode(['error' => 'Please login to your account before testing simulation.']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $planType = strtolower($input['plan_type'] ?? 'pro');
        $days = ($planType === 'enterprise') ? 36500 : 30;
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));

        $db = Database::getConnection();
        $stmt = $db->prepare("UPDATE users SET plan = ?, plan_expires_at = ? WHERE id = ?");
        $stmt->execute([$planType, $expiresAt, $user['id']]);

        TelegramService::send("🧪 <b>Sandbox Payment Simulation Executed</b>\n\n<b>User:</b> {$user['email']}\n<b>Plan Upgraded:</b> " . strtoupper($planType));

        echo json_encode([
            'success' => true,
            'message' => "🧪 Sandbox Payment Simulation Successful! Your account has been upgraded to " . strtoupper($planType) . " Plan!"
        ]);
        exit;
    }

    private static function generateCodeStr(string $prefix = 'PRO'): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $part1 = '';
        $part2 = '';
        for ($i = 0; $i < 4; $i++) {
            $part1 .= $chars[rand(0, strlen($chars) - 1)];
            $part2 .= $chars[rand(0, strlen($chars) - 1)];
        }
        return "{$prefix}-{$part1}-{$part2}";
    }
}
