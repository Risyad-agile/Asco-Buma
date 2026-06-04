<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\ConnectorSource;

class ConnectorService
{
    /**
     * Set dynamic database connection per company
     */
    public function setDynamicConnection($companyId): void
    {
        $company = \App\Models\Companies::findOrFail($companyId);

        Config::set("database.connections.dynamic", [
            "driver"   => "mysql",
            "host"     => $company->db_host,
            "database" => $company->db_name,
            "username" => $company->db_user,
            "password" => $company->db_pass,
            "charset"  => "utf8mb4",
            "collation" => "utf8mb4_general_ci",
        ]);

        DB::purge('dynamic');
        DB::reconnect('dynamic');
    }

    public function fetchCSRFromSource($companyId): array
    {
        $config = $this->getConfig($companyId, 'data_csr');
        Log::info("🚀 Fetch CSR - Company {$companyId}");
        $data = $this->fetchAllPages($config);
        Log::info("✅ CSR fetched successfully", ['company_id' => $companyId, 'total_rows' => count($data)]);
        return $data;
    }

    public function fetchTRNFromSource($companyId): array
    {
        $config = $this->getConfig($companyId, 'data_trn');
        Log::info("🚀 Fetch TRN - Company {$companyId}");
        $data = $this->fetchAllPages($config);
        Log::info("✅ TRN fetched successfully", ['company_id' => $companyId, 'total_rows' => count($data)]);
        return $data;
    }

    public function fetchTOCFromSource($companyId): array
    {
        $config = $this->getConfig($companyId, 'data_toc');
        Log::info("🚀 Fetch TOC - Company {$companyId}");
        $data = $this->fetchAllPages($config);
        Log::info("✅ TOC fetched successfully", ['company_id' => $companyId, 'total_rows' => count($data)]);
        return $data;
    }

    public function fetchSHEFromSource($companyId): array
    {
        $config = $this->getConfig($companyId, 'data_she');
        Log::info("🚀 Fetch SHE - Company {$companyId}");
        $data = $this->fetchAllPages($config);
        Log::info("✅ SHE fetched successfully", ['company_id' => $companyId, 'total_rows' => count($data)]);
        return $data;
    }

    private function getConfig($companyId, string $localTable): array
    {
        $source = ConnectorSource::where('comp_id', $companyId)
            ->where('local_table', $localTable)
            ->where('is_active', true)
            ->firstOrFail();

        $config = $source->config_json;

        if (is_string($config)) {
            $decoded = json_decode($config, true);
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }
            $config = $decoded;
        }

        if (!$config) {
            throw new \Exception("Config JSON kosong untuk {$localTable}");
        }

        return $config;
    }

    /**
     * Call external API client
     */
    public function fetchFromClient(array $config): array
    {
        $baseUrl  = $config['api_base_url'] ?? null;
        $endpoint = $config['api_endpoint'] ?? null;
        $method   = strtoupper($config['api_method'] ?? 'GET');

        if (!$baseUrl || !$endpoint) {
            throw new \Exception("Missing api_base_url or api_endpoint.");
        }

        $headers = ['Content-Type' => 'application/json'];

        if (!empty($config['api_token'])) {
            $headers['Authorization'] = 'Bearer ' . $config['api_token'];
        }

        if (!empty($config['api_key_name']) && !empty($config['api_key_value'])) {
            $headers[$config['api_key_name']] = $config['api_key_value'];
        }

        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        $body = [];
        if (!empty($config['api_body'])) {
            $body = json_decode($config['api_body'], true);
        }

        $client = new Client(['timeout' => 30]);

        $options = ['headers' => $headers];

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $body;
        }

        $response = $client->request($method, $url, $options);

        return [
            'status' => 'success',
            'code'   => $response->getStatusCode(),
            'data'   => json_decode($response->getBody(), true)
        ];
    }

    /**
     * Helper to fetch all pages API      
     * */
    private function fetchAllPages(array $config): array
    {
        $allData = [];
        $pageNumber = 1;

        do {
            // override pageNumber in body
            $body = json_decode($config['api_body'] ?? '{}', true) ?: [];
            $body['pagingParameter']['pageNumber'] = $pageNumber;
            $config['api_body'] = json_encode($body);

            $response = $this->fetchFromClient($config);

            if (($response['status'] ?? '') !== 'success') {
                throw new \Exception("API fetch failed on page {$pageNumber}");
            }

            $data = $response['data'] ?? [];

            // normalize
            if (isset($data['result']['content']['data'])) {
                $rows = $data['result']['content']['data'];
                $totalPages = (int) ($data['result']['content']['totalPages'] ?? 1);
            } elseif (isset($data['data'])) {
                $rows = $data['data'];
                $totalPages = 1;
            } else {
                $rows = [];
                $totalPages = 1;
            }

            if (!is_array($rows)) break;

            $allData = array_merge($allData, $rows);

            Log::info("📄 Fetched page {$pageNumber}/{$totalPages}", [
                'rows' => count($rows),
                'total_so_far' => count($allData),
            ]);

            $pageNumber++;
        } while ($pageNumber <= $totalPages);

        return $allData;
    }
}
