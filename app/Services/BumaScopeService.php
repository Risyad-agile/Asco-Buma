<?php

namespace App\Services;

use App\Models\DataEnv;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class BumaScopeService
{
    private function endpointByScope(int $scope): string
    {
        return match ($scope) {
            // 1 => '/api/esg/master/account-styles-scope-1',
            // 2 => '/api/esg/master/account-styles-scope-2',
            // 3 => '/api/esg/master/account-styles-scope-3',
            // 4 => '/api/esg/master/account-styles-non-scope', // 4 for non-scope
            
            1 => '/master/account-styles-scope-1',
            2 => '/master/account-styles-scope-2',
            3 => '/master/account-styles-scope-3',
            4 => '/master/account-styles-non-scope', // 4 for non-scope
            default => throw new InvalidArgumentException('Scope must be 1, 2, 3, or 4'),
        };
    }

    public function syncScope(
        int $scope,
        string $organization = 'BIG',
        int $pageSize = 100
    ): array {
        $baseUrl = rtrim(config('buma.esg_bridge.base_url'), '/');
        $apiKey  = config('buma.esg_bridge.api_key');
        $ver     = config('buma.esg_bridge.version', 'v1');

        $endpoint = $baseUrl . $this->endpointByScope($scope);

        // ✅ Better timeouts for large payload / APIM
        $client = new Client([
            'timeout' => 180,          // total request timeout
            'connect_timeout' => 20,   // connection timeout
        ]);

        $pageNumber = 1;
        $totalPages = 1;

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        do {
            $body = [
                "pagingParameter" => [
                    "pageNumber" => $pageNumber,
                    "pageSize" => $pageSize,
                    "orderColumn" => "",
                    "orderDir" => ""
                ],
                "globalSearch" => "",
                "pageFilter" => [
                    ["columnKey" => "Organization", "columnValue" => $organization]
                ]
            ];

            // ✅ Retry for timeout / transient network errors (cURL 28)
            $resp = $this->requestWithRetry($client, $endpoint, $ver, $apiKey, $body, $scope, $pageNumber);

            $json = json_decode((string) $resp->getBody(), true) ?? [];

            if (Arr::get($json, 'result.isError') === true) {
                throw new \Exception(Arr::get($json, 'result.message', 'API error'));
            }

            $content = Arr::get($json, 'result.content', []);
            $totalPages = (int) Arr::get($content, 'totalPages', 1);

            $rows = Arr::get($content, 'data', []);
            if (!is_array($rows)) $rows = [];

            $now = now();
            $toUpsert = [];

            // ✅ Build remote id list for this page
            $remoteIds = [];
            foreach ($rows as $r) {
                $rid = (int) Arr::get($r, 'id', 0);
                if ($rid > 0) $remoteIds[] = $rid;
            }

            // ✅ Per-page existing lookup (FAST, low memory)
            $existingModifiedMap = [];
            if (!empty($remoteIds)) {
                $existingModifiedMap = DataEnv::query()
                    ->where('scope', $scope)
                    ->whereIn('remote_id', $remoteIds)
                    ->pluck('modified_utc_date', 'remote_id')  // remote_id => modified_utc_date
                    ->toArray();
            }

            foreach ($rows as $r) {
                $remoteId = (int) Arr::get($r, 'id', 0);
                if ($remoteId <= 0) continue;

                // Parse modified only when needed
                $remoteModifiedStr = Arr::get($r, 'modifiedUtcDate');
                $remoteModified = $remoteModifiedStr ? Carbon::parse($remoteModifiedStr) : null;

                $existingModifiedStr = $existingModifiedMap[$remoteId] ?? null;
                $existingModified = $existingModifiedStr ? Carbon::parse($existingModifiedStr) : null;

                // ✅ Skip unchanged (NO DB write)
                if ($remoteModified && $existingModified && $remoteModified->lte($existingModified)) {
                    $skipped++;
                    continue;
                }

                $isExisting = $existingModifiedStr !== null;

                if (!$isExisting) {
                    $inserted++;
                    $status = 'new';
                } else {
                    $updated++;
                    $status = 'updated';
                }

                $toUpsert[] = [
                    'scope' => $scope,
                    'remote_id' => $remoteId,
                    'sync_status' => $status,
                    'last_synced_at' => $now,

                    'organization' => Arr::get($r, 'organization'),
                    'location' => Arr::get($r, 'location'),
                    'account_style_caption' => Arr::get($r, 'accountStyleCaption'),
                    'account_number' => Arr::get($r, 'accountNumber'),
                    'account_reference' => Arr::get($r, 'accountReference'),
                    'account_supplier' => Arr::get($r, 'accountSupplier'),
                    'quantity' => Arr::get($r, 'quantity'),
                    'total_cost_incl_tax_local_currency' => Arr::get($r, 'totalCostInclTaxInLocalCurrency'),
                    'record_reference' => Arr::get($r, 'recordReference'),
                    'record_invoice_number' => Arr::get($r, 'recordInvoiceNumber'),
                    'record_data_quality' => Arr::get($r, 'recordDataQuality'),
                    'total' => Arr::get($r, 'total'),

                    'record_start'      => $this->toMysqlDatetime(Arr::get($r, 'recordStart')),
                    'record_end'        => $this->toMysqlDatetime(Arr::get($r, 'recordEnd')),
                    'created_utc_date'  => $this->toMysqlDatetime(Arr::get($r, 'createdUtcDate')),
                    'modified_utc_date' => $this->toMysqlDatetime(Arr::get($r, 'modifiedUtcDate')),

                    // keep updated_at if you want it
                    'updated_at' => $now,
                ];
            }

            // ✅ Larger chunk is OK now because we reduced rows (only changed/new)
            foreach (array_chunk($toUpsert, 1000) as $chunk) {
                DataEnv::upsert(
                    $chunk,
                    ['scope', 'remote_id', 'account_style_caption'],
                    [
                        'sync_status','last_synced_at',
                        'organization','location','account_style_caption','account_number',
                        'account_reference','account_supplier','record_start','record_end',
                        'quantity','total_cost_incl_tax_local_currency','record_reference',
                        'record_invoice_number','record_data_quality','total',
                        'created_utc_date','modified_utc_date',
                        'updated_at'
                    ]
                );
            }

            $pageNumber++;

        } while ($pageNumber <= $totalPages);

        return [
            'scope' => $scope,
            'organization' => $organization,
            'pageSize' => $pageSize,
            'pagesSynced' => $totalPages,
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    /**
     * Retry wrapper for Azure APIM stalls / transient timeouts.
     */
    private function requestWithRetry(
        Client $client,
        string $endpoint,
        string $ver,
        string $apiKey,
        array $body,
        int $scope,
        int $pageNumber
    ) {
        $maxAttempts = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                return $client->request('POST', $endpoint, [
                    'query' => ['ver' => $ver],
                    'headers' => [
                        'Ocp-Apim-Subscription-Key' => $apiKey,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                    ],
                    'json' => $body,
                ]);
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                $isTimeout = str_contains($msg, 'cURL error 28') || str_contains(strtolower($msg), 'timed out');

                if ($attempt < $maxAttempts && $isTimeout) {
                    $sleep = min(60, 5 * $attempt); // 5,10,15,20...
                    \Log::warning("BUMA API timeout, retrying...", [
                        'scope' => $scope,
                        'page' => $pageNumber,
                        'attempt' => $attempt,
                        'sleep' => $sleep,
                        'endpoint' => $endpoint,
                        'error' => $msg,
                    ]);
                    sleep($sleep);
                    continue;
                }

                throw $e;
            }
        }

        // should never reach
        throw new \RuntimeException("BUMA request failed after retries (scope={$scope}, page={$pageNumber}).");
    }

    private function toMysqlDatetime(?string $value): ?string
    {
        if (!$value) return null;

        $value = trim($value);

        // If it contains timezone info like Z or +07:00 or -05:00, treat as timezone-aware (UTC)
        $hasTz = str_ends_with($value, 'Z') || preg_match('/[+-]\d{2}:?\d{2}$/', $value);

        try {
            if ($hasTz) {
                // true UTC timestamp => normalize to UTC
                return Carbon::parse($value)->utc()->format('Y-m-d H:i:s');
            }

            // No timezone in string => treat as local "as-is" (do NOT shift day)
            return Carbon::parse($value, config('app.timezone'))->format('Y-m-d H:i:s');

        } catch (\Throwable $e) {
            try {
                return Carbon::createFromFormat('Y-m-d H:i:s.u', $value, config('app.timezone'))
                    ->format('Y-m-d H:i:s');
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }
}
