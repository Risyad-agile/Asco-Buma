<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\ConnectorSource;
use App\Models\ConnectorTarget;
use App\Models\Companies; 

class ConnectorSourceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compid(){
        return Auth::user()->organization->company->id;
    }

    public function browse(Request $request)
    {
        // dd($request->all());
        // $connector = ConnectorSource::with('company')->latest()->get();
        $connector = ConnectorSource::with('company')->orderBy('id', 'asc')->get();
        $companies = Companies::select('id as comp_id', 'comp_name')->get(); 
        return view('connector_source.browse', compact('connector', 'companies'));
    }

    public function create(Request $request)
    {
        $comp_id = $request->query('comp_id');

        // 🔹 ambil daftar target aktif untuk dropdown "Destination Type"
        $targets = ConnectorTarget::where('is_active', 1)
            ->orderBy('conn_target_name')
            ->get();

        return view('connector_source.create', compact('comp_id', 'targets'));
    }

    // 🔽 Tambahkan method ini
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $compId    = $request->input('comp_id');
            $targetId  = $request->input('conn_target_id');
            $name      = $request->input('connect_name');
            $type      = $request->input('connect_type');
            $localTable = $request->input('local_table'); // <-- ambil dari input teks

            // 🔧 siapkan konfigurasi flat (tanpa connect_protocol)
            $config = [];

            switch ($type) {
                case 'API_KEY':
                    $config = [
                        'api_method'      => $request->input('api_method'),
                        'api_base_url'    => $request->input('api_base_url'),
                        'api_endpoint'    => $request->input('api_endpoint'),
                        'api_key_name'    => $request->input('api_key_name'),
                        'api_key_value'   => $request->input('api_key_value'),
                        'api_body'        => $request->input('api_body'),
                    ];
                    break;

                case 'BEARER_TOKEN':
                    $config = [
                        'api_method'      => $request->input('api_method'),
                        'api_base_url'    => $request->input('api_base_url'),
                        'api_endpoint'    => $request->input('api_endpoint'),
                        'api_token'       => $request->input('api_token'),
                        'api_body'        => $request->input('api_body'),
                    ];
                    break;

                case 'FTP':
                    $config = [
                        'ftp_host'  => $request->input('ftp_host'),
                        'ftp_port'  => $request->input('ftp_port'),
                        'ftp_user'  => $request->input('ftp_user'),
                        'ftp_pass'  => $request->input('ftp_pass'),
                        'ftp_path'  => $request->input('ftp_path'),
                    ];
                    break;

                case 'DB':
                    $config = [
                        'db_type'   => $request->input('db_type'),
                        'db_host'   => $request->input('db_host'),
                        'db_port'   => $request->input('db_port'),
                        'db_name'   => $request->input('db_name'),
                        'db_user'   => $request->input('db_user'),
                        'db_pass'   => $request->input('db_pass'),
                    ];
                    break;
            }

            // 🔧 destination
            if ($request->has('aws_key_id')) {
                $config = array_merge($config, [
                    'aws_key_id'        => $request->input('aws_key_id'),
                    'aws_key_secret'    => $request->input('aws_key_secret'),
                    'aws_remote_folder' => $request->input('aws_remote_folder'),
                ]);
            } elseif ($request->has('target_path')) {
                $config = array_merge($config, [
                    'target_path' => $request->input('target_path'),
                ]);
            }

            // 💾 simpan
            ConnectorSource::create([
                'comp_id'          => $compId,
                'conn_target_id'   => $targetId,
                'conn_source_name' => $name,
                'conn_source_type' => $type,
                'local_table'      => $localTable, // <-- simpan local_table di kolom
                'config_json'      => json_encode(array_filter($config, fn($v) => !is_null($v) && $v !== ''), JSON_PRETTY_PRINT),
                'is_active'        => true,
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }




    public function edit($id)
    {
        $connector = ConnectorSource::with('company')->findOrFail($id);
        $targets = ConnectorTarget::all();

        return view('connector_source.update', compact('connector', 'targets'));
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $conn = ConnectorSource::findOrFail($request->id);
            $type = $request->input('connect_type');
            $localTable = $request->input('local_table'); // <-- ambil dari request

            // 🔧 siapkan konfigurasi flat
            $config = [];

            switch ($type) {
                case 'API_KEY':
                    $config = [
                        'api_method'      => $request->input('api_method'),
                        'api_base_url'    => $request->input('api_base_url'),
                        'api_endpoint'    => $request->input('api_endpoint'),
                        'api_key_name'    => $request->input('api_key_name'),
                        'api_key_value'   => $request->input('api_key_value'),
                        'api_body'        => $request->input('api_body'),
                    ];
                    break;

                case 'BEARER_TOKEN':
                    $config = [
                        'api_method'      => $request->input('api_method'),
                        'api_base_url'    => $request->input('api_base_url'),
                        'api_endpoint'    => $request->input('api_endpoint'),
                        'api_token'       => $request->input('api_token'),
                        'api_body'        => $request->input('api_body'),
                    ];
                    break;

                case 'FTP':
                    $config = [
                        'ftp_host'  => $request->input('ftp_host'),
                        'ftp_port'  => $request->input('ftp_port'),
                        'ftp_user'  => $request->input('ftp_user'),
                        'ftp_pass'  => $request->input('ftp_pass'),
                        'ftp_path'  => $request->input('ftp_path'),
                    ];
                    break;

                case 'DB':
                    $config = [
                        'db_type'   => $request->input('db_type'),
                        'db_host'   => $request->input('db_host'),
                        'db_port'   => $request->input('db_port'),
                        'db_name'   => $request->input('db_name'),
                        'db_user'   => $request->input('db_user'),
                        'db_pass'   => $request->input('db_pass'),
                    ];
                    break;
            }

            // 🔧 destination
            if ($request->has('aws_key_id')) {
                $config = array_merge($config, [
                    'aws_key_id'        => $request->input('aws_key_id'),
                    'aws_key_secret'    => $request->input('aws_key_secret'),
                    'aws_remote_folder' => $request->input('aws_remote_folder'),
                ]);
            } elseif ($request->has('target_path')) {
                $config = array_merge($config, [
                    'target_path' => $request->input('target_path'),
                ]);
            }

            // 💾 update
            $conn->update([
                'comp_id'          => $request->comp_id,
                'conn_target_id'   => $request->conn_target_id,
                'conn_source_name' => $request->connect_name,
                'conn_source_type' => $type,
                'local_table'      => $localTable, // <-- simpan local_table
                'config_json'      => json_encode(array_filter($config, fn($v) => !is_null($v) && $v !== ''), JSON_PRETTY_PRINT),
                'is_active'        => $request->boolean('is_active'),
            ]);

            DB::commit();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }


    // public function update(Request $request)
    // {
    //     try {
    //         DB::beginTransaction();

    //         $conn = ConnectorSource::findOrFail($request->id);

    //         $config = [
    //             'connect_protocol' => $request->input('connect_protocol'),
    //         ];

    //         $type = $request->input('connect_type');

    //         switch ($type) {
    //             case 'API_KEY':
    //                 $config = array_merge($config, [
    //                     'api_method'      => $request->input('api_method'),
    //                     'api_base_url'    => $request->input('api_base_url'),
    //                     'api_endpoint'    => $request->input('api_endpoint'),
    //                     'api_key_name'    => $request->input('api_key_name'),
    //                     'api_key_value'   => $request->input('api_key_value'),
    //                     'api_body'        => $request->input('api_body'),
    //                 ]);
    //                 break;

    //             case 'BEARER_TOKEN':
    //                 $config = array_merge($config, [
    //                     'api_method'      => $request->input('api_method'),
    //                     'api_base_url'    => $request->input('api_base_url'),
    //                     'api_endpoint'    => $request->input('api_endpoint'),
    //                     'api_token'       => $request->input('api_token'),
    //                     'api_body'        => $request->input('api_body'),
    //                 ]);
    //                 break;

    //             case 'FTP':
    //                 $config = array_merge($config, [
    //                     'ftp_host'  => $request->input('ftp_host'),
    //                     'ftp_port'  => $request->input('ftp_port'),
    //                     'ftp_user'  => $request->input('ftp_user'),
    //                     'ftp_pass'  => $request->input('ftp_pass'),
    //                     'ftp_path'  => $request->input('ftp_path'),
    //                 ]);
    //                 break;

    //             case 'DB':
    //                 $config = array_merge($config, [
    //                     'db_type'   => $request->input('db_type'),
    //                     'db_host'   => $request->input('db_host'),
    //                     'db_port'   => $request->input('db_port'),
    //                     'db_name'   => $request->input('db_name'),
    //                     'db_user'   => $request->input('db_user'),
    //                     'db_pass'   => $request->input('db_pass'),
    //                 ]);
    //                 break;
    //         }

    //         // 🔧 destination
    //         if ($request->has('aws_key_id')) {
    //             $config = array_merge($config, [
    //                 'aws_key_id'        => $request->input('aws_key_id'),
    //                 'aws_key_secret'    => $request->input('aws_key_secret'),
    //                 'aws_remote_folder' => $request->input('aws_remote_folder'),
    //             ]);
    //         } elseif ($request->has('target_path')) {
    //             $config = array_merge($config, [
    //                 'target_path' => $request->input('target_path'),
    //             ]);
    //         }

    //         // 💾 update
    //         $conn->update([
    //             'comp_id'          => $request->comp_id,
    //             'conn_target_id'   => $request->conn_target_id,
    //             'conn_source_name' => $request->connect_name,
    //             'conn_source_type' => $type,
    //             'config_json'      => json_encode(array_filter($config, fn($v) => !is_null($v) && $v !== ''), JSON_PRETTY_PRINT),
    //             'is_active'        => $request->boolean('is_active'),
    //         ]);

    //         DB::commit();

    //         return response()->json(['success' => true]);
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    // public function update(Request $request)
    // {
    //     $conn = ConnectorSource::findOrFail($request->id);

    //     $conn->update([
    //         'comp_id'          => $request->comp_id,
    //         'conn_target_id'   => $request->conn_target_id,
    //         'conn_source_name' => $request->connect_name,
    //         'conn_source_type' => $request->connect_type,
    //         'config_json'      => json_encode($request->except(['_token', 'id', 'comp_id', 'conn_target_id', 'connect_name', 'connect_type'])),
    //         'is_active'        => $request->is_active ?? true,
    //     ]);

    //     return response()->json(['success' => true]);
    // }

    public function setStatus(Request $request)
    {
        $conn = ConnectorSource::find($request->id);
        if(!$conn){
            return response()->json(['error' => 'Connector not found'], 404);
        }

        $conn->is_active = !$conn->is_active;
        $conn->save();

        return response()->json(['new_status' => $conn->is_active]);
    }


    public function testAPI(Request $req)
    {
        \Log::info('TEST API HIT', $req->all());
        $url = rtrim($req->base_url, '/') . '/' . ltrim($req->endpoint, '/');
        $method = strtoupper($req->method ?? 'GET');

        // siapkan headers dasar
        $headers = [
            'Content-Type' => 'application/json'
        ];

        // tambahkan header opsional
        if (!empty($req->api_token)) {
            $headers['Authorization'] = 'Bearer ' . $req->api_token;
        }
        if (!empty($req->api_key_name) && !empty($req->api_key_value)) {
            $headers[$req->api_key_name] = $req->api_key_value;
        }

        // siapkan body (kalau POST/PUT)
        $body = [];
        if (!empty($req->api_body)) {
            try {
                $body = json_decode($req->api_body, true);
            } catch (\Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON body format.'
                ], 400);
            }
        }

        try {
            $client = new Client(['timeout' => 20]);
            $options = ['headers' => $headers];
            if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
                $options['json'] = $body;
            }

            $response = $client->request($method, $url, $options);

            return response()->json([
                'status' => 'success',
                'code' => $response->getStatusCode(),
                'data' => json_decode($response->getBody(), true)
            ]);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getResponse()->getBody()->getContents(),
                'code' => $e->getResponse()->getStatusCode()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
