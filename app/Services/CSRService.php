<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CSRService
{
    protected ConnectorService $connector;

    public function __construct(ConnectorService $connector)
    {
        $this->connector = $connector;
    }

    /**
     * STEP 2
     * Insert / Update CSR ke dynamic DB
     */
    public function syncCSR(array $data, int $companyId): array
    {
        // set koneksi dynamic
        $this->connector->setDynamicConnection($companyId);

        $db = DB::connection('dynamic');

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        foreach ($data as $row) {

            if (!is_array($row)) {
                $skipped++;
                continue;
            }

            try {

                $reffId = $row['id'] ?? null;

                if (!$reffId) {
                    $skipped++;
                    continue;
                }

                $payload = [
                    'location'              => trim($row['location'] ?? ''),
                    'account_style'         => trim($row['accountStyle'] ?? ''),
                    'year'                  => $row['year'] ?? null,

                    'male'                  => (int) ($row['male'] ?? 0),
                    'female'                => (int) ($row['female'] ?? 0),

                    'k30_th'                => (int) ($row['k30Th'] ?? 0),
                    'thirty_to_50_th'       => (int) ($row['thirtyTo50Th'] ?? 0),
                    'more_than_50_th'       => (int) ($row['moreThan50Th'] ?? 0),

                    'phd'                   => (int) ($row['phd'] ?? 0),
                    'postgraduate'          => (int) ($row['postgraduate'] ?? 0),
                    'undergraduate'         => (int) ($row['undergraduate'] ?? 0),
                    'diploma'               => (int) ($row['diploma'] ?? 0),
                    'high_school'           => (int) ($row['highSchool'] ?? 0),
                    'junior_high_school'    => (int) ($row['juniorHighSchool'] ?? 0),
                    'elementary_school'     => (int) ($row['elementarySchool'] ?? 0),
                    'others'                => (int) ($row['others'] ?? 0),

                    'islam'                 => (int) ($row['islam'] ?? 0),
                    'kristen'               => (int) ($row['kristen'] ?? 0),
                    'katolik'               => (int) ($row['katolik'] ?? 0),
                    'hindu'                 => (int) ($row['hindu'] ?? 0),
                    'budha'                 => (int) ($row['budha'] ?? 0),
                    'konghucu'              => (int) ($row['konghucu'] ?? 0),

                    'employee_total'        => (int) ($row['employeeTotal'] ?? 0),
                    'total'                 => (int) ($row['total'] ?? 0),

                    'created_utc_date'      => isset($row['createdUtcDate'])
                        ? Carbon::parse($row['createdUtcDate'])
                        : null,

                    'modified_utc_date'     => isset($row['modifiedUtcDate'])
                        ? Carbon::parse($row['modifiedUtcDate'])
                        : null,

                    'updated_at'            => now(),
                ];

                $exists = $db->table('data_csr')
                    ->where('reff_id', $reffId)
                    ->exists();

                if ($exists) {

                    $db->table('data_csr')
                        ->where('reff_id', $reffId)
                        ->update($payload);

                    $updated++;

                } else {

                    $payload['reff_id']   = $reffId;
                    $payload['created_at'] = now();

                    $db->table('data_csr')->insert($payload);

                    $inserted++;
                }

            } catch (\Exception $e) {

                Log::error("❌ CSR sync error", [
                    'company_id' => $companyId,
                    'message'    => $e->getMessage(),
                    'row'        => $row
                ]);

                $skipped++;
            }
        }

        Log::info("✅ CSR Sync Completed", [
            'company_id' => $companyId,
            'inserted'   => $inserted,
            'updated'    => $updated,
            'skipped'    => $skipped,
        ]);

        return compact('inserted', 'updated', 'skipped');
    }
}