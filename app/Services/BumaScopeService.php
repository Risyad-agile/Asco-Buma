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
            1 => '/api/esg/master/account-styles-scope-1',
            2 => '/api/esg/master/account-styles-scope-2',
            3 => '/api/esg/master/account-styles-scope-3',
            default => throw new InvalidArgumentException('Scope must be 1, 2, or 3'),
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

        $client = new Client(['timeout' => 60]);

        $pageNumber = 1;
        $totalPages = 1;

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        // preload local remote_id → modified_utc_date (fast)
        $localMap = DataEnv::query()
            ->select(['scope','remote_id','modified_utc_date'])
            ->whereNotNull('remote_id')
            ->get()
            ->keyBy(fn($x) => $x->scope . ':' . $x->remote_id);
      
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

            $resp = $client->request('POST', $endpoint, [
                'query' => ['ver' => $ver],
                'headers' => [
                    'Ocp-Apim-Subscription-Key' => $apiKey, // match Postman
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $body,
            ]);
            

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

            foreach ($rows as $r) {
                $remoteId = (int) Arr::get($r, 'id', 0);
                if ($remoteId <= 0) continue;

                $remoteModifiedStr = Arr::get($r, 'modifiedUtcDate');
                $remoteModified = $remoteModifiedStr ? Carbon::parse($remoteModifiedStr) : null;

                $key = $scope . ':' . $remoteId;
                $existing = $localMap->get($key);
                $existingModified = $existing?->modified_utc_date ? Carbon::parse($existing->modified_utc_date) : null;

                if (!$existing) {
                    $status = 'new';
                    $inserted++;
                } else {
                    if ($remoteModified && $existingModified && $remoteModified->lte($existingModified)) {
                        $skipped++;

                        // If you want to mark skipped in DB:
                        DataEnv::where('remote_id', $remoteId)->update([
                            'sync_status' => 'skipped',
                            'last_synced_at' => $now,
                            'updated_at' => $now,
                        ]);

                        continue;
                    }

                    $status = 'updated';
                    $updated++;
                }

                $toUpsert[] = [
                    'scope' => $scope,
                    'remote_id' => $remoteId,
                    'sync_status' => $status,
                    'last_synced_at' => $now,

                    // Optional: keep scope info in message or add a column if you want
                    // 'scope' => $scope,

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

                    'record_start'     => $this->toMysqlDatetime(Arr::get($r, 'recordStart')),
                    'record_end'       => $this->toMysqlDatetime(Arr::get($r, 'recordEnd')),
                    'created_utc_date' => $this->toMysqlDatetime(Arr::get($r, 'createdUtcDate')),
                    'modified_utc_date'=> $this->toMysqlDatetime(Arr::get($r, 'modifiedUtcDate')),
                

                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach (array_chunk($toUpsert, 500) as $chunk) {
                DataEnv::upsert(
                    $chunk,
                    ['remote_id'],
                    [
                        'sync_status','last_synced_at',
                        'organization','location','account_style_caption','account_number',
                        'account_reference','account_supplier','record_start','record_end',
                        'quantity','total_cost_incl_tax_local_currency','record_reference',
                        'record_invoice_number','record_data_quality','total',
                        'created_utc_date','modified_utc_date','updated_at'
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
    private function toMysqlDatetime(?string $iso): ?string
    {
        if (!$iso) return null;

        // handles "2026-01-14T02:16:29.557Z" and "2025-06-30T00:00:00"
        return Carbon::parse($iso)->utc()->format('Y-m-d H:i:s');
    }

}
