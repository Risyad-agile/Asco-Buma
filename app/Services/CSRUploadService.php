<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class CSRUploadService
{
    protected string $disk;

    public function __construct(string $disk = 's3_agile_poc')
    {
        $this->disk = $disk; // pakai disk sesuai .env
    }

    /**
     * Upload file Excel CSR ke S3
     * @param array $files -> array path local file
     * @return array -> status upload
     */
    public function uploadFiles(array $files): array
    {
        $uploadedFiles = [];

        foreach ($files as $localPath) {
            $fileName = basename($localPath);

            try {
                Storage::disk($this->disk)->putFileAs(
                    '',         // folder di bucket, bisa diatur sesuai kebutuhan
                    $localPath, // path file lokal
                    $fileName   // nama file di S3
                );

                $uploadedFiles[] = "Uploaded: {$fileName}";
                Log::info("✅ Uploaded to {$this->disk}: {$fileName}");
            } catch (\Exception $e) {
                Log::error("❌ Failed upload to {$this->disk}: {$fileName}", [
                    'message' => $e->getMessage()
                ]);
            }
        }

        return $uploadedFiles;
    }
}