<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use App\Models\Location;
use App\Models\LocationsPartner;
use App\Exports\LocationsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Companies;
use App\Models\Connector;
use App\Models\DataEnv;

class APISyncronize extends Controller
{
    public function syncLocations()
    {
        echo "Mulai sync locations...\n";

        // 1. Ambil data dari provider, tambahkan API Key
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('ADVICE_API_KEY'),
        ])->get('http://127.0.0.1:8000/api/locations');

        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal ambil data dari provider'
            ], 500);
        }

        $locations = $response->json('data', []); 
        $company = Companies::findOrFail(1); // buma 
        // dd(count($locations), $locations);
        // dd($locations);
        $ids = collect($locations)->pluck('id')->toArray();
        // dd($ids);
        Location::whereNotIn('reff_id', $ids)->delete(); 
            
        // 3. Simpan ke tabel locations   

        foreach ($locations as $loc) {
            // dd($loc);
            Location::updateOrCreate(
                ['reff_id' => $loc['id']],
                [  
                    'org_name'               => $loc['org_name'] ?? null,
                    'group_type'             => $loc['group_type'] ?? null,
                    'group_hierarchy_name'   => $loc['group_hierarchy_name'] ?? null,
                    'group_name1'            => $loc['group_name1'] ?? null,
                    'group_name2'            => $loc['group_name2'] ?? null,
                    'group_name3'            => $loc['group_name3'] ?? null,
                    'location_name'          => $loc['location_name'] ?? null,
                    'location_type'          => $loc['location_type'] ?? null,
                    'location_reff'          => $loc['location_reff'] ?? null,
                    'location_reff_no'       => $loc['location_reff_no'] ?? null,
                    'location_id'            => $loc['location_id'] ?? null,
                    'location_address'       => $loc['location_address'] ?? null,
                    'location_city'          => $loc['location_city'] ?? null,
                    'location_state_province'=> $loc['location_state_province'] ?? null,
                    'postal_code'            => $loc['postal_code'] ?? null,
                    'country'                => $loc['country'] ?? null,
                    'latitude_y'             => $loc['latitude_y'] ?? null,
                    'longitude_x'            => $loc['longitude_x'] ?? null,
                    'location_close_date'    => $loc['location_close_date'] ?? null,
                ]
            );
        }


        // 4. Export ke Excel dari DB dynamic dan upload ke S3
         
        $connector = Connector::where([
            ['comp_id', $company->id],
            ['connect_type', 'ENVIZI'],
            ['connect_protocol', 'S3']
        ])->first();

        if (!$connector) {
            return response()->json([
                'state' => 'error',
                'message' => "Connector for " . $company->comp_name . " Not Found, please contact Agile",
                'code' => 413
            ]);
        }
        $folder = rtrim($connector->connect_remote_folder, '/') . '/';
        $compId = str_pad($company->id, 2, '0', STR_PAD_LEFT);
        $fileName = 'Envizi_SetupConfig_' .$compId."_". now()->format('Ymd_His') . '.xlsx';
        $storagecode = $connector->connect_storage_code;
        // dd($folder, $fileName, $storagecode);
        // dd(Excel::store(new LocationsExport($company), $folder . $fileName, $storagecode));
        Excel::store(new LocationsExport($company), $folder . $fileName, $storagecode); // save in S3 Server    
        //    
        Excel::store(new LocationsExport($company), 'exports/' . $fileName); // save in local storage/app/exports --> buat cek saja

        return response()->json([
            'status' => 'success',
            'message' => 'Sinkronisasi locations selesai',
            'count'   => count($locations),
        ]);
    }



    // VERSI MULTIDABASE & DATA MIRROR ==============================================================
    // public function syncLocations()
    // {
    //     echo "Mulai sync locations...\n";

    //     // 1. Ambil data dari provider, tambahkan API Key
    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . env('ADVICE_API_KEY'),
    //     ])->get('http://127.0.0.1:8000/api/locations');

    //     if ($response->failed()) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Gagal ambil data dari provider'
    //         ], 500);
    //     }

    //     $locations = $response->json('data', []);
    //     // dd($locations);
    //     // 2. Ambil konfigurasi database dari company
    //     $company = Companies::findOrFail(1);
    //     // dd($company);
    //     Config::set("database.connections.dynamic", [
    //         "driver"   => "mysql",
    //         "host"     => $company->db_host,
    //         "database" => $company->db_name,
    //         "username" => $company->db_user,
    //         "password" => $company->db_pass,
    //         "charset"  => "utf8mb4",
    //         "collation"=> "utf8mb4_general_ci",
    //     ]);


    //     $ids = collect($locations)->pluck('id')->toArray();

    //     LocationsPartner::on('dynamic')
    //         ->whereNotIn('id', $ids)
    //         ->delete();

            
    //     // 3. Simpan ke tabel mst_locations (consumer)
    //     foreach ($locations as $loc) {
    //         // dd($loc);
    //         LocationsPartner::on('dynamic')->updateOrCreate(
    //             // ['id' => $loc['id']],
    //             [
    //                 'id'                     => $loc['id'],
    //                 'org_name'               => $loc['org_name'] ?? null,
    //                 'group_type'             => $loc['group_type'] ?? null,
    //                 'group_hierarchy_name'   => $loc['group_hierarchy_name'] ?? null,
    //                 'group_name1'            => $loc['group_name1'] ?? null,
    //                 'group_name2'            => $loc['group_name2'] ?? null,
    //                 'group_name3'            => $loc['group_name3'] ?? null,
    //                 'location_name'          => $loc['location_name'] ?? null,
    //                 'location_type'          => $loc['location_type'] ?? null,
    //                 'location_reff'          => $loc['location_reff'] ?? null,
    //                 'location_reff_no'       => $loc['location_reff_no'] ?? null,
    //                 'location_id'            => $loc['location_id'] ?? null,
    //                 'location_address'       => $loc['location_address'] ?? null,
    //                 'location_city'          => $loc['location_city'] ?? null,
    //                 'location_state_province'=> $loc['location_state_province'] ?? null,
    //                 'postal_code'            => $loc['postal_code'] ?? null,
    //                 'country'                => $loc['country'] ?? null,
    //                 'latitude_y'             => $loc['latitude_y'] ?? null,
    //                 'longitude_x'            => $loc['longitude_x'] ?? null,
    //                 'location_close_date'    => $loc['location_close_date'] ?? null,
    //             ]
    //         );
    //     }


    //     // 4. Export ke Excel dari DB dynamic dan upload ke S3
         
    //     $connector = Connector::where([
    //         ['comp_id', $company->id],
    //         ['connect_type', 'ENVIZI'],
    //         ['connect_protocol', 'S3']
    //     ])->first();

    //     if (!$connector) {
    //         return response()->json([
    //             'state' => 'error',
    //             'message' => "Connector for " . $company->comp_name . " Not Found, please contact Agile",
    //             'code' => 413
    //         ]);
    //     }
    //     $folder = rtrim($connector->connect_remote_folder, '/') . '/';
    //     $compId = str_pad($company->id, 2, '0', STR_PAD_LEFT);
    //     $fileName = 'Envizi_SetupConfig_' .$compId."_". now()->format('Ymd_His') . '.xlsx';
    //     $storagecode = $connector->connect_storage_code;
    //     Excel::store(new LocationsExport($company), $folder . $fileName, $storagecode); // save in S3 Server    
    //     //    
    //     Excel::store(new LocationsExport($company), 'exports/' . $fileName); // save in local storage/app/exports --> buat cek saja

    //     return response()->json([
    //         'status' => 'success',
    //         'message' => 'Sinkronisasi locations selesai',
    //         'count'   => count($locations),
    //     ]);
    // }


    // =====================================================================
    // 🔥 Tambahan baru: Sinkronisasi Data Environment dari API Client
    // =====================================================================
    public function syncDataEnv()
    {
        echo "Mulai sync data_env...\n";

        // 1. Ambil data dari API provider
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('ADVICE_API_KEY'),
        ])->get('http://127.0.0.1:8000/api/dataenv'); // <-- ubah ke endpoint asli client

        if ($response->failed()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal ambil data dari provider'
            ], 500);
        }

        $dataList = $response->json('result.content.data', []);
        $count = count($dataList);

        // 2. Simpan / update data ke tabel data_env
        foreach ($dataList as $item) {
            DataEnv::updateOrCreate(
                ['id' => $item['id']],
                [
                    'organization' => $item['organization'] ?? null,
                    'location' => $item['location'] ?? null,
                    'account_style_caption' => $item['accountStyleCaption'] ?? null,
                    'account_number' => $item['accountNumber'] ?? null,
                    'account_reference' => $item['accountReference'] ?? null,
                    'account_supplier' => $item['accountSupplier'] ?? null,
                    'record_start' => $item['recordStart'] ?? null,
                    'record_end' => $item['recordEnd'] ?? null,
                    'quantity' => $item['quantity'] ?? null,
                    'total_cost_incl_tax_local_currency' => $item['totalCostInclTaxInLocalCurrency'] ?? null,
                    'record_reference' => $item['recordReference'] ?? null,
                    'record_invoice_number' => $item['recordInvoiceNumber'] ?? null,
                    'record_data_quality' => $item['recordDataQuality'] ?? null,
                    'total' => $item['total'] ?? null,
                    'created_utc_date' => $item['createdUtcDate'] ?? null,
                    'modified_utc_date' => $item['modifiedUtcDate'] ?? null,
                ]
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Sinkronisasi data_env selesai',
            'count' => $count,
        ]);
    }


}
