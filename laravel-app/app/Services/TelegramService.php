<?php

namespace App\Services;

class TelegramService
{
    public static function send(string $text): bool
    {
        $s = SettingsService::getAll();
        if (!$s['enable_telegram_notif'] || empty($s['telegram_bot_token']) || empty($s['telegram_chat_id'])) {
            return false;
        }

        $url = "https://api.telegram.org/bot" . trim($s['telegram_bot_token']) . "/sendMessage";
        $payload = [
            'chat_id' => trim($s['telegram_chat_id']),
            'text' => $text,
            'parse_mode' => 'HTML'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && $res !== false;
    }
}
