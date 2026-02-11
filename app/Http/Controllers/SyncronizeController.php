<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Companies; 
use App\Models\Connector; 
use App\Models\Organizations;
use App\Models\Syncronize;
use App\Models\Locations;
use App\Models\AccountStyleCompanies;
use GuzzleHttp\Client;
use Config;

class SyncronizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function compid(){
        return Auth::user()->store->company->id;
    }
    public function index()
    {
        return view('syncronize/index');
    }
    public function listOpen(Request $request){
        $compid=$request->get('comp_id');
        $company=Companies::where('id',$compid)->first();

        return view('syncronize/list',compact('company'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sinc=Syncronize::with('company')->where('sync_state','1')->get();
        return $sinc;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function sincronizeOrganization(Request $request){
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first();
        $connector=Connector::where([['comp_id',$compid],['connect_type','ENVIZI'],['connect_protocol','API']])->first();
        $token = $connector->connect_token_value;
        if($token==""){
            return response()->json(['status' => 'error', 'message' => "Can't find Token Bearer Key", 'code' => 404]);
        }

        $url = Config::get('asri.envizi.api_address.au_apac.url');
        $methode=Config::get('asri.envizi.reports.AccountStyleDataExtract');

        try {
            $client=new Client([
                'base_uri'=>$url, 
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,   
                    'Accept'        => '*/*',
                    'Content-Type' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    // 'Connection' =>'keep-alive',
                ],
                // 'debug' => true,
            ]);
            $response = $client->request('GET',$url);

	        $result = json_decode($response->getBody()->getContents());

            foreach ($result->associates as $key => $associate) {
               $this->saveOrganization($compid,$associate->associate_Id,$associate->name);
            }
            $sync=new Syncronize;
            $sync->comp_id=$company->id;
            $sync->connect_id=$connector->id;
            $sync->sync_task_name="Sycronize Organization";
            $sync->sync_state_note='SUCCESS Syncronize using '.$connector->connect_name;
            $sync->sync_time=date("Y-m-d H:i:s");
            $sync->save();

	        return response()->json(['status' => 'success', 'message' => 'Data successfully Saved', 'code' => 200]);
		} catch (\Exception $e) { 
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
    }

    private function saveOrganization($compid,$orglink,$orgname){
        $org=Organizations::where('org_link',$orglink)->first();
        //jika ditemukan tidak usah disimpan
        if($org){
            return;
        }

        $org=new Organizations;
        $org->comp_id=$compid;
        $org->org_link=$orglink;
        $org->org_name=$orgname;
        $org->save();
    }

    public function sincronizeLocationAccount(Request $request){
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first();
        $connector=Connector::where([['comp_id',$compid],['connect_type','ENVIZI'],['connect_protocol','API']])->first();
        $token = $connector->connect_token_value;

        if($token==""){
            return response()->json(['status' => 'error', 'message' => "Can't find Token Bearer Key", 'code' => 404]);
        }

        $url = Config::get('asri.envizi.api_address.au_apac.url').'reports/';
        $methode=Config::get('asri.envizi.reports.AccountStyleDataExtract');

        try {
            $client=new Client([
                'base_uri'=>$url,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,   
                    'Accept'        => '*/*',
                    'Content-Type' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate, br',
                ],
            ]);
            $response = $client->request('GET',$methode);

	        $result = json_decode($response->getBody()->getContents());
            foreach ($result as $key => $rs) {
                foreach ($rs->params as $key => $param) {
                    if($param->name=="Location_Id"){
                        foreach ($param->availableValues as $key => $availableValue) {
                            $this->saveLocation($compid,$availableValue->value,$availableValue->name);
                        }
                    }
                    if($param->name=="Filter_By"){ 
                        foreach ($param->availableValues as $key => $availableValue) {
                            $this->saveAccountStyleCompany($compid,$availableValue->value,$availableValue->name);
                        }
                    }
                    
                }
             }
             $sync=new Syncronize;
             $sync->comp_id=$company->id;
             $sync->connect_id=$connector->id;
             $sync->sync_task_name="Sycronize Location and Account Style";
             $sync->sync_state_note='SUCCESS Sycronize Location and Account Style using '.$connector->connect_name;
             $sync->sync_time=date("Y-m-d H:i:s");
             $sync->save();

             return response()->json(['status' => 'success', 'message' => 'Data successfully Saved', 'code' => 200]);

            
		} catch (\Exception $e) {
			return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
    }
    private function saveLocation($compid,$locid,$locname){
        if($locname=="All Locations"){
            return;
        }
        
        //jika ditemukan tidak usah disimpan
        $loc=Locations::where('location_id',$locid)->first();
        if($loc){
            return;
        }

        $orgid=Organizations::where('comp_id',$compid)->first()->id;
        $loc=new Locations;
        $loc->org_id=$orgid;
        $loc->comp_id=$compid;
        $loc->location_id=$locid;
        $loc->location_name=$locname;
        $loc->save();
    }
    private function saveAccountStyleCompany($compid,$accstylelink,$accstylecaption){
        $accstyle=AccountStyleCompanies::where('acc_style_comp_link',$accstylelink)->first();
        //jika ditemukan tidak usah disimpan
        if($accstyle){
            return;
        }

        $accstyle=new AccountStyleCompanies;
        $accstyle->comp_id=$compid;
        $accstyle->acc_style_comp_link=$accstylelink;
        $accstyle->acc_style_comp_caption=$accstylecaption;
        $accstyle->save();
    }
}
