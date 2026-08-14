<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    /**
     * Stream file directly from S3 (MinIO) to browser and immediately delete from S3 once finished.
     * Memory usage is kept constant (e.g., 64KB chunk buffer) regardless of file size.
     *
     * @param Request $request
     * @param string $jobId
     * @return StreamedResponse
     */
    public function download(Request $request, string $jobId): StreamedResponse
    {
        // 1. Fetch Job from Database
        $job = Job::where('id', $jobId)->firstOrFail();

        // Verify Job Status
        if ($job->status !== 'done') {
            abort(400, "Job is not ready for download or has already been downloaded (Status: {$job->status}).");
        }

        $s3Path = $job->output_s3_key;
        $downloadFilename = $job->output_filename ?? 'converted_file';

        // 2. Check if file exists in S3 Temporary Buffer
        $disk = Storage::disk('s3');
        if (!$disk->exists($s3Path)) {
            $job->update(['status' => 'expired_or_deleted']);
            abort(404, "File missing or has already expired from temporary storage.");
        }

        // 3. Obtain file size & mime type for HTTP response headers
        $fileSize = $disk->size($s3Path);
        $mimeType = $disk->mimeType($s3Path) ?? 'application/octet-stream';

        // 4. Create S3 Read Stream Handle (low memory overhead, PHP stream wrapper)
        $stream = $disk->readStream($s3Path);

        if (!$stream || !is_resource($stream)) {
            abort(500, "Failed to initialize stream from MinIO/S3 storage.");
        }

        // 5. Construct StreamedResponse with immediate post-stream cleanup
        $response = new StreamedResponse(function () use ($stream, $s3Path, $disk, $job) {
            $chunkSize = 64 * 1024; // 64 KB buffer size to keep RAM footprint tiny

            try {
                // Disable script execution timeout during file transfer
                set_time_limit(0);

                // Clean existing output buffers to avoid loading content into PHP buffer memory
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }

                // Chunked transfer to php://output
                while (!feof($stream)) {
                    $buffer = fread($stream, $chunkSize);
                    if ($buffer === false) {
                        break;
                    }
                    echo $buffer;
                    flush();
                }
            } catch (\Throwable $e) {
                Log::error("Streaming interrupted for job {$job->id}: " . $e->getMessage());
            } finally {
                // 6. Close the S3 stream resource safely
                if (is_resource($stream)) {
                    fclose($stream);
                }

                // 7. CRITICAL REQUIREMENT: STREAM-AND-DELETE (STATELESS PROCESSING)
                // Purge file from MinIO/S3 Temporary Storage immediately after streaming finishes
                try {
                    // Delete output file
                    $disk->delete($s3Path);

                    // Delete original uploaded input file if still present
                    if ($job->input_s3_key && $disk->exists($job->input_s3_key)) {
                        $disk->delete($job->input_s3_key);
                    }

                    // Update Database Job Status
                    $job->update([
                        'status' => 'downloaded',
                        'downloaded_at' => now(),
                    ]);

                    Log::info("Stream-and-Delete executed successfully for job ID: {$job->id}. Key '{$s3Path}' purged from S3.");
                } catch (\Throwable $e) {
                    Log::error("Error executing auto-deletion on S3 for job ID {$job->id}: " . $e->getMessage());
                }
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . rawurlencode($downloadFilename) . '"',
            'Content-Length' => $fileSize,
            'Cache-Control' => 'no-cache, private, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Accel-Buffering' => 'no', // Prevents Nginx reverse proxy buffering stream to RAM
        ]);

        return $response;
    }
}
