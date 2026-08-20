<?php

namespace App\Services;

class LdapService
{
    /**
     * Test LDAP connection with provided or saved settings.
     */
    public static function testConnection(array $configOverride = []): array
    {
        if (!function_exists('ldap_connect')) {
            return [
                'success' => false,
                'error' => 'Ekstensi PHP LDAP (ext-ldap) belum aktif di server ini. Aktifkan modul LDAP di PHP untuk menggunakan fitur ini.'
            ];
        }

        $s = SettingsService::getAll();
        $host = trim($configOverride['ldap_host'] ?? $s['ldap_host'] ?? '');
        $port = (int)($configOverride['ldap_port'] ?? $s['ldap_port'] ?? 389);
        $useTls = isset($configOverride['ldap_use_tls']) ? ((string)$configOverride['ldap_use_tls'] === '1') : (($s['ldap_use_tls'] ?? '0') === '1');
        $bindDn = trim($configOverride['ldap_bind_dn'] ?? $s['ldap_bind_dn'] ?? '');
        $bindPass = trim($configOverride['ldap_bind_password'] ?? $s['ldap_bind_password'] ?? '');

        if (empty($host)) {
            return ['success' => false, 'error' => 'Host Server LDAP belum diisi.'];
        }

        $conn = @ldap_connect($host, $port);
        if (!$conn) {
            return ['success' => false, 'error' => "Gagal terhubung ke Server LDAP {$host}:{$port}."];
        }

        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        @ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        @ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 5);

        if ($useTls) {
            if (!@ldap_start_tls($conn)) {
                @ldap_close($conn);
                return ['success' => false, 'error' => "Gagal memulai enkripsi STARTTLS dengan server LDAP."];
            }
        }

        if (!empty($bindDn)) {
            $bind = @ldap_bind($conn, $bindDn, $bindPass);
            if (!$bind) {
                $err = @ldap_error($conn);
                @ldap_close($conn);
                return ['success' => false, 'error' => "Autentikasi LDAP Bind DN gagal: {$err}"];
            }
        } else {
            // Anonymous bind test
            $bind = @ldap_bind($conn);
            if (!$bind) {
                $err = @ldap_error($conn);
                @ldap_close($conn);
                return ['success' => false, 'error' => "Koneksi Anonymous LDAP gagal: {$err}"];
            }
        }

        @ldap_close($conn);
        return [
            'success' => true,
            'message' => "Koneksi LDAP ke {$host}:{$port} berhasil terhubung!"
        ];
    }

    /**
     * Attempt LDAP authentication for a user username/email and password.
     */
    public static function authenticate(string $userIdentifier, string $password): array
    {
        $s = SettingsService::getAll();
        if (($s['enable_ldap'] ?? '0') !== '1') {
            return ['success' => false, 'error' => 'LDAP Authentication disabled'];
        }

        if (!function_exists('ldap_connect')) {
            return ['success' => false, 'error' => 'PHP LDAP Extension not available'];
        }

        $host = trim($s['ldap_host']);
        $port = (int)($s['ldap_port'] ?: 389);
        $baseDn = trim($s['ldap_base_dn']);
        $bindDn = trim($s['ldap_bind_dn']);
        $bindPass = trim($s['ldap_bind_password']);
        $userAttr = trim($s['ldap_user_attribute'] ?: 'uid'); // 'uid', 'sAMAccountName', or 'mail'
        $useTls = ($s['ldap_use_tls'] ?? '0') === '1';

        if (empty($host) || empty($baseDn)) {
            return ['success' => false, 'error' => 'Pengaturan LDAP belum lengkap'];
        }

        $conn = @ldap_connect($host, $port);
        if (!$conn) {
            return ['success' => false, 'error' => 'Tidak dapat terhubung ke server LDAP'];
        }

        @ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        @ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
        @ldap_set_option($conn, LDAP_OPT_NETWORK_TIMEOUT, 5);

        if ($useTls && !@ldap_start_tls($conn)) {
            @ldap_close($conn);
            return ['success' => false, 'error' => 'Gagal STARTTLS LDAP'];
        }

        // 1. Bind with Admin / System account if configured, or attempt direct user bind
        if (!empty($bindDn)) {
            if (!@ldap_bind($conn, $bindDn, $bindPass)) {
                @ldap_close($conn);
                return ['success' => false, 'error' => 'LDAP Admin bind gagal'];
            }
        }

        // 2. Search user entry in Base DN
        $sanitizedUser = preg_replace('/[^a-zA-Z0-9\.\-\_\@]/', '', $userIdentifier);
        $filter = "({$userAttr}={$sanitizedUser})";
        $search = @ldap_search($conn, $baseDn, $filter, ['dn', 'cn', 'mail', 'displayName']);

        if (!$search) {
            @ldap_close($conn);
            return ['success' => false, 'error' => 'Pencarian user di LDAP gagal'];
        }

        $entries = @ldap_get_entries($conn, $search);
        if (!$entries || $entries['count'] === 0) {
            @ldap_close($conn);
            return ['success' => false, 'error' => 'User tidak ditemukan di direktori LDAP'];
        }

        $userDn = $entries[0]['dn'];
        $userName = $entries[0]['cn'][0] ?? $entries[0]['displayname'][0] ?? $sanitizedUser;
        $userEmail = $entries[0]['mail'][0] ?? ($sanitizedUser . '@ldap.local');

        // 3. Bind with user DN and password to verify password
        $userBind = @ldap_bind($conn, $userDn, $password);
        @ldap_close($conn);

        if (!$userBind) {
            return ['success' => false, 'error' => 'Password LDAP tidak valid'];
        }

        return [
            'success' => true,
            'name' => $userName,
            'email' => strtolower($userEmail),
            'dn' => $userDn
        ];
    }
}
