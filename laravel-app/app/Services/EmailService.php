<?php

namespace App\Services;

class EmailService
{
    public static function sendVerificationOtp(string $toEmail, string $userName, string $otpCode): array
    {
        $subject = "Verify Your Email - Convertify Pro (Code: {$otpCode})";
        
        $body = "
        <div style=\"font-family: 'Segoe UI', Arial, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 2rem; border-radius: 12px; max-width: 600px; margin: 0 auto;\">
            <div style=\"text-align: center; margin-bottom: 2rem;\">
                <h1 style=\"color: #06b6d4; margin: 0; font-size: 1.8rem;\">⚡ Convertify Pro</h1>
                <p style=\"color: #94a3b8; font-size: 0.9rem; margin-top: 0.3rem;\">Fast & Secure Document Converter</p>
            </div>
            
            <div style=\"background-color: #1e293b; padding: 1.8rem; border-radius: 10px; border: 1px solid #334155;\">
                <h2 style=\"color: #f1f5f9; margin-top: 0;\">Halo, " . htmlspecialchars($userName) . "! 👋</h2>
                <p style=\"color: #cbd5e1; font-size: 0.95rem; line-height: 1.5;\">
                    Terima kasih telah mendaftar di <strong>Convertify Pro</strong>. Untuk mengaktifkan akun Anda dan mulai mengonversi dokumen, silakan gunakan kode verifikasi OTP 6-digit berikut:
                </p>
                
                <div style=\"text-align: center; margin: 2rem 0;\">
                    <span style=\"display: inline-block; background: linear-gradient(135deg, #06b6d4, #3b82f6); color: #ffffff; font-size: 2.2rem; font-weight: 800; letter-spacing: 8px; padding: 0.8rem 2rem; border-radius: 12px; box-shadow: 0 4px 14px rgba(6, 182, 212, 0.4);\">
                        {$otpCode}
                    </span>
                </div>
                
                <p style=\"color: #94a3b8; font-size: 0.85rem; text-align: center;\">
                    Kode ini berlaku selama 30 menit. Jangan berikan kode ini kepada siapapun demi keamanan akun Anda.
                </p>
            </div>
            
            <div style=\"text-align: center; margin-top: 2rem; color: #64748b; font-size: 0.8rem;\">
                <p>&copy; " . date('Y') . " Convertify Pro. All rights reserved.</p>
            </div>
        </div>
        ";

        return self::sendMail($toEmail, $subject, $body);
    }

    public static function sendMail(string $toEmail, string $subject, string $htmlContent): array
    {
        $s = SettingsService::getAll();
        $fromAddress = $s['smtp_from_address'] ?: 'no-reply@converter.bangden.my.id';
        $fromName = $s['smtp_from_name'] ?: 'Convertify Pro';

        $smtpHost = trim($s['smtp_host']);
        $smtpPort = (int)$s['smtp_port'];
        $smtpUser = trim($s['smtp_username']);
        $smtpPass = trim($s['smtp_password']);

        // 1. Try Direct SMTP Socket connection if SMTP details provided
        if (!empty($smtpHost) && !empty($smtpUser) && !empty($smtpPass)) {
            $smtpResult = self::sendViaSmtp($smtpHost, $smtpPort, $smtpUser, $smtpPass, $fromAddress, $fromName, $toEmail, $subject, $htmlContent);
            if ($smtpResult['success']) {
                return $smtpResult;
            }
        }

        // 2. Fallback to PHP native mail()
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . sprintf('%s <%s>', $fromName, $fromAddress),
            'Reply-To: ' . $fromAddress,
            'X-Mailer: PHP/' . phpversion()
        ];

        $sent = @mail($toEmail, $subject, $htmlContent, implode("\r\n", $headers));
        if ($sent) {
            return ['success' => true, 'message' => 'Email sent via mail()'];
        }

        return ['success' => false, 'error' => 'Gagal mengirim email verifikasi. Konfigurasikan SMTP di Backoffice Admin.'];
    }

    private static function sendViaSmtp(string $host, int $port, string $user, string $pass, string $fromAddress, string $fromName, string $toEmail, string $subject, string $body): array
    {
        $timeout = 10;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $transport = ($port === 465) ? "ssl://{$host}" : "tcp://{$host}";
        $socket = @stream_socket_client("{$transport}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);

        if (!$socket) {
            return ['success' => false, 'error' => "SMTP Connection failed: {$errstr} ({$errno})"];
        }

        $getResponse = function() use ($socket) {
            $res = '';
            while ($str = fgets($socket, 515)) {
                $res .= $str;
                if (substr($str, 3, 1) == ' ') break;
            }
            return $res;
        };

        $sendCommand = function($cmd) use ($socket, $getResponse) {
            fputs($socket, $cmd . "\r\n");
            return $getResponse();
        };

        $getResponse(); // Initial greeting
        $sendCommand("EHLO " . gethostname());

        if ($port === 587) {
            $sendCommand("STARTTLS");
            @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT);
            $sendCommand("EHLO " . gethostname());
        }

        $authRes = $sendCommand("AUTH LOGIN");
        if (strpos($authRes, '334') === 0) {
            $sendCommand(base64_encode($user));
            $passRes = $sendCommand(base64_encode($pass));
            if (strpos($passRes, '235') !== 0) {
                fclose($socket);
                return ['success' => false, 'error' => "SMTP Auth failed: {$passRes}"];
            }
        }

        $sendCommand("MAIL FROM: <{$fromAddress}>");
        $sendCommand("RCPT TO: <{$toEmail}>");
        $sendCommand("DATA");

        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=UTF-8",
            "From: {$fromName} <{$fromAddress}>",
            "To: <{$toEmail}>",
            "Subject: {$subject}",
            "Date: " . date("r")
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
        $dataRes = $sendCommand($message);

        $sendCommand("QUIT");
        fclose($socket);

        if (strpos($dataRes, '250') === 0) {
            return ['success' => true, 'message' => 'Email sent via SMTP'];
        }

        return ['success' => false, 'error' => "SMTP Data delivery failed: {$dataRes}"];
    }
}
