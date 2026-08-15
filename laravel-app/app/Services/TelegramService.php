<?php

namespace App\Services;

class TelegramService
{
    public static function send(string $text): bool
    {
        $res = self::sendDetailed($text);
        return $res['success'];
    }

    public static function sendDetailed(string $text): array
    {
        $s = SettingsService::getAll();
        if (empty($s['enable_telegram_notif'])) {
            return ['success' => false, 'error' => 'Notifikasi Telegram belum diaktifkan (Enable Telegram Notif: Off). Silakan ubah ke Enabled dan simpan.'];
        }
        if (empty($s['telegram_bot_token'])) {
            return ['success' => false, 'error' => 'Telegram Bot Token masih kosong. Isi token dari @BotFather di Telegram.'];
        }
        if (empty($s['telegram_chat_id'])) {
            return ['success' => false, 'error' => 'Telegram Admin Chat ID masih kosong. Isi Chat ID dari @userinfobot di Telegram.'];
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $res !== false) {
            return ['success' => true, 'error' => ''];
        }

        $detail = '';
        if ($res !== false) {
            $json = @json_decode($res, true);
            if (isset($json['description'])) {
                $detail = $json['description'];
            }
        }

        return ['success' => false, 'error' => "Telegram API Error (HTTP {$httpCode}): " . ($detail ?: 'Pastikan Bot Token & Chat ID benar, dan Anda sudah klik /start ke bot.')];
    }
}
