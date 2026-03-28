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
            "collation"=> "utf8mb4_general_ci",
        ]);

        DB::purge('dynamic');
        DB::reconnect('dynamic');
    }

    public function fetchCSRFromSource($companyId): array
    {
        // 1️⃣ ambil config dari DB
        $source = ConnectorSource::where('comp_id', $companyId)
                    ->where('local_table', 'DATA_CSR')
                    ->where('is_active', true)
                    ->firstOrFail();

        $config = $source->config_json; // sudah otomatis array

        // handle double encoded JSON
        if (is_string($config)) {

            // first decode
            $decoded = json_decode($config, true);

            // kalau hasilnya masih string, decode lagi
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            $config = $decoded;
        }

        if (!$config) {
            throw new \Exception("Config JSON kosong untuk DATA_CSR");
        }

        Log::info("🚀 Fetch CSR - Company {$companyId}");

        // 2️⃣ panggil API
        $response = $this->fetchFromClient($config);

        if (($response['status'] ?? '') !== 'success') {
            throw new \Exception("Fetch CSR gagal dari client");
        }

        $data = $response['data'] ?? [];

        // 3️⃣ normalisasi struktur response
        if (isset($data['result']['content']['data'])) {
            $data = $data['result']['content']['data'];
        } elseif (isset($data['data'])) {
            $data = $data['data'];
        }

        if (!is_array($data)) {
            throw new \Exception("Response CSR bukan array valid");
        }

        Log::info("✅ CSR fetched successfully", [
            'company_id' => $companyId,
            'total_rows' => count($data)
        ]);

        return $data;
    }    


    public function fetchTRNFromSource($companyId): array
    {
        // 1️⃣ ambil config dari DB
        $source = ConnectorSource::where('comp_id', $companyId)
                    ->where('local_table', 'DATA_TRN')
                    ->where('is_active', true)
                    ->firstOrFail();

        $config = $source->config_json;

        // handle double encoded JSON
        if (is_string($config)) {

            $decoded = json_decode($config, true);

            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            $config = $decoded;
        }

        if (!$config) {
            throw new \Exception("Config JSON kosong untuk DATA_TRN");
        }

        Log::info("🚀 Fetch TRN - Company {$companyId}");

        // 2️⃣ panggil API
        $response = $this->fetchFromClient($config);

        if (($response['status'] ?? '') !== 'success') {
            throw new \Exception("Fetch TRN gagal dari client");
        }

        $data = $response['data'] ?? [];

        // 3️⃣ normalisasi struktur response
        if (isset($data['result']['content']['data'])) {
            $data = $data['result']['content']['data'];
        } elseif (isset($data['data'])) {
            $data = $data['data'];
        }

        if (!is_array($data)) {
            throw new \Exception("Response TRN bukan array valid");
        }

        Log::info("✅ TRN fetched successfully", [
            'company_id' => $companyId,
            'total_rows' => count($data)
        ]);

        return $data;
    }


    public function fetchTOCFromSource($companyId): array
    {
        // 1️⃣ ambil config dari DB
        $source = ConnectorSource::where('comp_id', $companyId)
                    ->where('local_table', 'DATA_TOC')
                    ->where('is_active', true)
                    ->firstOrFail();

        $config = $source->config_json;

        // handle double encoded JSON
        if (is_string($config)) {

            $decoded = json_decode($config, true);

            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            $config = $decoded;
        }

        if (!$config) {
            throw new \Exception("Config JSON kosong untuk DATA_TOC");
        }

        Log::info("🚀 Fetch TOC - Company {$companyId}");

        // 2️⃣ panggil API
        $response = $this->fetchFromClient($config);

        if (($response['status'] ?? '') !== 'success') {
            throw new \Exception("Fetch TOC gagal dari client");
        }

        $data = $response['data'] ?? [];

        // 3️⃣ normalisasi struktur response (penting karena beda-beda API)
        if (isset($data['result']['content']['data'])) {
            $data = $data['result']['content']['data'];
        } elseif (isset($data['data'])) {
            $data = $data['data'];
        }

        if (!is_array($data)) {
            throw new \Exception("Response TOC bukan array valid");
        }

        Log::info("✅ TOC fetched successfully", [
            'company_id' => $companyId,
            'total_rows' => count($data)
        ]);

        return $data;
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
}