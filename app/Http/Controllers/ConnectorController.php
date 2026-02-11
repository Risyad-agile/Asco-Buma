<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Companies; 
use App\Models\Connector; 
use Session;
use Validator;

class ConnectorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compid(){
        return Auth::user()->organization->company->id;
    }
    public function index()
    {
        return view('connector/index');
    }

    public function listByCompany(Request $request)
    {
        $companyId = $request->company_id;

        $data = Connector::where('company_id', $companyId)
            ->select('id','connect_type','connect_name','connect_protocol','company_id')
            ->get();

        return response()->json($data);
    }


    public function listOpen(Request $request){
        $compid=$request->get('comp_id');
        $company=Companies::where('id',$compid)->first(); 
        $connector=Connector::where([['connect_state','1'],['comp_id',$compid]])->get();
        return view('connector/list',compact('company','connector'));
    }

    public function listOpenByCompanyId($id){
        $company=Companies::where('id',$id)->first(); 
        $connector=Connector::where([['connect_state','1'],['comp_id',$company->id]])->get();
        return view('connector/list',compact('company','connector'));
    }

    


    public function mainOpen(Request $request){
        $state=$request->state;
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first(); 
        if($state=="NEW"){
            return view('connector/protocols',compact('company'));
        }

        $id=$request->id;
        if($state=="UPDATE"){
            $connector=Connector::where('id',$id)->first(); 
            $protocol=$connector->connect_protocol;
            if($protocol=="API"){
                return view('connector/update_api',compact('connector'));
            }
            if($protocol=="S3"){
                return view('connector/update_s3',compact('connector'));
            }
            if($protocol=="FTP"){
                return view('connector/update_ftp',compact('connector'));
            }
        }
    }

    public function newProtocolOpen(Request $request){
        $protocol=$request->protocol;
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first(); 
        if($protocol=="API"){
            return view('connector/new_api',compact('company','protocol'));
        }
        if($protocol=="S3"){
            return view('connector/new_s3',compact('company','protocol'));
        }
        if($protocol=="FTP"){
            return view('connector/new_ftp',compact('company','protocol'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->get('form'), [
            'connect_name' => 'required|string',
        ],[
            'connect_name.required' => 'Please Fill Connection Name',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }

        $compid=$request->compid;
        $connect_name=$request->get('form')['connect_name'];
        $connect_type=$request->get('form')['connect_type'];  
        $connect_protocol=$request->connect_protocol;
        
    
        if($connect_type=="ENVIZI"){
            $one_envizi=Connector::where([['comp_id',$compid],['connect_type',$connect_type],['connect_protocol',$connect_protocol]])->count();
            if($one_envizi!=0){
                return response()->json(['status' => 'error', 'message' => "Envizi Connection for these company already exist", 'code' => 404]);
            }
            // $one_s3=Connector::where([['comp_id',$compid],['connect_type',$connect_type],['connect_protocol',$connect_protocol]])->count();
            // if($one_s3!=0){
            //     return response()->json(['status' => 'error', 'message' => "Envizi S3 Connection for these company already exist", 'code' => 404]);
            // }
        }

        $connect_username=isset($request->get('form')['connect_username'])?$request->get('form')['connect_username']:"";
        $connect_password=isset($request->get('form')['connect_password'])?$request->get('form')['connect_password']:"";
        $connect_url=isset($request->get('form')['connect_url'])?$request->get('form')['connect_url']:"";
        $connect_email=isset($request->get('form')['connect_email'])?$request->get('form')['connect_email']:"";
        $connect_body=isset($request->get('form')['connect_body'])?$request->get('form')['connect_body']:"";
        $connect_token_value=isset($request->get('form')['connect_token_value'])?$request->get('form')['connect_token_value']:"";
        $connect_storage_code=isset($request->get('form')['connect_storage_code'])?$request->get('form')['connect_storage_code']:"";
        $connect_access_key_id=isset($request->get('form')['connect_access_key_id'])?$request->get('form')['connect_access_key_id']:"";
        $connect_access_key_secret=isset($request->get('form')['connect_access_key_secret'])?$request->get('form')['connect_access_key_secret']:"";
        $connect_access_region=isset($request->get('form')['connect_access_region'])?$request->get('form')['connect_access_region']:"";
        $connect_remote_folder=isset($request->get('form')['connect_remote_folder'])?$request->get('form')['connect_remote_folder']:"";
        $connect_data_load_name=isset($request->get('form')['connect_data_load_name'])?$request->get('form')['connect_data_load_name']:"POCAccountSetupandDataLoad_mid.xlsx";
        $connect_data_load_name_csr=isset($request->get('form')['connect_data_load_name_csr'])?$request->get('form')['connect_data_load_name_csr']:"Account_Setup_and_Data_Load_CSR_mid.xlsx";

        $connector=new Connector;
        $connector->comp_id=$request->compid;
        $connector->connect_name=$request->get('form')['connect_name'];
        $connector->connect_type=$request->get('form')['connect_type'];  
        $connector->connect_protocol=$request->connect_protocol; 
        $connector->connect_username=$connect_username;  
        $connector->connect_password=$connect_password; 
        $connector->connect_url=$connect_url;
        $connector->connect_body=$connect_body;  
        $connector->connect_email=$connect_email;  
        $connector->connect_token_value=$connect_token_value;  
        $connector->connect_storage_code=$connect_storage_code;  
        $connector->connect_access_key_id=$connect_access_key_id; 
        $connector->connect_access_key_secret=$connect_access_key_secret; 
        $connector->connect_access_region=$connect_access_region;  
        $connector->connect_remote_folder=$connect_remote_folder;
        $connector->connect_data_load_name=$connect_data_load_name;  
        $connector->connect_data_load_name_csr=$connect_data_load_name_csr;  
        $connector->connect_state='1';  
        $connector->save(); 
        return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
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
        $connect_username=isset($request->get('form')['connect_username'])?$request->get('form')['connect_username']:"";
        $connect_password=isset($request->get('form')['connect_password'])?$request->get('form')['connect_password']:"";
        $connect_url=isset($request->get('form')['connect_url'])?$request->get('form')['connect_url']:"";
        $connect_email=isset($request->get('form')['connect_email'])?$request->get('form')['connect_email']:"";
        $connect_body=isset($request->get('form')['connect_body'])?$request->get('form')['connect_body']:"";
        $connect_token_value=isset($request->get('form')['connect_token_value'])?$request->get('form')['connect_token_value']:"";
        $connect_storage_code=isset($request->get('form')['connect_storage_code'])?$request->get('form')['connect_storage_code']:"";
        $connect_access_key_id=isset($request->get('form')['connect_access_key_id'])?$request->get('form')['connect_access_key_id']:"";
        $connect_access_key_secret=isset($request->get('form')['connect_access_key_secret'])?$request->get('form')['connect_access_key_secret']:"";
        $connect_access_region=isset($request->get('form')['connect_access_region'])?$request->get('form')['connect_access_region']:"";
        $connect_remote_folder=isset($request->get('form')['connect_remote_folder'])?$request->get('form')['connect_remote_folder']:"";
        $connect_data_load_name=isset($request->get('form')['connect_data_load_name'])?$request->get('form')['connect_data_load_name']:"POCAccountSetupandDataLoad_mid.xlsx";
        $connect_data_load_name_csr=isset($request->get('form')['connect_data_load_name_csr'])?$request->get('form')['connect_data_load_name_csr']:"Account_Setup_and_Data_Load_CSR_mid.xlsx";
        
        Connector::where('id',$id)->update([
            'connect_name'=>$request->get('form')['connect_name'],
            'connect_type'=>$request->get('form')['connect_type'],
            'connect_username'=>$connect_username,
            'connect_password'=>$connect_password,
            'connect_url'=>$connect_url,
            'connect_body'=>$connect_body,
            'connect_email'=>$connect_email,
            'connect_token_value'=>$connect_token_value,  
            'connect_storage_code'=>$connect_storage_code,  
            'connect_access_key_id'=>$connect_access_key_id, 
            'connect_access_key_secret'=>$connect_access_key_secret, 
            'connect_access_region'=>$connect_access_region,  
            'connect_remote_folder'=>$connect_remote_folder,
            'connect_data_load_name'=>$connect_data_load_name,  
            'connect_data_load_name_csr'=>$connect_data_load_name_csr,
        ]);
        return response()->json(['status' => 'success', 'message' => 'Data successfully updated', 'code' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    #---Pengaturan Koneksi untuk Partner/Company
    public function partnerConnectorList(){
        $compid=$this->compid();
        $company=Companies::where('id',$compid)->first(); 
        $connector=Connector::where([['connect_state','1'],['connect_type','!=','ENVIZI'],['comp_id',$compid]])->get();
        return view('connector/partner/list',compact('company','connector'));
    }
    public function partnerConnectorListByCompany($id){
        $company=Companies::where('id',$id)->first(); 
        $connector=Connector::where([['connect_state','1'],['connect_type','!=','ENVIZI'],['comp_id',$id]])->get();
        return view('connector/partner/list',compact('company','connector'));
    }
    public function partnerConnectorMainOpen(Request $request){
        $state=$request->state;
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first(); 
        if($state=="NEW"){
            return view('connector/partner/protocols',compact('company'));
        }

        $id=$request->id;
        if($state=="UPDATE"){
            $connector=Connector::where('id',$id)->first(); 
            $protocol=$connector->connect_protocol;
            if($protocol=="API"){
                return view('connector/partner/update_api',compact('connector'));
            }
            if($protocol=="S3"){
                return view('connector/partner/update_s3',compact('connector'));
            }
            if($protocol=="FTP"){
                return view('connector/partner/update_ftp',compact('connector'));
            }
        }
    }
    public function partnerConnectorProtocolOpen(Request $request){
        $protocol=$request->protocol;
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first(); 
        if($protocol=="API"){
            return view('connector/partner/new_api',compact('company','protocol'));
        }
        if($protocol=="S3"){
            return view('connector/partner/new_s3',compact('company','protocol'));
        }
        if($protocol=="FTP"){
            return view('connector/partner/new_ftp',compact('company','protocol'));
        }
    }
    // //pengaturan token untuk akses API
    // public function companyTokenIndex(){
    //     return view('companies/token');
    // }

    // //pengaturan envizi s3 upload file
    // public function companyEnviziConfigIndex(){
    //     return view('companies/envizi_s3');
    // } 
}
