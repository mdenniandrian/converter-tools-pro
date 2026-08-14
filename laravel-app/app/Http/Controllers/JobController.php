<?php

namespace App\Http\Controllers;

use App\Services\Database;
use App\Services\AuthService;
use App\Services\TelegramService;
use Exception;

class JobController
{
    public static function handleUpload(): void
    {
        header('Content-Type: application/json');
        try {
            $user = AuthService::getUser();
            $rawFiles = $_FILES['files'] ?? ($_FILES['file'] ?? null);
            if (!$rawFiles) {
                echo json_encode(['error' => 'No files uploaded']);
                exit;
            }

            $uploadErrMsg = '';
            $files = self::normalizeFiles($rawFiles, $uploadErrMsg);
            if (empty($files)) {
                echo json_encode(['error' => $uploadErrMsg ?: 'No valid files uploaded']);
                exit;
            }

            $targetFormat = strtolower($_POST['target_format'] ?? 'docx');
            $validFormats = ['docx', 'xlsx', 'pdf', 'png', 'jpg', 'webp', 'removebg', 'compress', 'compress_max', 'compress_ebook', 'compress_mail', 'zip'];
            if (!in_array($targetFormat, $validFormats)) {
                echo json_encode(['error' => 'Invalid target format selected']);
                exit;
            }

            // Plan Access Rules Enforcement
            $userPlan = strtolower($user['plan'] ?? 'free');

            // 1. Image Format Converter (png, jpg, webp) -> Requires PRO or ENTERPRISE
            if (in_array($targetFormat, ['png', 'jpg', 'webp'])) {
                if (!in_array($userPlan, ['pro', 'enterprise'])) {
                    echo json_encode([
                        'error' => 'Image Converter requires a PRO or ENTERPRISE plan. Free plan is limited to PDF & Document tools only.',
                        'code' => 'UPGRADE_REQUIRED'
                    ]);
                    exit;
                }
            }

            // 2. AI Background Remover (removebg) -> Requires ENTERPRISE
            if ($targetFormat === 'removebg') {
                if ($userPlan !== 'enterprise') {
                    echo json_encode([
                        'error' => 'AI Background Removal requires an ENTERPRISE plan. Please upgrade your plan to unlock!',
                        'code' => 'UPGRADE_REQUIRED'
                    ]);
                    exit;
                }
            }

            // 3. Freemium Batch Upload Limit Check (Max 10 files for Free plan)
            if ($userPlan === 'free' && count($files) > 10) {
                echo json_encode([
                    'error' => 'Free accounts are limited to max 10 files per batch upload. Upgrade to PRO or ENTERPRISE for higher limits!',
                    'code' => 'UPGRADE_REQUIRED'
                ]);
                exit;
            }

            $redisHost = getenv('REDIS_HOST') ?: 'redis';
            $redisPort = (int) (getenv('REDIS_PORT') ?: 6379);

            $db = Database::getConnection();
            $createdJobs = [];

            foreach ($files as $file) {
                $jobId = Database::generateUuid();
                $originalFilename = $file['name'];
                $tmpPath = $file['tmp_name'];

                $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
                $inputS3Key = "temp_uploads/{$jobId}/input.{$ext}";

                $outExt = $targetFormat;
                if (in_array($targetFormat, ['compress', 'compress_max', 'compress_ebook', 'compress_mail'])) {
                    $outExt = $ext ?: 'pdf';
                } elseif ($targetFormat === 'removebg') {
                    $outExt = 'png';
                }
                $outputS3Key = "temp_outputs/{$jobId}/output.{$outExt}";

                self::uploadToMinIO($inputS3Key, $tmpPath, $file['type']);

                $actionType = ($targetFormat === 'removebg') ? 'remove_bg' : 'doc_convert';
                $stmt = $db->prepare("INSERT INTO jobs (id, user_id, action_type, original_filename, output_filename, target_format, input_s3_key, output_s3_key, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$jobId, $user['id'] ?? null, $actionType, $originalFilename, $originalFilename, $targetFormat, $inputS3Key, $outputS3Key]);

                $queuePayload = json_encode([
                    'job_id' => $jobId,
                    'user_id' => $user['id'] ?? null,
                    'original_filename' => $originalFilename,
                    'target_format' => $targetFormat,
                    'input_s3_key' => $inputS3Key,
                    'output_s3_key' => $outputS3Key
                ]);

                $targetQueue = ($targetFormat === 'removebg') ? 'converter_jobs_bg' : 'converter_jobs_doc';
                self::redisRPush($redisHost, $redisPort, $targetQueue, $queuePayload);

                $createdJobs[] = [
                    'job_id' => $jobId,
                    'original_filename' => $originalFilename,
                    'target_format' => $targetFormat,
                    'status' => 'pending'
                ];
            }

            echo json_encode([
                'success' => true,
                'total_files' => count($createdJobs),
                'jobs' => $createdJobs,
                'message' => count($createdJobs) . ' file(s) queued for processing'
            ]);
            exit;

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public static function handleStatus(string $jobId): void
    {
        header('Content-Type: application/json');
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT id, COALESCE(original_filename, output_filename) as original_filename, target_format, status, error_message, created_at, updated_at FROM jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job) {
            http_response_code(404);
            echo json_encode(['error' => 'Job not found']);
            exit;
        }

        echo json_encode([
            'job_id' => $job['id'],
            'original_filename' => $job['original_filename'],
            'target_format' => $job['target_format'],
            'status' => $job['status'],
            'error_message' => $job['error_message'],
            'created_at' => $job['created_at'],
            'updated_at' => $job['updated_at']
        ]);
        exit;
    }

    public static function handleDownload(string $jobId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT input_s3_key, output_s3_key, COALESCE(original_filename, output_filename) as original_filename, target_format, status FROM jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch();

        if (!$job || $job['status'] !== 'done' || empty($job['output_s3_key'])) {
            http_response_code(404);
            echo "File not ready or expired.";
            exit;
        }

        $minioHost = "http://minio:9000";
        $bucket = getenv('AWS_BUCKET') ?: 'temp-converter-files';
        $accessKey = getenv('AWS_ACCESS_KEY_ID') ?: (getenv('MINIO_ROOT_USER') ?: 'minioadmin');
        $secretKey = getenv('AWS_SECRET_ACCESS_KEY') ?: (getenv('MINIO_ROOT_PASSWORD') ?: 'minioadmin123');
        $region = 'us-east-1';
        $service = 's3';

        $cleanKey = ltrim($job['output_s3_key'], '/');
        $fileUrl = "{$minioHost}/{$bucket}/{$cleanKey}";

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $amzDate = $now->format('Ymd\THis\Z');
        $dateStamp = $now->format('Ymd');

        $canonicalUri = "/{$bucket}/{$cleanKey}";
        $emptyHash = hash('sha256', '');
        $canonicalHeaders = "host:minio:9000\nx-amz-content-sha256:{$emptyHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

        $canonicalRequest = "GET\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$emptyHash}";
        $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorizationHeader = "AWS4-HMAC-SHA256 Credential={$accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            'Host: minio:9000',
            "x-amz-date: {$amzDate}",
            "x-amz-content-sha256: {$emptyHash}",
            "Authorization: {$authorizationHeader}"
        ];

        $ch = curl_init($fileUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $fileData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($httpCode === 403 || $httpCode === 0) {
            $ch = curl_init($fileUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $fileData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);
        }

        if ($httpCode !== 200 || !$fileData) {
            http_response_code(500);
            $errDetail = trim(substr(strip_tags((string)$fileData), 0, 200));
            echo "Failed to stream download from storage (HTTP {$httpCode})" . ($errDetail ? ": {$errDetail}" : ".");
            exit;
        }

        // Delete temporary files from MinIO immediately after reading stream data
        if (!empty($job['input_s3_key'])) {
            self::deleteFromMinIO($job['input_s3_key']);
        }
        if (!empty($job['output_s3_key'])) {
            self::deleteFromMinIO($job['output_s3_key']);
        }

        $origBaseName = pathinfo($job['original_filename'], PATHINFO_FILENAME);
        $outExt = pathinfo($job['output_s3_key'], PATHINFO_EXTENSION);
        if (!$outExt || in_array($job['target_format'], ['compress', 'compress_max', 'compress_ebook', 'compress_mail'])) {
            $outExt = pathinfo($job['original_filename'], PATHINFO_EXTENSION);
        }
        if (!$outExt) {
            $outExt = ($job['target_format'] === 'removebg') ? 'png' : $job['target_format'];
        }

        // Clean filename for HTTP header compatibility
        $safeBaseName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $origBaseName);
        if (empty($safeBaseName)) {
            $safeBaseName = 'converted_file';
        }
        $safeDownloadName = "{$safeBaseName}_converted.{$outExt}";
        $utf8DownloadName = rawurlencode("{$origBaseName}_converted.{$outExt}");

        // Clear any previous output buffers to avoid Content-Length mismatch (NS_ERROR_NET_PARTIAL_TRANSFER)
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . $safeDownloadName . '"; filename*=UTF-8\'\'' . $utf8DownloadName);
        header('Content-Length: ' . strlen($fileData));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');

        $updateStmt = $db->prepare("UPDATE jobs SET status = 'downloaded', downloaded_at = NOW() WHERE id = ?");
        $updateStmt->execute([$jobId]);

        echo $fileData;
        exit;
    }

