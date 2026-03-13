<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ConnectorService;
use App\Models\Companies; 
use App\Models\Connector; 
use App\Models\Organization;
use App\Models\Syncronize;
use App\Models\Location;
use App\Models\AccountStyleCompanies;
use GuzzleHttp\Client;
use Config;


class LocationController extends Controller
{
    protected $savedLocations = [];
    // protected $connectorService;

    public function __construct(ConnectorService $connectorService)
    {
        $this->middleware('auth');
        $this->connectorService = $connectorService;
    }


    // ====================== Helper Auth Info ======================
    public function orgid() { return Auth::user()->company->organization->id; }
    public function compid() { return Auth::user()->company->id; }

    // ====================== Browse UI ======================
    public function browse()
    {
        $user = auth()->user();
        $company = ($user->role != 'superadmin') ? $user->company : null;
        return view('location.browse', compact('company', 'user'));
    }

    // ====================== Combo Organization ======================
    // ======================
    // COMBO ORGANIZATION (PER FITUR LOCATION)
    // ======================
    public function comboOrg()
    {
        $user = auth()->user(); 
        // SUPERADMIN bisa lihat semua organization
        if ($user->hasRole('superadmin')) {
            $orgs = Organization::orderBy('org_name')->get(['id','org_name']);
        } 
        // USER BIASA hanya org milik company-nya
        else {
            $orgs = Organization::whereHas('company', function($q) use ($user){
                $q->where('id', $user->comp_id);
            })
            ->orderBy('org_name')
            ->get(['id','org_name']);
        }

        return response()->json($orgs);
    }


