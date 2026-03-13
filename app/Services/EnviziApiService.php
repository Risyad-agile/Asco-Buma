<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Companies;
use App\Models\Connector;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

class EnviziApiService
{

    public function getEnviziData($orgId)
    {
        // GET COMP_ID BASE ON ORG_ID 
        $rstOrg = DB::table('organizations')
                    ->select ('comp_id','org_name','org_link')
                    ->where ('id',$orgId) 
                    ->first();

        // GET COMPANY & CONFIG 
        // $company = DB::table('companies') 
        //          ->where ('id',$rstOrg->comp_id) 
        //          ->first();
        // $company = Companies::find($rstOrg->comp_id);
        // if(!$company){
        //     throw new \Exception("Company not found");
        // }  
        // === GET CONNECTOR BASE ON COMP_ID FROM ORGANIZATION =============
        $connector = Connector::where([
            ['comp_id', $rstOrg->comp_id],
            ['connect_type', 'ENVIZI'],
            ['connect_protocol', 'API']
        ])->first();

        if(!$connector || empty($connector->connect_token_value)){
            throw new \Exception("ENVIZI Token not found");
        }

        // ================= CONFIG =================
        $token  = $connector->connect_token_value; 
        $url    = Config::get('asco.envizi.api_address.us_apac.url').'reports/';
        $method = Config::get('asco.envizi.reports.AccountStyleDataExtract');
        // ================= HTTP CLIENT =================
        $client = new Client([
            'base_uri' => $url,
            'headers' => [
                'Authorization'   => 'Bearer '.$token,
                'Accept'          => '*/*',
                'Content-Type'    => 'application/json',
                'Accept-Encoding' => 'gzip, deflate, br',
            ],
        ]);

        // ================= CALL API =================
        $response = $client->request('GET', $method);

        $result = json_decode($response->getBody()->getContents());

        if(empty($result)){
            throw new \Exception("ENVIZI returned empty data");
        }

        return $result;
    }
}