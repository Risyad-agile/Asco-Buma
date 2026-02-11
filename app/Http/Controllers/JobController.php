<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Models\Job;
use App\Models\Companies;
use App\Models\Connector;
use App\Models\ProcessDataLogs;
use App\Models\AccountDataLoadImport;
use App\Models\AccountDataLoadImportCSR;
use App\Models\Organizations;
use App\Models\Locations;
use App\Models\UploadTasks;
use App\Models\AccountStyles;
use App\Models\AccountDataLoad;
use App\Models\Tasks;
use App\Models\AccountStyleGroupImport;
use App\Models\LocationGroupImport;
use App\Models\AccountStyleCompanies;
use App\Exports\AccountDataLoadExport;
use App\Mail\TaskWarningAccStyleMissing;
use GuzzleHttp\Client;
use Validator;
use Config;
use DB;

class JobController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compid(){
        return Auth::user()->company->id;
    }
    public function index()
    {
        return view('jobs/index');
    }
    public function listOpen(Request $request){
        $compid=$request->comp_id;
        $company=Companies::where('id',$compid)->first();
        $jobs=Job::where('comp_id',$compid)->get();
        return view('jobs/list',compact('company','jobs'));
    }
    public function listOpenByCompanyId($compid){
        $company=Companies::where('id',$compid)->first(); 
        $jobs=Job::where('comp_id',$compid)->get();
        return view('jobs/list',compact('company','jobs'));
    }
    public function main(Request $request){
        $jobid=$request->id;
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first();
        $connector=Connector::where('comp_id',$compid)->get();
        $state=$request->state;
        if($state=="NEW"){
            return view('jobs/new',compact('company','connector'));
        }        
        if($state=="UPDATE"){
            $job=Job::where('id',$jobid)->first();
            return view('jobs/update',compact('company','connector','job'));
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
            'job_name' => 'required|string',
            'connect_id' => 'required|string', 
        ],[
            'job_name.required' => 'Please Fill Job Name',
            'connect_id.required' => 'Please Choose Connection from List',
        ]);

        $job_name=$request->get('form')['job_name'];
        $connect_id=$request->get('form')['connect_id'];
        $job_interval=isset($request->get('form')['job_interval'])?$request->get('form')['job_interval']:"HOURLY";
        $job_repeating_date=isset($request->get('form')['job_repeating_date'])?$request->get('form')['job_repeating_date']:"01";
        $job_repeating_day=isset($request->get('form')['job_repeating_day'])?$request->get('form')['job_repeating_day']:"MONDAY";
        $job_execute_time=isset($request->get('form')['job_execute_time'])?$request->get('form')['job_execute_time']:"MONDAY";

        $job=new Job;
        $job->comp_id=$request->compid;
        $job->connect_id=$connect_id;
        $job->job_name=$job_name;
        $job->job_interval=$job_interval;
        $job->job_repeating_date=$job_repeating_date;
        $job->job_repeating_day=$job_repeating_day;
        $job->job_execute_time=$job_execute_time;
        $job->job_state="1";
        $job->save();          

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
        $job_name=isset($request->get('form')['job_name'])?$request->get('form')['job_name']:"";
        $connect_id=isset($request->get('form')['connect_id'])?$request->get('form')['connect_id']:"";
        $job_interval=isset($request->get('form')['job_interval'])?$request->get('form')['job_interval']:"";
        $job_repeating_date=isset($request->get('form')['job_repeating_date'])?$request->get('form')['job_repeating_date']:"";
        $job_repeating_day=isset($request->get('form')['job_repeating_day'])?$request->get('form')['job_repeating_day']:"";
        $job_execute_time=isset($request->get('form')['job_execute_time'])?$request->get('form')['job_execute_time']:"";
       
      
        Job::where('id',$id)->update([ 
          'job_name'=>$job_name,
          'connect_id'=>$connect_id,
          'job_interval'=>$job_interval,
          'job_repeating_date'=>$job_repeating_date,
          'job_repeating_day'=>$job_repeating_day,
          'job_execute_time'=>$job_execute_time,   
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

    //----Partner/Company 
    public function partnerJobList(){
        $compid=$this->compid();
        $company=Companies::where('id',$compid)->first();
        $jobs=Job::where('comp_id',$compid)->get();
        return view('jobs/partner/list',compact('company','jobs'));
    }
    public function partnerJobListByCompanyId($compid){
        $company=Companies::where('id',$compid)->first(); 
        $jobs=Job::where('comp_id',$compid)->get();
        return view('jobs/partner/list',compact('company','jobs'));
    }
    public function partnerMain(Request $request){
        //dd($request);
        $jobid=$request->id;
        $compid=$request->compid;
        $company=Companies::where('id',$compid)->first();
        $connector=Connector::where([['connect_state','1'],['connect_type','!=','ENVIZI'],['comp_id',$compid]])->get();
        $state=$request->state;
        if($state=="NEW"){
            return view('jobs/partner/new',compact('company','connector'));
        }  
        if($state=="UPDATE"){
            $job=Job::where('id',$jobid)->first();
            return view('jobs/partner/update',compact('company','connector','job'));
        }        
    }

    //----Tools
    public function executeJob(Request $request){
        $jobid=$request->jobid;
        $connect_id=Job::where('id',$jobid)->first()->connect_id;
        $connector=Connector::where('id',$connect_id)->first();
        $type=$connector->connect_type;
        $url = $connector->connect_url;
        $body= $connector->connect_body;
        $token=$connector->connect_token_value;
        $status="success";
        $message="Data successfully Syncronized";
        try {
            $client=new Client([
                'base_uri'=>$url, 
                'headers' => [
                    'Authorization' => 'Bearer '.$token,   
                    'Content-Type' => 'application/json',
                ],
                // 'debug' => true,
            ]);
            // var_dump(openssl_get_cert_locations());
            $response = $client->request('POST',$url,['body'=>$body]);
   
	        $api_result = json_decode($response->getBody()->getContents());
            if($api_result->title="Success"){
                // dd($result->result);
                $contents=$api_result->result->content;
                if($type=="CLIENT-ACCSTYLE"){
                    $status="warning";
                    $message=$this->retrieveAccountStyleData($connect_id,$contents);
                }
                if($type=="CLIENT-CSRDATA"){
                    $status="warning";
                    $message=$this->retrieveAccountStyleCSRData($connect_id,$contents);
                }
            }
	        return response()->json(['status' =>  $status, 'message' => $message, 'code' => 200]);
		} catch (\Exception $e) { 
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
    }

    public function retrieveAccountStyleData($connectid,$contents){
        $connector=Connector::where('id',$connectid)->first();
        $compid=$connector->comp_id;
        $company=Companies::where('id',$compid)->first();
        $org=Organizations::where('id',$company->org_id)->first();

        $message="Retrieve Account Data from ".$org->org_name." via API";
        $this->savingProcesDataLog($compid,$connectid,$message);
        $taskname="Automatic Retriever Account Style from Client";
        $taskid=$this->savingTask($compid,$taskname);
        
        //masukan kedalam data load import
        foreach ($contents as $key => $content) {
            $accdata=new AccountDataLoadImport;
            $accdata->comp_id=$compid;
            $accdata->task_id=$taskid;
            // $accdata->organization_name=$content->organizationJobsite;
            $accdata->organization_name=$org->org_name; // update langsung organsasinya 
            $accdata->location_name=$content->location;
            $accdata->acc_style_caption=$content->accountStyleCaption; 
            $accdata->acc_number=$content->accountNumber;
            $accdata->acc_reference=$content->accountReference;
            $accdata->acc_supplier=$content->accountSupplier;
            $accdata->record_date_start=$content->recordStartYyyyMmDd;
            $accdata->record_date_end=$content->recordEndYyyyMmDd;
            $accdata->record_quality=$content->recordDataQuality;
            $accdata->acc_data_tot_cost=$content->totalCostInclTaxInLocalCurrency;
            $accdata->record_reference=$content->recordReference;
            $accdata->record_inv_no=$content->recordInvoiceNumber;
            $accdata->acc_data_qty=$content->quantity;
            $accdata->save();
        }   

        $verified=$this->verifyAccountStyleData($taskid);
        $message="Data successfully processed";
        if(!$verified){
            $message="Failed Automatic Proccess, please check on task";
            $this->sendTaskWarningEmail($taskid);
            return $message;
        }
        $this->executeSendingAccountStyleProcess($compid,$taskid);
    
        return $message;
    }

    public function retrieveAccountStyleCSRData($connectid,$contents){
        $connector=Connector::where('id',$connectid)->first();
        $compid=$connector->comp_id;
        $company=Companies::where('id',$compid)->first();
        $org=Organizations::where('id',$company->org_id)->first();

        $message="Retrieve CSR Account Data from ".$org->org_name." via API ASRI-CONNECT";
        $this->savingProcesDataLog($compid,$connectid,$message);
        $taskname="Automatic Retriever CSR Account Style from Client";
        $taskid=$this->savingTask($compid,$taskname);

        foreach ($contents as $key => $content) {
            $accdlcsr=new AccountDataLoadImportCSR; 
            $accdlcsr->comp_id=$compid;
            $accdlcsr->task_id=$taskid;
            $accdlcsr->location_name=$content->location; 
            $accdlcsr->acc_style_caption=$content->accountStyle;
            $accdlcsr->organization_name=$org->org_name;
            $accdlcsr->csr_total=$content->male+$content->female;
            $accdlcsr->csr_male=$content->male;
            $accdlcsr->csr_female=$content->female;
            $accdlcsr->csr_less_30=$content->_30Th;
            $accdlcsr->csr_between_30_50=$content->_3050Th;
            $accdlcsr->csr_more_50=$content->_50th;
            $accdlcsr->csr_phd=$content->phD;
            $accdlcsr->csr_post_graduate=$content->postGraduate;
            $accdlcsr->csr_bachelor_degree=$content->baachelorDegree;
            $accdlcsr->csr_high_school=$content->highSchool;
            $accdlcsr->csr_junior_high_school=$content->juniorHighSchool;
            $accdlcsr->csr_elementary_school=$content->elementarySchool;
            $accdlcsr->csr_eduction_other=$content->others;
            $accdlcsr->csr_islam=$content->islam;
            $accdlcsr->csr_budha=$content->buddha;
            $accdlcsr->csr_hindu=$content->hindu;
            $accdlcsr->csr_katolik=$content->katolik;
            $accdlcsr->csr_kristen=$content->kristen;
            $accdlcsr->csr_religion_other=$content->other;
            $accdlcsr->save();
         }
         $verified=$this->verifyAccountStyleCSRData($taskid);
         $message="Data successfully processed"; 
         if(!$verified){
            $message="Failed Automatic Proccess, please check on task";
            $this->sendTaskWarningEmailCSR($taskid);
            return $message;
        }
         return $message;
    }

    private function savingProcesDataLog($compid,$connectid,$message){
        $processdataload=new ProcessDataLogs;
        $processdataload->comp_id=$compid;
        $processdataload->connect_id=$connectid;
        $processdataload->process_data_type="RETRIEVE";
        $processdataload->process_data_origin="Client API";
        $processdataload->process_data_tittle="Retrieve from Client API";
        $processdataload->process_data_note=$message;
        $processdataload->process_data_process_state="RETRIVED";
        $processdataload->process_data_process_time=date("Y-m-d H:i:s");
        $processdataload->process_data_describe_state= "Retrieve Success";
        $processdataload->save();
    }

    private function savingTask($compid,$taskname){
        $tasks=new Tasks;
        $tasks->comp_id=$compid;
        $tasks->task_name=$taskname;
        $tasks->task_maker_name="Automatic";
        $tasks->task_maker_message="Automatic Retrieve from Client API";
        $tasks->task_maker_time=date("Y-m-d H:i:s");
        $tasks->task_state="1";
        $tasks->task_progress="50%";
        $tasks->task_approval_type="APV-AUTO";
        $tasks->task_file_name="";
        $tasks->task_last_message="Prepared Data for Proses";
        $tasks->save();
        return $tasks->id;
    }
   
    private function verifyAccountStyleData($taskid){
        #verifikasi lokasi, cukup dengan mencari lokasi pada master data, jika tidak ditemukan
        #maka dikembalikan dengan status lokasi tidak terverifikasi
        $message="Data Verified";

        $verifyresult=true;

        if(!$this->verifyAccountStyleLocation($taskid)){
            $message="Automatic Process Can't Continue, problem verify location data";
            $verifyresult=false;
        }
        if(!$this->verifyAccountStyle($taskid)){
            $message="Automatic Process Can't Continue, problem verify account style data, some or All can't find on ASRI Database please fix it";
            $verifyresult=false;
        }
        Tasks::where('id',$taskid)->update(['task_last_message'=>$message]);
        return  $verifyresult;
    }
    private function verifyAccountStyleLocation($taskid){
        $verify=true;
        //grouping berdasarkan location yang baru diterima
        $locgroups=DB::table('account_data_load_import')->select('comp_id','location_name')
            ->where('task_id',$taskid)->groupBy('comp_id','location_name')->get();
        foreach($locgroups as $key => $locgroup){
            $locfound=Locations::where('location_name',$locgroup->location_name)->first();
            if(!$locfound){
                $verify=false; 
            }
        }
        return $verify;
    }

    private function verifyAccountStyle($taskid){
        //grouping berdasarkan accountstyle yang baru diterima
        $accstylegroups=DB::table('account_data_load_import')->select('comp_id','acc_style_caption')
            ->where('task_id',$taskid)->groupBy('comp_id','acc_style_caption')->get();
        
        $verify=true;
        //cari account style pada tabel account styke company
        foreach($accstylegroups as $key => $acsg){
            $accfound=AccountStyleCompanies::where('acc_style_comp_caption_import',$acsg->acc_style_caption)->first();
            if($accfound){
                AccountDataLoadImport::where('acc_style_caption',$acsg->acc_style_caption)
                    ->update(['acc_style_caption'=>$accfound->acc_style_comp_caption]);
            }else{
                $accstylegroup =new AccountStyleGroupImport;
                $accstylegroup->comp_id=$acsg->comp_id;
                $accstylegroup->task_id=$taskid; 
                $accstylegroup->acc_style_caption='ACCSTYLE';  
                $accstylegroup->acc_style_caption_import=$acsg->acc_style_caption;
                $accstylegroup->acc_style_group_describe_state="Account Style ".$acsg->acc_style_caption." not found in ASRI Database, please fix it";
                $accstylegroup->acc_style_group_state='1'; //not maping
                $accstylegroup->save();
                $verify=false;
            };
        }
        return $verify;
    }
    private function verifyAccountStyleCSRData($taskid){
        #verifikasi lokasi, cukup dengan mencari lokasi pada master data, jika tidak ditemukan
        #maka dikembalikan dengan status lokasi tidak terverifikasi
        $message="Data Verified";

        $verifyresult=true;

        if(!$this->verifyAccountStyleCSRLocation($taskid)){
            $message="Automatic Process Can't Continue, problem verify location data";
            $verifyresult=false;
        }
        if(!$this->verifyAccountStyleCSR($taskid)){
            $message="Automatic Process Can't Continue, problem verify account style data, some or All can't find on ASRI Database please fix it";
            $verifyresult=false;
        }
        Tasks::where('id',$taskid)->update(['task_last_message'=>$message]);
        return  $verifyresult;
    }
    private function verifyAccountStyleCSRLocation($taskid){
        $verify=true;
        //grouping berdasarkan location yang baru diterima
        $locgroups=DB::table('account_data_load_import_csr')->select('comp_id','location_name')
            ->where('task_id',$taskid)->groupBy('comp_id','location_name')->get();
        foreach($locgroups as $key => $locgroup){
            $locfound=Locations::where('location_name',$locgroup->location_name)->first();
            if(!$locfound){
                $verify=false; 
            }
        }
        return $verify;
    }

    private function verifyAccountStyleCSR($taskid){
        //grouping berdasarkan accountstyle yang baru diterima
        $accstylegroups=DB::table('account_data_load_import_csr')->select('comp_id','acc_style_caption')
            ->where('task_id',$taskid)->groupBy('comp_id','acc_style_caption')->get();
        
        $verify=true;
        //cari account style pada tabel account styke company
        foreach($accstylegroups as $key => $acsg){
            $accfound=AccountStyleCompanies::where('acc_style_comp_caption_import',$acsg->acc_style_caption)->first();
            if($accfound){
                AccountDataLoadImport::where('acc_style_caption',$acsg->acc_style_caption)
                    ->update(['acc_style_caption'=>$accfound->acc_style_comp_caption]);
            }else{
                $accstylegroup =new AccountStyleGroupImport;
                $accstylegroup->comp_id=$acsg->comp_id;
                $accstylegroup->task_id=$taskid; 
                $accstylegroup->acc_style_caption='ACCSTYLE';  
                $accstylegroup->acc_style_caption_import=$acsg->acc_style_caption;
                $accstylegroup->acc_style_group_describe_state="Account Style ".$acsg->acc_style_caption." not found in ASRI Database, please fix it";
                $accstylegroup->acc_style_group_state='1'; //not maping
                $accstylegroup->save();
                $verify=false;
            };
        }
        return $verify;
    }

    private function sendTaskWarningEmail($taskid){
        $compid=Tasks::where('id',$taskid)->first()->comp_id;
        $email=Companies::where('id',$compid)->first()->comp_email;

        $accstylegroups=DB::table('account_data_load_import')->select('task_id','comp_id','acc_style_caption')
        ->where('task_id',$taskid)->groupBy('task_id','comp_id','acc_style_caption');

        $tasks=DB::table('tasks')->joinSub($accstylegroups,'accstylegroups',
            function($join){
                $join->on('tasks.id','=','accstylegroups.task_id');
            })
        ->join('companies','tasks.comp_id','=','companies.id') 
        ->join('organizations','companies.org_id','=','organizations.id')
        ->select(
            'tasks.id','tasks.task_name','tasks.task_last_message','tasks.task_maker_time',
            'accstylegroups.comp_id','accstylegroups.acc_style_caption','organizations.org_name'
        )->get();

    
        Mail::to($email)->send(new TaskWarningAccStyleMissing($tasks));
        return;
    }

    private function sendTaskWarningEmailCSR($taskid){
        $compid=Tasks::where('id',$taskid)->first()->comp_id;
        $email=Companies::where('id',$compid)->first()->comp_email;

        $accstylegroups=DB::table('account_data_load_import_csr')->select('task_id','comp_id','acc_style_caption')
        ->where('task_id',$taskid)->groupBy('task_id','comp_id','acc_style_caption');

        $tasks=DB::table('tasks')->joinSub($accstylegroups,'accstylegroups',
            function($join){
                $join->on('tasks.id','=','accstylegroups.task_id');
            })
        ->join('companies','tasks.comp_id','=','companies.id') 
        ->join('organizations','companies.org_id','=','organizations.id')
        ->select(
            'tasks.id','tasks.task_name','tasks.task_last_message','tasks.task_maker_time',
            'accstylegroups.comp_id','accstylegroups.acc_style_caption','organizations.org_name'
        )->get();

    
        Mail::to($email)->send(new TaskWarningAccStyleMissing($tasks));
        return;
    }

    private function executeSendingAccountStyleProcess($compid, $taskid){
        $accdataloadimports =AccountDataLoadImport::where('task_id',$taskid)->get();
        $connector=Connector::where([['comp_id',$this->compid()],['connect_type','ENVIZI'],['connect_protocol','S3']])->first();
        $company=Companies::where('id',$compid)->first();
        $org=Organizations::where('id',$company->org_id)->first();

        $arrId=array();
        foreach ($accdataloadimports as $key => $accdataloadimport) {
            $loc_name=$accdataloadimport->location_name;
            $loc=Locations::where('location_name',$loc_name)->first();
            $accstylecaption=$accdataloadimport->acc_style_caption;
            $accountstyle=AccountStyles::where('acc_style_caption',$accstylecaption)->first();
    
            $accdl=new AccountDataLoad;
            $accdl->org_id=$org->id;
            $accdl->loc_id=$loc->id;
            $accdl->comp_id=$company->id;
            $accdl->acc_style_id=$accountstyle->id;
            $accdl->task_id=$accdataloadimport->task_id;
            $accdl->organization_link=$org->org_link;
            $accdl->organization_name=$accdataloadimport->organization_name;
            $accdl->location_name=$accdataloadimport->location_name;
            $accdl->acc_style_link=$accountstyle->acc_style_link;
            $accdl->acc_style_caption=$accdataloadimport->acc_style_caption;
            $accdl->acc_number=$accdataloadimport->acc_number;
            $accdl->acc_reference=$accdataloadimport->acc_reference;
            $accdl->acc_supplier=$accdataloadimport->acc_supplier;
            $accdl->record_date_start=$accdataloadimport->record_date_start;
            $accdl->record_date_end=$accdataloadimport->record_date_end;
            $accdl->acc_data_qty=$accdataloadimport->acc_data_qty;
            $accdl->acc_data_tot_cost=$accdataloadimport->acc_data_tot_cost;
            $accdl->record_reference=$accdataloadimport->record_reference;
            $accdl->record_inv_no=$accdataloadimport->record_inv_no;
            $accdl->record_quality=$accdataloadimport->record_quality;
            $accdl->save();
            $arrId[]=[$accdl->id];
        }
        
        $folder=$connector->connect_remote_folder;// "client_209a3264c7744b/";
        $filename=$connector->connect_data_load_name; // "POCAccountSetupandDataLoad_Upload.xlsx";
        $storagecode=$connector->connect_storage_code; // 'public';//'s3';

        //kirim ke s3 aws sesuai konfirgurasi pada config filename 
        Excel::store(new AccountDataLoadExport, $folder.$filename,$storagecode); 

        //perbaharui status menjadi telah di upload
        AccountDataLoad::whereIn('id',$arrId)->update(['acc_data_state'=>'2']);

        //update task
        Tasks::where('id',$id)->update([
            "task_checker_name" => "Automatic",
            "task_checker_message" => "Automatic Retrieve from Client API.",
            "task_checker_time" => date("Y-m-d H:i:s"),
            "task_progress" => "60%", 
            "task_approval_name" => "Automatic",
            "task_approval_message" => "Automatic Retrieve from Client API",
            "task_approval_time" => date("Y-m-d H:i:s"),
            "task_progress" => "100%",
            "task_last_message"=>"Automatic Retrieve from Client API",
            "task_state" => "4",
        ]);
    }
}

