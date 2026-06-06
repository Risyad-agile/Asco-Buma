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
                    'organization'          => trim($row['organization']),
                    'account_number'        => trim($row['accountNumber']),
                    'year'                  => $row['year'] ?? null,
                    'month'                 => $row['month'] ?? null,

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

    public function syncTRN(array $data, int $companyId): array
    {
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

                // 🔥 composite key
                $accountNumber   = trim($row['accountNumber'] ?? '');
                $personnelNumber = trim($row['personnelNumber'] ?? '');
                $courseName      = trim($row['courseTrainingName'] ?? '');

                if (!$accountNumber || !$personnelNumber || !$courseName) {
                    $skipped++;
                    continue;
                }

                $payload = [
                    'organization'          => trim($row['organization'] ?? ''),
                    'location'              => trim($row['location'] ?? ''),
                    'account_style'         => trim($row['accountStyle'] ?? ''),
                    'account_number'        => $accountNumber,

                    'personnel_number'      => $personnelNumber,
                    'name'                  => trim($row['name'] ?? ''),

                    'level'                 => (int) ($row['level'] ?? 0),
                    'gender'                => trim($row['gender'] ?? ''),
                    'age_category'          => trim($row['ageCategory'] ?? ''),

                    'course_training_name'  => $courseName,
                    'total_hours'           => (int) ($row['totalHours'] ?? 0),
                    'total'                 => (int) ($row['total'] ?? 0),

                    // 🔥 pakai helper aman
                    'created_utc_date'      => $this->parseDate($row['createdUtcDate'] ?? null),
                    'modified_utc_date'     => $this->parseDate($row['modifiedUtcDate'] ?? null),
                    'start_date'            => $row['startDate'] ?? null,
                    'end_date'              => $row['endDate'] ?? null,

                    'updated_at'            => now(),
                ];

                // 🔥 cek existing
                $query = $db->table('data_trn')
                    ->where('account_number', $accountNumber)
                    ->where('personnel_number', $personnelNumber)
                    ->where('course_training_name', $courseName);

                if ($query->exists()) {

                    $query->update($payload);
                    $updated++;
                } else {

                    $payload['created_at'] = now();

                    $db->table('data_trn')->insert($payload);
                    $inserted++;
                }
            } catch (\Exception $e) {

                Log::error("❌ TOC sync error", [
                    'company_id' => $companyId,
                    'message'    => $e->getMessage(),
                    'row'        => $row
                ]);

                $skipped++;
            }
        }

        Log::info("✅ TRN Sync Completed", [
            'company_id' => $companyId,
            'inserted'   => $inserted,
            'updated'    => $updated,
            'skipped'    => $skipped,
        ]);

        return compact('inserted', 'updated', 'skipped');
    }

    public function syncTOC(array $data, int $companyId): array
    {
        // 🔥 set dynamic connection
        // dd($data);
        $this->connector->setDynamicConnection($companyId);

        $db = DB::connection('dynamic');

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        try {

            // $contents = $data['result']['content'] ?? [];
            $contents = $data; // already normalized flat array from fetchTOCFromSource

            if (empty($contents)) {
                // throw new \Exception("Data TOC kosong dari API");
                Log::warning("Data TOC kosong dari API", ['company_id' => $companyId]);
            }

            foreach ($contents as $content) {

                $jobSite      = $content['jobSite'] ?? null;
                $organization = $content['organization'] ?? null;
                $details      = $content['details'] ?? [];

                if (!$jobSite || !$organization || !is_array($details)) {
                    $skipped++;
                    continue;
                }

                // =========================
                // 🔥 LOOP CATEGORY
                // =========================
                foreach ($details as $category => $detailList) {

                    // handle kalau object bukan array
                    if (isset($detailList['problemCategory'])) {
                        $detailList = [$detailList];
                    }

                    if (!is_array($detailList)) continue;

                    foreach ($detailList as $detail) {

                        $problemCategory = $detail['problemCategory'] ?? '';
                        $location        = $detail['location'] ?? '';

                        // =========================
                        // 🔹 tipeBeneficiariesDetails
                        // =========================
                        foreach ($detail['tipeBeneficiariesDetails'] ?? [] as $benefit) {

                            $tipe = $benefit['tipeBeneficiaries'] ?? '';

                            if (!$tipe) {
                                $skipped++;
                                continue;
                            }

                            $payload = [
                                'job_site'            => $jobSite,
                                'organization'        => $organization,
                                'category'            => $category,
                                'problem_category'    => $problemCategory,
                                'location'            => $location,
                                'tipe_beneficiaries'  => $tipe,

                                'output'              => (int) ($benefit['output'] ?? 0),
                                'outcome'             => (int) ($benefit['outcome'] ?? 0),
                                'outcome_additional'  => isset($benefit['outcomeAdditional'])
                                    ? (int) $benefit['outcomeAdditional']
                                    : null,

                                'output_event'        => null,
                                'output_all'          => null,

                                'updated_at'          => now(),
                            ];

                            $query = $db->table('data_toc')
                                ->where('job_site', $jobSite)
                                ->where('category', $category)
                                ->where('problem_category', $problemCategory)
                                ->where('location', $location)
                                ->where('tipe_beneficiaries', $tipe);

                            if ($query->exists()) {

                                $query->update($payload);
                                $updated++;
                            } else {

                                $payload['created_at'] = now();

                                $db->table('data_toc')->insert($payload);
                                $inserted++;
                            }
                        }

                        // =========================
                        // 🔹 othersDetails
                        // =========================
                        foreach ($detail['othersDetails'] ?? [] as $other) {

                            $tipe = $other['tipeBeneficiaries'] ?? 'Others';

                            $payload = [
                                'job_site'            => $jobSite,
                                'organization'        => $organization,
                                'category'            => $category,
                                'problem_category'    => $problemCategory,
                                'location'            => $location,
                                'tipe_beneficiaries'  => $tipe,

                                'output'              => null,
                                'outcome'             => null,
                                'outcome_additional'  => null,

                                'output_event'        => (int) ($other['outputEvent'] ?? 0),
                                'output_all'          => (int) ($other['outputAll'] ?? 0),

                                'updated_at'          => now(),
                            ];

                            $query = $db->table('data_toc')
                                ->where('job_site', $jobSite)
                                ->where('category', $category)
                                ->where('problem_category', $problemCategory)
                                ->where('location', $location)
                                ->where('tipe_beneficiaries', $tipe);

                            if ($query->exists()) {

                                $query->update($payload);
                                $updated++;
                            } else {

                                $payload['created_at'] = now();

                                $db->table('data_toc')->insert($payload);
                                $inserted++;
                            }
                        }
                    }
                }

                // =========================
                // 🔥 SAVE SUMMARY
                // =========================
                $summary = $content['summary'] ?? [];
                $pillars = collect($summary['pillarCostDetail'] ?? [])
                    ->keyBy('pillarName');

                $summaryPayload = [
                    'job_site'                  => $jobSite,
                    'organization'              => $organization,
                    'location'                  => $content['siteShortName'] ?? '',
                    'total_cost_planning'       => $summary['totalCostPlanning'] ?? 0,
                    'total_cost_implementation' => $summary['totalCostImplementation'] ?? 0,
                    'pillar_socio_culture'      => $pillars['Socio-Cultural & Religious']['pillarCost'] ?? 0,
                    'pillar_infra_env'          => $pillars['Infrastucture & Environmental Development']['pillarCost'] ?? 0,
                    'pillar_education_health'   => $pillars['Education & Health']['pillarCost'] ?? 0,
                    'pillar_economic_dev'       => $pillars['Economic Development']['pillarCost'] ?? 0,
                    'total_planning'            => (int) ($summary['totalPlanning'] ?? 0),
                    'total_impacted_plan'       => (int) ($summary['totalImpactedPlan'] ?? 0),
                    'total_impacted_real'       => (int) ($summary['totalImpactedReal'] ?? 0),
                    'total_implementation'      => (int) ($summary['totalImplementation'] ?? 0),
                    'total_observation'         => (int) ($summary['totalObservation'] ?? 0),
                    'updated_at'                => now(),
                ];

                $exists = $db->table('data_toc_summary')
                    ->where('job_site', $jobSite)
                    ->exists();

                if ($exists) {
                    $db->table('data_toc_summary')
                        ->where('job_site', $jobSite)
                        ->update($summaryPayload);
                } else {
                    $summaryPayload['created_at'] = now();
                    $db->table('data_toc_summary')->insert($summaryPayload);
                }
            }

            Log::info("✅ TEC Sync Completed", [
                'company_id' => $companyId,
                'inserted'   => $inserted,
                'updated'    => $updated,
                'skipped'    => $skipped,
            ]);

            return compact('inserted', 'updated', 'skipped');
        } catch (\Throwable $e) {

            Log::error("❌ TOC Sync FAILED", [
                'company_id' => $companyId,
                'message'    => $e->getMessage(),
                'line'       => $e->getLine(),
            ]);

            throw $e;
        }
    }

    public function syncSHE(array $data, int $companyId): array
    {
        $this->connector->setDynamicConnection($companyId);
        $db = DB::connection('dynamic');

        $inserted = 0;
        $updated  = 0;
        $skipped  = 0;

        try {
            if (empty($data)) {
                Log::warning("Data SHE kosong dari API", ['company_id' => $companyId]);
                return compact('inserted', 'updated', 'skipped');
            }

            foreach ($data as $row) {
                $remoteId = $row['id'] ?? null;
                if (!$remoteId) {
                    $skipped++;
                    continue;
                }

                $payload = [
                    'remote_id'             => $remoteId,
                    'organization'          => $row['organization'] ?? null,
                    'location'              => $row['location'] ?? null,
                    'account_style_caption' => $row['accountStyleCaption'] ?? null,
                    'account_number'        => $row['accountNumber'] ?? null,
                    'site_id'               => $row['siteId'] ?? null,
                    'month'                 => $row['month'] ?? null,
                    'year'                  => $row['year'] ?? null,
                    'trir'                  => $row['trir'] ?? null,
                    'ltifr'                 => $row['ltifr'] ?? null,
                    'count_incident'        => $row['countIncident'] ?? null,
                    'count_accident'        => $row['countAccident'] ?? null,
                    'ceiling_trir'          => $row['ceilingTrir'] ?? null,
                    'ceiling_ltifr'         => $row['ceilingLtifr'] ?? null,
                    'last_load_utc_date'    => isset($row['lastLoadUtcDate'])
                        ? \Carbon\Carbon::parse($row['lastLoadUtcDate'])->format('Y-m-d H:i:s')
                        : null,
                    'updated_at'            => now(),
                ];

                $exists = $db->table('data_she')
                    ->where('remote_id', $remoteId)
                    ->exists();

                if ($exists) {
                    $db->table('data_she')
                        ->where('remote_id', $remoteId)
                        ->update($payload);
                    $updated++;
                } else {
                    $payload['created_at'] = now();
                    $db->table('data_she')->insert($payload);
                    $inserted++;
                }
            }

            Log::info("✅ SHE Sync Completed", [
                'company_id' => $companyId,
                'inserted'   => $inserted,
                'updated'    => $updated,
                'skipped'    => $skipped,
            ]);

            return compact('inserted', 'updated', 'skipped');
        } catch (\Throwable $e) {
            Log::error("❌ SHE Sync FAILED", [
                'company_id' => $companyId,
                'message'    => $e->getMessage(),
                'line'       => $e->getLine(),
            ]);
            throw $e;
        }
    }

    private function parseDate($value)
    {
        try {

            if (empty($value)) {
                return null;
            }

            $date = Carbon::parse($value);

            // 🔥 handle tanggal aneh dari .NET (0001)
            if ($date->year < 1000) {
                return null;
            }

            // optional: convert ke WIB
            return $date
                ->setTimezone('Asia/Jakarta')
                ->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }
}
