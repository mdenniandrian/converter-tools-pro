<?php

namespace App\Services;

class SettingsService
{
    public static function getAll(): array
    {
        $db = Database::getConnection();
        $stmt = $db->query("SELECT key, value FROM system_settings");
        $raw = [];
        while ($row = $stmt->fetch()) {
            $raw[$row['key']] = $row['value'];
        }

        $proPrice = (int)($raw['pro_price'] ?? 49000);
        $proDiscount = (int)($raw['pro_discount_percent'] ?? 20);
        $enterprisePrice = (int)($raw['enterprise_price'] ?? 149000);
        $enterpriseDiscount = (int)($raw['enterprise_discount_percent'] ?? 25);

        // Effective discounted prices
        $proFinalPrice = round($proPrice * (1 - ($proDiscount / 100)));
        $enterpriseFinalPrice = round($enterprisePrice * (1 - ($enterpriseDiscount / 100)));

        return [
            'enable_midtrans' => ($raw['enable_midtrans'] ?? '1') === '1',
            'enable_whatsapp' => ($raw['enable_whatsapp'] ?? '1') === '1',
            'enable_sandbox_sim' => ($raw['enable_sandbox_sim'] ?? '1') === '1',
            
            // Pricing & Discounts
            'pro_price' => $proPrice,
            'pro_discount_percent' => $proDiscount,
            'pro_final_price' => $proFinalPrice,
            
            'enterprise_price' => $enterprisePrice,
            'enterprise_discount_percent' => $enterpriseDiscount,
            'enterprise_final_price' => $enterpriseFinalPrice,
            
            'promo_code' => strtoupper($raw['promo_code'] ?? 'DISCOUNT20'),
            'promo_discount_percent' => (int)($raw['promo_discount_percent'] ?? 10),

            // Payment Keys & Config
            'midtrans_server_key' => $raw['midtrans_server_key'] ?? (getenv('MIDTRANS_SERVER_KEY') ?: ''),
            'midtrans_client_key' => $raw['midtrans_client_key'] ?? (getenv('MIDTRANS_CLIENT_KEY') ?: ''),
            'midtrans_is_production' => ($raw['midtrans_is_production'] ?? (getenv('MIDTRANS_IS_PRODUCTION') ?: '1')) === '1',
            'wa_admin_number' => $raw['wa_admin_number'] ?? (getenv('WA_ADMIN_NUMBER') ?: '6282113237920'),
            
            // Telegram Config
            'telegram_bot_token' => $raw['telegram_bot_token'] ?? (getenv('TELEGRAM_BOT_TOKEN') ?: ''),
            'telegram_chat_id' => $raw['telegram_chat_id'] ?? (getenv('TELEGRAM_CHAT_ID') ?: ''),
            'enable_telegram_notif' => ($raw['enable_telegram_notif'] ?? '1') === '1',

            // SMTP & Email Verification Config
            'enable_email_verification' => ($raw['enable_email_verification'] ?? '0') === '1',
            'smtp_host' => $raw['smtp_host'] ?? (getenv('SMTP_HOST') ?: ''),
            'smtp_port' => (int)($raw['smtp_port'] ?? (getenv('SMTP_PORT') ?: 587)),
            'smtp_username' => $raw['smtp_username'] ?? (getenv('SMTP_USERNAME') ?: ''),
            'smtp_password' => $raw['smtp_password'] ?? (getenv('SMTP_PASSWORD') ?: ''),
            'smtp_from_address' => $raw['smtp_from_address'] ?? (getenv('SMTP_FROM_ADDRESS') ?: 'no-reply@converter.bangden.my.id'),
            'smtp_from_name' => $raw['smtp_from_name'] ?? (getenv('SMTP_FROM_NAME') ?: 'Convertify Pro'),

            // LDAP Config
            'enable_ldap' => ($raw['enable_ldap'] ?? '0') === '1',
            'ldap_host' => $raw['ldap_host'] ?? (getenv('LDAP_HOST') ?: ''),
            'ldap_port' => (int)($raw['ldap_port'] ?? (getenv('LDAP_PORT') ?: 389)),
            'ldap_base_dn' => $raw['ldap_base_dn'] ?? (getenv('LDAP_BASE_DN') ?: ''),
            'ldap_bind_dn' => $raw['ldap_bind_dn'] ?? (getenv('LDAP_BIND_DN') ?: ''),
            'ldap_bind_password' => $raw['ldap_bind_password'] ?? (getenv('LDAP_BIND_PASSWORD') ?: ''),
            'ldap_user_attribute' => $raw['ldap_user_attribute'] ?? (getenv('LDAP_USER_ATTRIBUTE') ?: 'uid'),
            'ldap_use_tls' => ($raw['ldap_use_tls'] ?? '0') === '1',
        ];
    }

    public static function update(array $settings): void
    {
        $db = Database::getConnection();
        $allowedKeys = [
            'enable_midtrans', 'enable_whatsapp', 'enable_sandbox_sim',
            'pro_price', 'pro_discount_percent', 'enterprise_price', 'enterprise_discount_percent',
            'promo_code', 'promo_discount_percent',
            'midtrans_server_key', 'midtrans_client_key', 'midtrans_is_production',
            'wa_admin_number', 'telegram_bot_token', 'telegram_chat_id', 'enable_telegram_notif',
            'enable_email_verification', 'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_from_address', 'smtp_from_name',
            'enable_ldap', 'ldap_host', 'ldap_port', 'ldap_base_dn', 'ldap_bind_dn', 'ldap_bind_password',
            'ldap_user_attribute', 'ldap_use_tls'
        ];

        foreach ($allowedKeys as $key) {
            if (array_key_exists($key, $settings)) {
                $val = is_bool($settings[$key]) ? ($settings[$key] ? '1' : '0') : (string)$settings[$key];
                $stmt = $db->prepare("INSERT INTO system_settings (key, value) VALUES (?, ?) ON CONFLICT (key) DO UPDATE SET value = EXCLUDED.value, updated_at = CURRENT_TIMESTAMP");
                $stmt->execute([$key, $val]);
            }
        }
    }

    public static function getPlanPrice(string $planType): int
    {
        $all = self::getAll();
        $plan = strtolower($planType);
        if ($plan === 'enterprise') {
            return (int)$all['enterprise_final_price'];
        }
        return (int)$all['pro_final_price'];
    }
}
