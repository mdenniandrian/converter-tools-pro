<?php

namespace App\Services;

class MidtransService
{
    public static function createCheckoutToken(array $user, string $planType): array
    {
        $settings = SettingsService::getAll();
        $planType = strtolower($planType);
        $amount = SettingsService::getPlanPrice($planType);
        $orderId = 'TRX-' . time() . '-' . rand(1000, 9999);

        $serverKey = $settings['midtrans_server_key'];
        $isProduction = $settings['midtrans_is_production'];
        $snapUrl = $isProduction ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount
            ],
            'customer_details' => [
                'first_name' => $user['name'],
                'email' => $user['email']
            ],
            'item_details' => [[
                'id' => $planType,
                'price' => $amount,
                'quantity' => 1,
                'name' => 'Convertify Pro ' . strtoupper($planType) . ' Subscription'
            ]],
            'custom_field1' => $user['id'],
            'custom_field2' => $planType
        ];

        $ch = curl_init($snapUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':')
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $resData = json_decode($response, true);
        if (isset($resData['token'])) {
            return [
                'success' => true,
                'token' => $resData['token'],
                'redirect_url' => $resData['redirect_url'],
                'order_id' => $orderId
            ];
        }

        return ['error' => $resData['error_messages'][0] ?? 'Failed to initialize Midtrans transaction. Check Midtrans Server Key in Backoffice.'];
    }

    public static function handleWebhook(array $data): void
    {
        if (isset($data['transaction_status'])) {
            $status = $data['transaction_status'];
            $userId = $data['custom_field1'] ?? null;
            $planType = $data['custom_field2'] ?? 'pro';

            if (($status === 'settlement' || $status === 'capture') && $userId) {
                $days = ($planType === 'enterprise') ? 36500 : 30;
                $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));

                $db = Database::getConnection();
                $stmt = $db->prepare("UPDATE users SET plan = ?, plan_expires_at = ? WHERE id = ?");
                $stmt->execute([$planType, $expiresAt, $userId]);

                // Fetch user for notification
                $uStmt = $db->prepare("SELECT email FROM users WHERE id = ?");
                $uStmt->execute([$userId]);
                $user = $uStmt->fetch();
                $email = $user['email'] ?? 'User';

                TelegramService::send("💳 <b>Midtrans Payment Success!</b>\n\n<b>User Email:</b> {$email}\n<b>Upgraded Plan:</b> " . strtoupper($planType) . "\n<b>Status:</b> SETTLED");
            }
        }
    }
}