    private static function normalizeFiles($rawFiles, string &$errorMessage = ''): array
    {
        $normalized = [];
        if (!is_array($rawFiles['name'])) {
            if ($rawFiles['error'] === UPLOAD_ERR_OK) {
                $normalized[] = $rawFiles;
            } elseif ($rawFiles['error'] === UPLOAD_ERR_INI_SIZE || $rawFiles['error'] === UPLOAD_ERR_FORM_SIZE) {
                $errorMessage = "File exceeds upload limit (Max 100MB).";
            }
            return $normalized;
        }

        $count = count($rawFiles['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($rawFiles['error'][$i] === UPLOAD_ERR_OK) {
                $normalized[] = [
                    'name' => $rawFiles['name'][$i],
                    'type' => $rawFiles['type'][$i],
                    'tmp_name' => $rawFiles['tmp_name'][$i],
                    'error' => $rawFiles['error'][$i],
                    'size' => $rawFiles['size'][$i]
                ];
            } elseif ($rawFiles['error'][$i] === UPLOAD_ERR_INI_SIZE || $rawFiles['error'][$i] === UPLOAD_ERR_FORM_SIZE) {
                $errorMessage = "File exceeds upload limit (Max 100MB).";
            }
        }

        return $normalized;
    }

    private static function uploadToMinIO(string $objectKey, string $filePath, string $contentType): void
    {
        $minioHost = "http://minio:9000";
        $bucket = getenv('AWS_BUCKET') ?: 'temp-converter-files';
        $accessKey = getenv('AWS_ACCESS_KEY_ID') ?: (getenv('MINIO_ROOT_USER') ?: 'minioadmin');
        $secretKey = getenv('AWS_SECRET_ACCESS_KEY') ?: (getenv('MINIO_ROOT_PASSWORD') ?: 'minioadmin123');
        $region = 'us-east-1';
        $service = 's3';

        $cleanKey = ltrim($objectKey, '/');
        $url = "{$minioHost}/{$bucket}/{$cleanKey}";
        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new Exception("Failed to read upload file: {$filePath}");
        }
        $contentHash = hash('sha256', $content);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $amzDate = $now->format('Ymd\THis\Z');
        $dateStamp = $now->format('Ymd');

