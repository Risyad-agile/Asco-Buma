<?php
namespace App\Services;

use App\Models\IntegrationRun;
use App\Models\IntegrationRunLog;

class IntegrationLogger
{
    public function start(array $data): IntegrationRun
    {
        return IntegrationRun::create(array_merge([
            'status' => 'running',
            'started_at' => now(),
        ], $data));
    }

    public function log(IntegrationRun $run, string $event, string $message, array $data = [], string $level = 'info'): void
    {
        IntegrationRunLog::create([
            'integration_run_id' => $run->id,
            'level' => $level,
            'event' => $event,
            'scope' => $data['scope'] ?? $run->scope,
            'page' => $data['page'] ?? null,
            'batch' => $data['batch'] ?? null,
            'type' => $data['type'] ?? null,
            'filename' => $data['filename'] ?? null,
            's3_key' => $data['s3_key'] ?? null,
            'count' => $data['count'] ?? null,
            'message' => $message,
            'context' => $data,
        ]);
    }

    public function success(IntegrationRun $run, array $totals = []): void
    {
        $run->update(array_merge([
            'status' => 'success',
            'finished_at' => now(),
        ], $totals));
    }

    public function fail(IntegrationRun $run, \Throwable $e): void
    {
        $this->log($run, 'failed', $e->getMessage(), [
            'exception' => get_class($e),
            'trace' => substr($e->getTraceAsString(), 0, 8000),
        ], 'error');

        $run->update([
            'status' => 'failed',
            'finished_at' => now(),
            'error_message' => $e->getMessage(),
        ]);
    }
}
