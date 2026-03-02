<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LOCService
{
    protected $connector;

    public function __construct(ConnectorService $connector)
    {
        $this->connector = $connector;
    }

    /**
     * Fetch data dari API client dan simpan ke table locations
     */
    public function fetchAndStore($source)
    {
        $config = json_decode($source->config_json, true);

        if (!$config) {
            return ['success' => false, 'message' => 'Invalid config_json'];
        }

        try {

            // 1️⃣ set dynamic DB
            $this->connector->setDynamicConnection($source->comp_id);

            // 2️⃣ fetch API
            $response = $this->connector->fetchFromClient($config);

            if (($response['status'] ?? '') !== 'success') {
                return [
                    'success' => false,
                    'message' => 'Failed fetch from client'
                ];
            }

            // 3️⃣ normalize
            $data = $this->normalize($response['data']);

            if (!is_array($data)) {
                return ['success' => false, 'message' => 'Invalid response data'];
            }

            // 4️⃣ insert
            $result = $this->insertLocations($data);

            return [
                'success' => true,
                'detail'  => $result
            ];

        } catch (\Exception $e) {

            Log::error("❌ LOCService error", [
                'message' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Normalize API structure
     */
    protected function normalize($data)
    {
        if (isset($data['result']['content']['data'])) {
            return $data['result']['content']['data'];
        }

        if (isset($data['data'])) {
            return $data['data'];
        }

        return $data;
    }

    /**
     * Insert locations ke dynamic DB
     */
    protected function insertLocations(array $data)
    {
        $db = DB::connection('dynamic');

        $inserted = 0;
        $skipped  = 0;

        foreach ($data as $row) {

            if (!is_array($row)) {
                $skipped++;
                continue;
            }

            $mandatory = [
                'organization',
                'groupType',
                'groupName1',
                'groupName2',
                'groupName3',
                'location'
            ];

            $missing = array_filter($mandatory, fn($k) => empty($row[$k] ?? null));

            if ($missing) {
                Log::warning("Skipped location - missing mandatory", [
                    'missing' => $missing
                ]);
                $skipped++;
                continue;
            }

            try {

                $reffId = $row['id'] ?? null;

                if (!$reffId) {
                    $skipped++;
                    continue;
                }

                $exists = $db->table('locations')
                             ->where('reff_id', $reffId)
                             ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $db->table('locations')->insert([
                    'reff_id'                  => $reffId,
                    'org_name'                 => trim($row['organization'] ?? ''),
                    'group_type'               => trim($row['groupType'] ?? ''),
                    'group_hierarchy_name'     => trim($row['groupHierarchyName'] ?? ''),
                    'group_name1'              => trim($row['groupName1'] ?? ''),
                    'group_name2'              => trim($row['groupName2'] ?? ''),
                    'group_name3'              => trim($row['groupName3'] ?? ''),
                    'location_name'            => trim($row['location'] ?? ''),
                    'location_reff'            => trim($row['locationReference'] ?? ''),
                    'location_reff_no'         => trim($row['locationRefNo'] ?? ''),
                    'location_address'         => trim($row['streetAddress'] ?? ''),
                    'location_city'            => trim($row['city'] ?? ''),
                    'location_state_province'  => trim($row['stateProvince'] ?? ''),
                    'postal_code'              => $row['postalCode'] ?? null,
                    'country'                  => trim($row['country'] ?? ''),
                    'latitude_y'               => $row['latitudeY'] ?? null,
                    'longitude_x'              => $row['longitudeX'] ?? null,
                    'location_close_date'      => ($row['locationCloseDate'] ?? null) === "1900-01-01T00:00:00"
                                                ? null
                                                : $row['locationCloseDate'],
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);

                $inserted++;

            } catch (\Exception $e) {

                Log::error("❌ Failed insert location", [
                    'message' => $e->getMessage()
                ]);

                $skipped++;
            }
        }

        Log::info("📦 LOC inserted: {$inserted}, skipped: {$skipped}");

        return compact('inserted', 'skipped');
    }
}