        $canonicalUri = "/{$bucket}/{$cleanKey}";
        $canonicalHeaders = "host:minio:9000\nx-amz-content-sha256:{$contentHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

        $canonicalRequest = "PUT\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$contentHash}";
        $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorizationHeader = "AWS4-HMAC-SHA256 Credential={$accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            'Content-Type: ' . ($contentType ?: 'application/octet-stream'),
            'Host: minio:9000',
            "x-amz-date: {$amzDate}",
            "x-amz-content-sha256: {$contentHash}",
            "Authorization: {$authorizationHeader}"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 204) {
            throw new Exception("S3 upload failed with HTTP status code {$httpCode}: {$response}");
        }
    }

    private static function redisRPush(string $host, int $port, string $queue, string $payload): void
    {
        $fp = @fsockopen($host, $port, $errno, $errstr, 3.0);
        if (!$fp) {
            throw new Exception("Could not connect to Redis at {$host}:{$port} ($errstr)");
        }
        $cmd = "*3\r\n$5\r\nRPUSH\r\n$" . strlen($queue) . "\r\n{$queue}\r\n$" . strlen($payload) . "\r\n{$payload}\r\n";
        fwrite($fp, $cmd);
        $response = fgets($fp);
        fclose($fp);
    }

    private static function deleteFromMinIO(string $objectKey): void
    {
        if (empty($objectKey))
            return;

        $minioHost = "http://minio:9000";
        $bucket = getenv('AWS_BUCKET') ?: 'temp-converter-files';
        $accessKey = getenv('AWS_ACCESS_KEY_ID') ?: (getenv('MINIO_ROOT_USER') ?: 'minioadmin');
        $secretKey = getenv('AWS_SECRET_ACCESS_KEY') ?: (getenv('MINIO_ROOT_PASSWORD') ?: 'minioadmin123');
        $region = 'us-east-1';
        $service = 's3';

        $cleanKey = ltrim($objectKey, '/');
        $url = "{$minioHost}/{$bucket}/{$cleanKey}";

        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $amzDate = $now->format('Ymd\THis\Z');
        $dateStamp = $now->format('Ymd');

        $canonicalUri = "/{$bucket}/{$cleanKey}";
        $emptyHash = hash('sha256', '');
        $canonicalHeaders = "host:minio:9000\nx-amz-content-sha256:{$emptyHash}\nx-amz-date:{$amzDate}\n";
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';

        $canonicalRequest = "DELETE\n{$canonicalUri}\n\n{$canonicalHeaders}\n{$signedHeaders}\n{$emptyHash}";
        $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
        $stringToSign = "AWS4-HMAC-SHA256\n{$amzDate}\n{$credentialScope}\n" . hash('sha256', $canonicalRequest);

        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
        $signature = hash_hmac('sha256', $stringToSign, $kSigning);

        $authorizationHeader = "AWS4-HMAC-SHA256 Credential={$accessKey}/{$credentialScope}, SignedHeaders={$signedHeaders}, Signature={$signature}";

        $headers = [
            'Host: minio:9000',
            "x-amz-date: {$amzDate}",
            "x-amz-content-sha256: {$emptyHash}",
            "Authorization: {$authorizationHeader}"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}