    // ====================== List Location ======================
    public function list(Request $request)
    {
        $orgId = $request->org_id;

        if (!$orgId) {
            return response()->json([]);
        }

        $org = Organization::with('company')->find($orgId);
        if (!$org || !$org->company) {
            return response()->json([]);
        }

        // 🔥 pakai ConnectorService untuk switch DB
        $this->connectorService->setDynamicConnection($org->company->id);

        $query = DB::connection('dynamic')->table('locations');

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('location_name', 'like', '%'.$request->search.'%')
                ->orWhere('location_id', 'like', '%'.$request->search.'%');
            });
        }

        return response()->json($query->get());
    }


    // public function list(Request $request)
    // {
    //     $query = Location::with('organization');

    //     if($request->org_id) { $query->where('org_id', $request->org_id); }
    //     if($request->search){
    //         $query->where(function($q) use($request){
    //             $q->where('location_name','like','%'.$request->search.'%')
    //               ->orWhere('location_id','like','%'.$request->search.'%');
    //         });
    //     }

    //     return response()->json($query->get());
    // }

    // ====================== Sync Location + Account ======================

    public function store(Request $request)
    {
        try {
            // ================= VALIDATION =================
            $request->validate([
                'orgid' => 'required|exists:organizations,id'
            ]);

            $orgid = $request->orgid;

            // ================= LOAD ORGANIZATION + COMPANY =================
            $organization = Organization::with('company')->find($orgid);
            if(!$organization){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Organization not found',
                    'code' => 404
                ]);
            }

            $company = $organization->company;
            if(!$company){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Company not found for this organization',
                    'code' => 404
                ]);
            }

            // ================= FIND CONNECTOR =================
            $connector = Connector::where([
                ['comp_id', $company->id],
                ['connect_type', 'ENVIZI'],
                ['connect_protocol', 'API']
            ])->first();

            if(!$connector || empty($connector->connect_token_value)){
                return response()->json([
                    'status' => 'error',
                    'message' => "Can't find Token Bearer Key",
                    'code' => 404
                ]);
            }

            // ================= PREPARE API =================
            $token  = $connector->connect_token_value;
            // $token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsIng1dCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSIsImtpZCI6IkNOdjBPSTNSd3FsSEZFVm5hb01Bc2hDSDJYRSJ9.eyJhdWQiOiJhcGk6Ly83ZmZlNmEwNi03Mzg3LTQzNzQtYjI3OS0wMDQ5MWQwODA3YWUiLCJpc3MiOiJodHRwczovL3N0cy53aW5kb3dzLm5ldC9hZTY2MjdhZC00YjIwLTQ2NmQtODIxYy1mZDllZTdhMjVhNTUvIiwiaWF0IjoxNzQ3MTQxOTI2LCJuYmYiOjE3NDcxNDE5MjYsImV4cCI6MTc0NzE0OTQyNiwiYWNyIjoiMSIsImFpbyI6IkFaUUFhLzhaQUFBQUNEUTJ4NkVqaUNTZUpITVNxeVk3akZqL1V6ODFWWVNXUVRiallpaklxTWxOMXFBQndJOUdIaHdabUk3QzR2Zm10bjlrSE5KNUozMFZ6QWZBZk5DeWxYMm9rZkg4UHN0UXk0dXlNNEdYNGl0MjRzcGF0VThoTjJpRDhYZUxidmNUeEpPdER1TTRQWUcrV0dkbWJpWC8rWW1McFQ4bTdmNXhhMVZKcU8xL0p2TXZHU29nU1Vid2g5VW80ZlhsYWx5NiIsImFtciI6WyJwd2QiXSwiYXBwaWQiOiIwOTdiZWU3Zi0yNmQ0LTQ4ZjUtYmEzMS0zYWRjMDZjZGMwMWIiLCJhcHBpZGFjciI6IjAiLCJlbWFpbCI6ImJhbWJhbmcuemlkbnlAZ21haWwuY29tIiwiaWRwIjoibGl2ZS5jb20iLCJpcGFkZHIiOiIxNDAuMjEzLjE0MC4zMSIsIm5hbWUiOiJiYW1iYW5nLnppZG55QGdtYWlsLmNvbSIsIm9pZCI6IjZmMTIwOGMzLTE3NmEtNGIwNy1iZDUyLWYyOThlZDFhZjI5NSIsInJoIjoiMS5BWElBclNkbXJpQkxiVWFDSFAyZTU2SmFWUVpxX24tSGMzUkRzbmtBU1IwSUI2NXlBQ0J5QUEuIiwic2NwIjoiZ2VuZXJhbF9hcGkiLCJzaWQiOiIwMDRlNGVmOS03OTE5LWRiOTUtYjc5Ni1mMTk5MDVmNjUyYjciLCJzdWIiOiJTSFlBU2RIamkzTERMcUF5Q0k4RHRTWm1lUHhMWUJnMUNISUREVHo5blpFIiwidGlkIjoiYWU2NjI3YWQtNGIyMC00NjZkLTgyMWMtZmQ5ZWU3YTI1YTU1IiwidW5pcXVlX25hbWUiOiJsaXZlLmNvbSNiYW1iYW5nLnppZG55QGdtYWlsLmNvbSIsInV0aSI6Im1PNmpXTmdYMWtlckE2Zy1FUjRMQUEiLCJ2ZXIiOiIxLjAifQ.QorCJRh1vNDmEYsIUpkUq18XRCHQZTxP_bCU4Dt82TEpsR_gGobszESM3tc4udxpiULdqWO1Qox-KFbwXs4VxV_MWN2CiZ5xnI3R2G_mB5WjD5SjItP-aYQlAEeIRjmJIWFbRFuMkZ7DK3CG8GD4I3x3nsEV3GoA-tLyKe6nlFuiIbnAdBaQFmR4d3idB3t63E31wQoGL7j1Hb3CLG_rDAABzLsBRib6On6-JfNx3ZbyboDg5dBNsIsbo94hObqwIt20Tup0DcvFAJdj3JlJqYm75QtIa_fJWiI7vf9W42VmBIFdjLp8yiczWgE0-J-E_Yahyhw601RTN3JOw8cVYw";
            $url    = Config::get('asri.envizi.api_address.au_apac.url').'reports/';
            $method = Config::get('asri.envizi.reports.AccountStyleDataExtract');

            $client = new \GuzzleHttp\Client([
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
            $result   = json_decode($response->getBody()->getContents());

            if(empty($result)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Empty response from ENVIZI',
                    'code' => 500
                ]);
            }

            // ================= PROCESS RESPONSE =================
            
            foreach($result as $rs){
                if(!isset($rs->params)) continue;

                foreach($rs->params as $param){

                    if($param->name == "Location_Id" && !empty($param->availableValues)){
                        foreach($param->availableValues as $availableValue){
                            $this->saveLocation(
                                $organization->org_name,
                                $availableValue->value,
                                $availableValue->name
                            );
                        }
                    }

                    if($param->name == "Filter_By" && !empty($param->availableValues)){
                        foreach($param->availableValues as $availableValue){
                            $this->saveAccountStyleCompany(
                                $company->id,
                                $availableValue->value,
                                $availableValue->name
                            );
                        }
                    }
                }
            }

            // ================= LOG SYNC =================
            $sync = new Syncronize;
            $sync->comp_id = $company->id;
            $sync->connect_id = $connector->id;
            $sync->sync_task_name = "Synchronize Location";
            $sync->sync_state_note = 'SUCCESS Synchronize Location and Account Style using '.$connector->connect_name;
            $sync->sync_time = now();
            $sync->save();

            // ================= RETURN =================
            return response()->json([
                'status' => 'success',
                'message' => 'Data successfully saved',
                'code' => 200
            ]);

        } catch(\GuzzleHttp\Exception\RequestException $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'ENVIZI API Error: '.$e->getMessage(),
                'code' => 500
            ]);

        } catch(\Exception $e){

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => 500
            ]);
        }
    }

    // ====================== Private Save Location ======================
    private function saveLocation($orgname, $locid, $locname, $data=[])
    {
        // dd($orgname,$locid,$locname,$data);
        $locname = trim($locname);
        if($locname=="All Locations") return;

        $key = strtolower($orgname.'|'.$locname);
        if(isset($this->savedLocations[$key])) return;
        $this->savedLocations[$key] = true;

        $location = Location::where('org_name',$orgname)
            ->whereRaw('LOWER(TRIM(location_name)) = ?', [strtolower($locname)])
            ->first();

        if(!$location){
            Location::create([
                'org_name'=>$orgname,
                'group_type'=>$data['group_type']??'Classification',
                'group_name1'=>$data['group_name1']??'DEFAULT_G1',
                'group_name2'=>$data['group_name2']??'DEFAULT_G2',
                'group_name3'=>$data['group_name3']??'DEFAULT_G3',
                'location_name'=>$locname,
                'location_id'=>$locid,
            ]);
        } else if(empty($location->location_id) && !empty($locid)){
            $location->update(['location_id'=>$locid]);
        }
    }

    // ====================== Private Save Account Style ======================
    private function saveAccountStyleCompany($compid,$accstylelink,$accstylecaption)
    {
        $accstyle = AccountStyleCompanies::where('acc_style_comp_link',$accstylelink)->first();
        if($accstyle) return;

        $accstyle = new AccountStyleCompanies;
        $accstyle->comp_id = $compid;
        $accstyle->acc_style_comp_link = $accstylelink;
        $accstyle->acc_style_comp_caption = $accstylecaption;
        $accstyle->save();
    }

    // public function store(Request $request)
    // {
    //     try {
    //         $orgid = $request->orgid;
    //         $organization = Organization::with('company')->find($orgid);
    //         if(!$organization) return response()->json(['status'=>'error','message'=>'Organization not found','code'=>404]);

    //         $company = $organization->companies()->first(); // ambil company pertama
    //         $connector = Connector::where([['comp_id',$company->id],['connect_type','ENVIZI'],['connect_protocol','API']])->first();
    //         if(!$connector || empty($connector->connect_token_value))
    //             return response()->json(['status'=>'error','message'=>"Can't find Token Bearer Key",'code'=>404]);

    //         $token = $connector->connect_token_value;
    //         $url = Config::get('asri.envizi.api_address.au_apac.url').'reports/';
    //         $methode = Config::get('asri.envizi.reports.AccountStyleDataExtract');

    //         $client = new Client([
    //             'base_uri'=>$url,
    //             'headers'=>[
    //                 'Authorization'=>'Bearer '.$token,
    //                 'Accept'=>'*/*',
    //                 'Content-Type'=>'application/json',
    //                 'Accept-Encoding'=>'gzip, deflate, br',
    //             ],
    //         ]);

    //         $response = $client->request('GET',$methode);
    //         $result = json_decode($response->getBody()->getContents());

    //         foreach($result as $rs){
    //             foreach($rs->params as $param){
    //                 if($param->name=="Location_Id"){
    //                     foreach($param->availableValues as $availableValue){
    //                         $this->saveLocation($organization->org_name,$availableValue->value,$availableValue->name);
    //                     }
    //                 }
    //                 if($param->name=="Filter_By"){
    //                     foreach($param->availableValues as $availableValue){
    //                         $this->saveAccountStyleCompany($company->id,$availableValue->value,$availableValue->name);
    //                     }
    //                 }
    //             }
    //         }

    //         $sync = new Syncronize;
    //         $sync->comp_id = $company->id;
    //         $sync->connect_id = $connector->id;
    //         $sync->sync_task_name = "Sycronize Location";
    //         $sync->sync_state_note = 'SUCCESS Sycronize Location and Account Style using '.$connector->connect_name;
    //         $sync->sync_time = now();
    //         $sync->save();

    //         return response()->json(['status'=>'success','message'=>'Data successfully Saved','code'=>200]);

    //     } catch(\Exception $e){
    //         return response()->json(['status'=>'error','message'=>$e->getMessage(),'code'=>500]);
    //     }
    // }
}
