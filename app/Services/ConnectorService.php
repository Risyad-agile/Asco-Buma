<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Models\Location;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ConnectorService
{
    public function setDynamicConnection($companyId)
    {
        
        $company = \App\Models\Companies::findOrFail($companyId);
        // dd($company);
        Config::set("database.connections.dynamic", [
            "driver"   => "mysql",
            "host"     => $company->db_host,
            "database" => $company->db_name,
            "username" => $company->db_user,
            "password" => $company->db_pass,
            "charset"  => "utf8mb4",
            "collation"=> "utf8mb4_general_ci",
        ]);
        
        // force reconnect
        DB::purge('dynamic');
        DB::reconnect('dynamic');

        return true;
    }

    public function fetchAndStoreFromClient($source)
    {
        $config = json_decode($source->config_json, true);
        if (!$config) {
            return ['success' => false, 'message' => 'Invalid or empty config_json'];
        }

        $localTable = strtoupper(trim($source->local_table ?? ''));
        // dd($localTable);
        if (empty($localTable)) {
            return ['success' => false, 'message' => 'Local table not defined for this source'];
        }

        try {
            // 1️⃣ Panggil API client
            $response = $this->fetchFromClient($config);

            if (($response['status'] ?? '') !== 'success') {
                return [
                    'success' => false,
                    'message' => 'Failed to fetch data from client',
                    'detail'  => $response
                ];
            }

            $data = $response['data'] ?? [];
            
            // 2️⃣ Normalisasi data
            if (isset($data['result']['content']['data'])) {
                $data = $data['result']['content']['data'];
            } elseif (isset($data['data'])) {
                $data = $data['data'];
            }

            if (!is_array($data)) {
                return ['success' => false, 'message' => 'Response data is not a valid array'];
            }
            
            // 3️⃣ Simpan data sesuai local_table
            $result = null; 
            switch ($localTable) {
                case 'LOCATIONS':
                    // dd($localTable, $source->comp_id);
                    $result = $this->insertLocations($data, $source->comp_id);
                    break;

                case 'DATA_CSR':  
                    $result = $this->insertUpdateDataCSR($data, $source->comp_id);
                    break;

                default:
                    return [
                        'success' => false,
                        'message' => "No handler defined for local_table '{$localTable}'"
                    ];
            }

            return [
                'success' => true,
                'message' => "Fetched & processed data for {$localTable}.",
                'detail'  => $result
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }


    public function fetchFromClient(array $config)
    {
        // --- ambil value dari konfigurasi (pakai prefix api_) ---
        $baseUrl  = $config['api_base_url'] ?? null;
        $endpoint = $config['api_endpoint'] ?? null;
        $method   = strtoupper($config['api_method'] ?? 'GET');

        if (!$baseUrl || !$endpoint) {
            throw new \Exception("Missing api_base_url or api_endpoint in configuration.");
        }

        // --- headers dasar ---
        $headers = ['Content-Type' => 'application/json'];

        // --- tambahkan header opsional ---
        if (!empty($config['api_token'])) {
            $headers['Authorization'] = 'Bearer ' . $config['api_token'];
        }

        if (!empty($config['api_key_name']) && !empty($config['api_key_value'])) {
            $headers[$config['api_key_name']] = $config['api_key_value'];
        }

        // --- URL gabungan ---
        $url = rtrim($baseUrl, '/') . '/' . ltrim($endpoint, '/');

        // --- body (kalau ada api_body) ---
        $body = [];
        if (!empty($config['api_body'])) {
            try {
                $body = json_decode($config['api_body'], true);
            } catch (\Exception $e) {
                throw new \Exception("Invalid JSON format in api_body.");
            }
        }

        // --- panggil API pakai Guzzle ---
        $client = new Client(['timeout' => 30]);
        $options = ['headers' => $headers];

        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['json'] = $body;
        }

        $response = $client->request($method, $url, $options); 
        $statusCode = $response->getStatusCode();
        $result = json_decode($response->getBody(), true);

        return [
            'status' => 'success',
            'code' => $statusCode,
            'data' => $result
        ];
    }

    protected function insertLocations(array $data, $companyId)
    {
        // dd($companyId);
        $this->setDynamicConnection($companyId);

        $db = DB::connection('dynamic');
        
        $inserted = 0;
        $skipped = 0;

        foreach ($data as $row) {

            if (!is_array($row)) continue;

            // Mandatory validation
            $mandatory = [
                'organization', 'groupType',
                'groupName1', 'groupName2', 'groupName3',
                'location'
            ];

            $missing = array_filter($mandatory, fn($key) => empty($row[$key] ?? null));
            if (count($missing) > 0) {
                Log::warning("Skipped location - missing mandatory fields", [
                    'missing' => $missing,
                    'data' => $row
                ]);
                $skipped++;
                continue;
            }

            try {
                $reffId = $row['id'] ?? null;

                // cek duplikat di dynamic db
                $exists = $db->table('locations')
                            ->where('reff_id', $reffId)
                            ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                // insert ke dynamic database
                $db->table('locations')->insert([
                    'reff_id'              => $reffId,
                    'org_name'             => trim($row['organization'] ?? ''),
                    'group_type'           => trim($row['groupType'] ?? ''),
                    'group_hierarchy_name' => trim($row['groupHierarchyName'] ?? ''),
                    'group_name1'          => trim($row['groupName1'] ?? ''),
                    'group_name2'          => trim($row['groupName2'] ?? ''),
                    'group_name3'          => trim($row['groupName3'] ?? ''),
                    'location_name'        => trim($row['location'] ?? ''),
                    'location_type'        => null,
                    'location_reff'        => trim($row['locationReference'] ?? ''),
                    'location_reff_no'     => trim($row['locationRefNo'] ?? ''),
                    'location_id'          => null,
                    'location_address'     => trim($row['streetAddress'] ?? ''),
                    'location_city'        => trim($row['city'] ?? ''),
                    'location_state_province' => trim($row['stateProvince'] ?? ''),
                    'postal_code'          => $row['postalCode'] ?? null,
                    'country'              => trim($row['country'] ?? ''),
                    'latitude_y'           => $row['latitudeY'] ?? null,
                    'longitude_x'          => $row['longitudeX'] ?? null,
                    'location_close_date'  => ($row['locationCloseDate'] ?? null) === "1900-01-01T00:00:00"
                                                ? null
                                                : $row['locationCloseDate'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $inserted++;

            } catch (\Exception $e) {
                Log::error("❌ Failed to insert location: " . $e->getMessage(), [
                    'data' => $row
                ]);
            }
        }

        Log::info("📦(Client {$companyId}) Locations inserted: {$inserted}, skipped: {$skipped}");

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    protected function insertUpdateDataCSR(array $data, $companyId)
    {
        $this->setDynamicConnection($companyId);

        $db = DB::connection('dynamic');
        $test = DB::connection('dynamic')->select('select database() as dbname');
        logger('🔥 Dynamic DB: ' . json_encode($test));

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($data as $row) {
            if (!is_array($row)) continue;

            try {
                // Ambil reff_id dari JSON
                $reffId = $row['id'] ?? null;
                if (!$reffId) {
                    $skipped++;
                    continue;
                }

                // Cek apakah data sudah ada
                $exists = $db->table('data_csr')
                            ->where('reff_id', $reffId)
                            ->first();

                // Payload mapping JSON → DB
                $payload = [
                    'reff_id'           => $reffId,

                    'location'          => trim($row['location'] ?? ''),
                    'account_style'     => trim($row['accountStyle'] ?? ''),
                    'year'              => $row['year'] ?? null,

                    'male'              => $row['male'] ?? 0,
                    'female'            => $row['female'] ?? 0,
                    'k30_th'            => $row['k30Th'] ?? 0,
                    'thirty_to_50_th'   => $row['thirtyTo50Th'] ?? 0,
                    'more_than_50_th'   => $row['moreThan50Th'] ?? 0,

                    'phd'               => $row['phd'] ?? 0,
                    'postgraduate'      => $row['postgraduate'] ?? 0,
                    'undergraduate'     => $row['undergraduate'] ?? 0,
                    'diploma'           => $row['diploma'] ?? 0,
                    'high_school'       => $row['highSchool'] ?? 0,
                    'junior_high_school'=> $row['juniorHighSchool'] ?? 0,
                    'elementary_school' => $row['elementarySchool'] ?? 0,
                    'others'            => $row['others'] ?? 0,

                    'islam'             => $row['islam'] ?? 0,
                    'kristen'           => $row['kristen'] ?? 0,
                    'katolik'           => $row['katolik'] ?? 0,
                    'hindu'             => $row['hindu'] ?? 0,
                    'budha'             => $row['budha'] ?? 0,
                    'konghucu'          => $row['konghucu'] ?? 0,

                    'employee_total'    => $row['employeeTotal'] ?? 0,
                    'total'             => $row['total'] ?? 0,

                    'created_utc_date'  => $row['createdUtcDate'] ?? null,
                    'modified_utc_date' => $row['modifiedUtcDate'] ?? null,

                    'updated_at'        => now(),
                ];

                if ($exists) {
                    // UPDATE
                    $db->table('data_csr')
                    ->where('reff_id', $reffId)
                    ->update($payload);

                    $updated++;

                } else {
                    // INSERT
                    $payload['created_at'] = now();

                    $db->table('data_csr')
                    ->insert($payload);

                    $inserted++;
                }

            } catch (\Exception $e) {
                Log::error("❌ Failed to insert/update data_csr: " . $e->getMessage(), [
                    'data' => $row
                ]);
            }
        }

        return [
            'inserted' => $inserted,
            'updated'  => $updated,
            'skipped'  => $skipped,
        ];
    }


    // protected function insertLocations(array $data)
    // {
    //     $inserted = 0;
    //     $skipped = 0; 
    //     foreach ($data as $row) {
    //         if (!is_array($row)) continue;

    //         // ✅ validasi mandatory field
    //         $mandatory = [
    //             'organization', 'groupType',
    //             'groupName1', 'groupName2', 'groupName3',
    //             'location'
    //         ];
             
    //         $missing = array_filter($mandatory, fn($key) => empty($row[$key] ?? null));
    //         if (count($missing) > 0) {
    //             Log::warning("Skipped location - missing mandatory fields", [
    //                 'missing' => $missing,
    //                 'data' => $row
    //             ]);
    //             $skipped++;
    //             continue;
    //         }

    //         try {
    //             $locid = trim($row['id'] ?? '');

    //             // ✅ cek apakah sudah ada di DB
    //             $exists = Location::where('reff_id', $row['id'] ?? null)->exists(); 

    //             if ($exists) {
    //                 $skipped++;
    //                 continue;
    //             }

    //             // ✅ insert baru
    //             Location::create([
    //                 'reff_id'              => $row['id'] ?? null,
    //                 'org_name'             => trim($row['organization'] ?? ''),
    //                 'group_type'           => trim($row['groupType'] ?? ''),
    //                 'group_hierarchy_name' => trim($row['groupHierarchyName'] ?? ''),
    //                 'group_name1'          => trim($row['groupName1'] ?? ''),
    //                 'group_name2'          => trim($row['groupName2'] ?? ''),
    //                 'group_name3'          => trim($row['groupName3'] ?? ''),
    //                 'location_name'        => trim($row['location'] ?? ''),
    //                 'location_type'        => null,
    //                 'location_reff'        => trim($row['locationReference'] ?? ''),
    //                 'location_reff_no'     => trim($row['locationRefNo'] ?? ''),
    //                 'location_id'          => null,
    //                 'location_address'     => trim($row['streetAddress'] ?? ''),
    //                 'location_city'        => trim($row['city'] ?? ''),
    //                 'location_state_province' => trim($row['stateProvince'] ?? ''),
    //                 'postal_code'          => $row['postalCode'] ?? null,
    //                 'country'              => trim($row['country'] ?? ''),
    //                 'latitude_y'           => $row['latitudeY'] ?? null,
    //                 'longitude_x'          => $row['longitudeX'] ?? null,
    //                 'location_close_date'  => ($row['locationCloseDate'] ?? null) === "1900-01-01T00:00:00"
    //                     ? null
    //                     : $row['locationCloseDate'],
    //             ]);

    //             $inserted++;
    //         } catch (\Exception $e) {
    //             Log::error("❌ Failed to insert location: " . $e->getMessage(), [
    //                 'data' => $row
    //             ]);
    //         }
    //     }

    //     Log::info("📦 Locations inserted: {$inserted}, skipped: {$skipped}");
    //     return ['inserted' => $inserted, 'skipped' => $skipped];
    // }
}
