<?php


namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Laravel\Passport\Token;
use App\Models\User;
use App\Models\Companies;
use App\Models\Connector;
use App\Models\Organizations;
use App\Models\Locations;  
use App\Models\AccountStyles; 
use App\Models\Tasks;
use App\Models\ProcessDataLogs;
use App\Models\AccountDataLoad;
use App\Models\AccountDataLoadCSR;
use App\Models\AccountDataLoadImport; 
use App\Models\AccountDataLoadImportCSR; 
use App\Exports\AccountDataLoadExport;
use App\Exports\AccountDataLoadCSRExport;
use App\Mail\ProcessDataLogError;

class APIAccountDataLoad extends Controller
{
    public function postAccountDataLoad(Request $request){
        $bearertoken=$request->bearerToken();
        $user=$this->findUserByBearerToken($bearertoken);
        $usercompid=$user->comp_id;

        $org_name=$request->ORGANIZATION;
        $org=Organizations::with('company')->where('org_name',$org_name)->first();
        $company=$org->company;
        $compid=$company->id; 
        if($usercompid!=$compid){
            return response()->json(['state' => 'error', 'message' => "Invalid User Token, user did't match with attached data", 'code' => 410]);
        }
        if(!$org){
            return response()->json(['state' => 'error', 'message' => "Organization not Exist", 'code' => 411]);
        }
        $dataloads=json_decode(json_encode($request->DATA_LOAD),false);
        $orgid=$org->id;
        //periksa pakah data lokasi dan data account semua transaksi
        //yang dikirim sudah terdaftar
        $i=1;
        foreach ($dataloads as $key => $dataload) {
            $org_name=$dataload->Organization_Jobsite;
            $org=Organizations::where('org_name',$org_name)->first();
            
            if(!$org){
                $message="Organization on Row [".$i."] Not Exist";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' => $message, 'code' => 411]);
            }
    
            $loc_name=$dataload->Location;
            $loc=Locations::where('location_name',$loc_name)->first();
            if(!$loc){
                $message="Location ".$loc_name." on Row [".$i."] not Exist";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' =>  $message, 'code' => 412]);
            }

            $accstylecaption=$dataload->Account_Style_Caption;
            
            $accountstyle=AccountStyles::where('acc_style_caption',$accstylecaption)->first();
            if(!$accountstyle){
                $message="Account Style ".$accstylecaption." on Row [".$i."] not Exist";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' => $message, 'code' => 413]);
            }
            $i++;
        }


        $connector=Connector::where([['comp_id',$compid],['connect_type','ENVIZI'],['connect_protocol','S3']])->first();
        if(!$connector){
            return response()->json(['state' => 'error', 'message' => "Connector for ".$company->comp_name." Not Found please contact Agile", 'code' => 413]);
        }
        $connectid=$connector->id;

        $message='SUCCESS Receive Data via API ASRI-CONNECT';
        $state='RECEIVE';
        $taskname="Automatic Receive Account Data Load from Client";
        $this->savingProcesDataLog($company->id,$connectid,$message,$state);
        $taskid=$this->savingTask($compid,$connectid,$taskname);
        $this->saveAccStyleDataLoadImport($compid,$taskid,$dataloads);
        $arrId=array();
        foreach ($dataloads as $key => $dataload) {
            $loc_name=$dataload->Location;
            $loc=Locations::where('location_name',$loc_name)->first();
            $accstylecaption=$dataload->Account_Style_Caption;
            $accountstyle=AccountStyles::where('acc_style_caption',$accstylecaption)->first();
            $accdl=new AccountDataLoad;
            $accdl->org_id=$org->id;
            $accdl->loc_id=$loc->id;
            $accdl->comp_id=$company->id;
            $accdl->acc_style_id=$accountstyle->id;
            $accdl->organization_link=$org->org_link;
            $accdl->organization_name=$dataload->Organization_Jobsite;
            $accdl->location_name=$dataload->Location;
            $accdl->acc_style_link=$accountstyle->acc_style_link;
            $accdl->acc_style_caption=$dataload->Account_Style_Caption;
            $accdl->acc_number=$dataload->Account_Number;
            $accdl->acc_reference=$dataload->Account_Reference;
            $accdl->acc_supplier=$dataload->Account_Supplier;
            $accdl->record_date_start=$dataload->Record_Start_YYYY_MM_DD;
            $accdl->record_date_end=$dataload->Record_End_YYYY_MM_DD;
            $accdl->acc_data_qty=$dataload->Quantity;
            $accdl->acc_data_tot_cost=$dataload->Total_cost_incl_Tax_in_local_currency;
            $accdl->record_reference=$dataload->Record_Reference;
            $accdl->record_inv_no=$dataload->Record_Invoice_Number;
            $accdl->record_quality=$dataload->Record_Data_Quality;           
            $accdl->save();
            $arrId[]=[$accdl->id];
        }
        
       
        $folder=$connector->connect_remote_folder;// "client_209a3264c7744b/";
        $filename=$connector->connect_data_load_name; // "POCAccountSetupandDataLoad_Upload.xlsx";
        $storagecode=$connector->connect_storage_code; // 'public';//'s3';
        //kirim ke s3 aws sesuai konfirgurasi pada config filename
        // Excel::store(new AccountDataLoadExport, $folder.$filename,'public');
        Excel::store(new AccountDataLoadExport, $folder.$filename,$storagecode);

        //tambah log upload
        $message='SUCCESS Upload Data using '.$connector->connect_name.' via '.$connector->connect_protocol.' driver, to '.$connector->connect_storage_code;
        $state='UPLOADED';
        $this->savingProcesDataLog($company->id,$connector->id,$message,$state);

        //perbaharui status menjadi telah di upload
        AccountDataLoad::whereIn('id',$arrId)->update(['acc_data_state'=>'2']);
        return response()->json(['state' => 'success', 'message' => "Receive Success", 'code' => 200]);
    }

    public function postAccountDataLoadCSR(Request $request){
        $bearertoken=$request->bearerToken();
        $user=$this->findUserByBearerToken($bearertoken);
        $usercompid=$user->comp_id;

        $org_name=$request->ORGANIZATION;
        $org=Organizations::with('company')->where('org_name',$org_name)->first();
        $company=$org->company;
        $compid=$company->id; 
        if($usercompid!=$compid){
            return response()->json(['state' => 'error', 'message' => "Invalid User Token, user did't match with attached data", 'code' => 410]);
        }
        if(!$org){
            return response()->json(['state' => 'error', 'message' => "Organization not Exist", 'code' => 411]);
        }
        $orgid=$org->id;
        $dataloads=json_decode(json_encode($request->DATA_LOAD_CSR),false);

        // dd($dataloads);
        $i=1;
        foreach ($dataloads as $key => $dataload) {
            $loc_name=$dataload->Location;
            $loc=Locations::where('location_name',$loc_name)->first();
            if(!$loc){
                $message="Location ".$loc_name." on Row [".$i."] not Exist";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' =>  $message, 'code' => 412]);
            }

            $accstylecaption=$dataload->Account_Style;
            $accountstyle=AccountStyles::where('acc_style_caption',$accstylecaption)->first();
            if(!$accountstyle){
                $message="Account Style ".$accstylecaption." on Row [".$i."] not Exist";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' => $message, 'code' => 413]);
            }

            //harus ada
            if(!isset($dataload->Account_Number)){
                $message="Account Style ".$accstylecaption." on Row [".$i."] doesn't have Account Number (Account_Number) Please complete before continuing";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' => $message, 'code' => 413]);
            }
            if(!isset($dataload->Record_Start_YYYY_MM_DD)){
                $message="Account Style ".$accstylecaption." on Row [".$i."] doesn't have Record Date Start (Record_Start_YYYY_MM_DD) Please complete before continuing";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' => $message, 'code' => 413]);
            }
            if(!isset($dataload->Record_End_YYYY_MM_DD)){
                $message="Account Style ".$accstylecaption." on Row [".$i."] doesn't have Record Date End (Record_End_YYYY_MM_DD) Please complete before continuing";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' => $message, 'code' => 413]);
            }
            $emptotal=$dataload->Employee_Total;
            $malefemale=$dataload->Male+$dataload->Female;
            if($emptotal!=$malefemale){
                $message="Account Style ".$accstylecaption." on Row [".$i."] have wrong Employee Total Value, Please fix it before continuing";
                $this->sendProcessErrorEmail($orgid,$message);
                return response()->json(['state' => 'error', 'message' => $message, 'code' => 413]);
            }
            $i++;
        }

        
        $connector=Connector::where([['comp_id',$compid],['connect_type','ENVIZI'],['connect_protocol','S3']])->first();
        if(!$connector){
            return response()->json(['state' => 'error', 'message' => "Connector for ".$company->comp_name." Not Found please contact Agile", 'code' => 413]);
        }
        $connectid=$connector->id;
        $message='SUCCESS Receive CSR Data via API ASRI-CONNECT';
        $state='RECEIVE';
        $taskname="Automatic Receive CSR Account Data Load from Client";
        $this->savingProcesDataLog($company->id,$connectid,$message,$state);
        $taskid=$this->savingTask($compid,$connectid,$taskname);
        $this->saveAccStyleCSRDataLoadImport($compid,$taskid,$dataloads);
        $arrId=array();
        foreach ($dataloads as $key => $dataload) {
            $loc_name=$dataload->Location; 
            $loc=Locations::where('location_name',$loc_name)->first();
            $accstylecaption=$dataload->Account_Style;
            $accountstyle=AccountStyles::where('acc_style_caption',$accstylecaption)->first();
            $accdlcsr=new AccountDataLoadCSR;
            $accdlcsr->org_id=$org->id;
            $accdlcsr->loc_id=$loc->id;
            $accdlcsr->comp_id=$company->id;
            $accdlcsr->acc_style_id=$accountstyle->id; 
            $accdlcsr->location_name=$loc->location_name;
            $accdlcsr->location_ref=$loc->location_reff;
            $accdlcsr->acc_style_link=$accountstyle->acc_style_link; //harus ada cek ini karena unuk csr di periksa di envizi
            $accdlcsr->acc_style_caption=$accountstyle->acc_style_caption;
            $accdlcsr->organization_link=$org->org_link;
            $accdlcsr->organization_name=$org->org_name;
            $accdlcsr->acc_subtype=$accountstyle->acc_style_subtype;
            $accdlcsr->acc_reference=$accountstyle->acc_style_reference;
            $accdlcsr->acc_supplier=$accountstyle->acc_style_supplier;
            $accdlcsr->acc_reader=$accountstyle->acc_style_reader;
            $accdlcsr->acc_data_qty=0;
            $accdlcsr->acc_data_tot_cost=0;
            $accdlcsr->acc_number=$dataload->Account_Number;
            $accdlcsr->record_date_start=$dataload->Record_Start_YYYY_MM_DD;
            $accdlcsr->record_date_end=$dataload->Record_End_YYYY_MM_DD;
            $accdlcsr->record_quality="Actual";
            $accdlcsr->record_billing_type="Standard";
            $accdlcsr->record_entry_method="Overwrite";
            $accdlcsr->csr_total=$dataload->Employee_Total;
            $accdlcsr->csr_male=$dataload->Male;
            $accdlcsr->csr_female=$dataload->Female;
            $accdlcsr->csr_less_30=$dataload->_30_th;
            $accdlcsr->csr_between_30_50=$dataload->_30_50_th;
            $accdlcsr->csr_more_50=$dataload->_50th;
            $accdlcsr->csr_phd=$dataload->PhD;
            $accdlcsr->csr_post_graduate=$dataload->Post_Graduate;
            $accdlcsr->csr_bachelor_degree=$dataload->Baachelor_Degree;
            $accdlcsr->csr_high_school=$dataload->High_School;
            $accdlcsr->csr_junior_high_school=$dataload->Junior_High_School;
            $accdlcsr->csr_elementary_school=$dataload->Elementary_School;
            $accdlcsr->csr_eduction_other=$dataload->Others;
            $accdlcsr->csr_islam=$dataload->Islam;
            $accdlcsr->csr_budha=$dataload->Buddha;
            $accdlcsr->csr_hindu=$dataload->Hindu;
            $accdlcsr->csr_katolik=$dataload->Katolik;
            $accdlcsr->csr_kristen=$dataload->Kristen;
            $accdlcsr->csr_religion_other=$dataload->Other;
            $accdlcsr->save();

            $arrId[]=[$accdlcsr->id];
        }
       
        $folder=$connector->connect_remote_folder;// "client_209a3264c7744b/";
        $filename=$connector->connect_data_load_name_csr; // "Account_Setup_and_Data_Load_CSR_Upload.xlsx";
        $storagecode=$connector->connect_storage_code; // 'public';//'s3';

        //kirim ke s3 aws sesuai konfirgurasi pada config filename
        Excel::store(new AccountDataLoadCSRExport,  $folder.$filename, $storagecode);
        // Excel::store(new AccountDataLoadExport, $folder.$filename,'s3');
 
        //tambah log upload
        $message='SUCCESS Upload Data CSR using '.$connector->connect_name.' via '.$connector->connect_protocol.' driver, to '.$connector->connect_storage_code;
        $state='UPLOADED';
        $this->savingProcesDataLog($company->id,$connector->id,$message,$state);

        //perbaharui status menjadi telah di upload
        AccountDataLoadCSR::whereIn('id',$arrId)->update(['csr_state'=>'2']);

        return response()->json(['state' => 'success', 'message' => "Receive Success", 'code' => 200]);
    }

    private function findUserByBearerToken($bearertoken){
        // dd($request->header('Authorization'));
        // $token = $auth_header[1];
        // $auth_header=$request->bearerToken();

        $token_parts = explode('.', $bearertoken);
        $token_header = $token_parts[1];
        $token_header_json = base64_decode($token_header);
        $token_header_array = json_decode($token_header_json, true);
        $token_id = $token_header_array['jti'];
        $user = Token::find($token_id)->user;

        return $user;
    }
    private function savingProcesDataLog($compid,$connectid,$message,$state){
        $processdataload=new ProcessDataLogs;
        $processdataload->comp_id=$compid;
        $processdataload->connect_id=$connectid;
        $processdataload->process_data_type="RECEIVE";
        $processdataload->process_data_origin="Client API Receive";
        $processdataload->process_data_tittle="Receive from ASRI-CONNECT API";
        $processdataload->process_data_note=$message;
        $processdataload->process_data_process_state=$state;
        $processdataload->process_data_process_time=date("Y-m-d H:i:s");
        $processdataload->process_data_describe_state= "Receive Success";
        $processdataload->save();
    }
    private function savingTask($compid,$connectid,$taskname){
        $tasks=new Tasks;
        $tasks->comp_id=$compid;
        $tasks->task_name=$taskname;
        $tasks->task_maker_name="Automatic";
        $tasks->task_maker_message="Automatic Receive from ASRI-CONNECT API";
        $tasks->task_maker_time=date("Y-m-d H:i:s");
        $tasks->task_checker_name="Automatic";
        $tasks->task_checker_message="Automatic Receive from ASRI-CONNECT API";
        $tasks->task_checker_time=date("Y-m-d H:i:s");
        $tasks->task_approval_name="Automatic";
        $tasks->task_approval_message="Automatic Receive from ASRI-CONNECT API";
        $tasks->task_approval_time=date("Y-m-d H:i:s");
        $tasks->task_state="4";
        $tasks->task_progress="100%";
        $tasks->task_approval_type="APV-AUTO";
        $tasks->task_file_name="";
        $tasks->task_final_state="SUCCESS";
        $tasks->task_last_message="Data has been receipted, approved and submited...";
        $tasks->save();

        return $tasks->id;
     }
     public function saveAccStyleDataLoadImport($compid,$taskid,$dataloads){
        //masukan kedalam data load import
        foreach ($dataloads as $key => $dataload) {
            $accdata=new AccountDataLoadImport;
            $accdata->comp_id=$compid;
            $accdata->task_id=$taskid;
            $accdata->organization_name=$dataload->Organization_Jobsite;
            $accdata->location_name=$dataload->Location;
            $accdata->acc_style_caption=$dataload->Account_Style_Caption;
            $accdata->acc_number=$dataload->Account_Number;
            $accdata->acc_reference=$dataload->Account_Reference;
            $accdata->acc_supplier=$dataload->Account_Supplier;
            $accdata->record_date_start=$dataload->Record_Start_YYYY_MM_DD;
            $accdata->record_date_end=$dataload->Record_End_YYYY_MM_DD;
            $accdata->record_quality=$dataload->Record_Data_Quality;
            $accdata->acc_data_tot_cost=$dataload->Total_cost_incl_Tax_in_local_currency;
            $accdata->record_reference=$dataload->Record_Reference;
            $accdata->record_inv_no=$dataload->Record_Invoice_Number;
            $accdata->acc_data_qty=$dataload->Quantity;
            $accdata->acc_data_state='3';
            $accdata->save();
        } 
     }
     public function saveAccStyleCSRDataLoadImport($compid,$taskid,$dataloads){
        $orgid=Companies::where('id',$compid)->first()->org_id;
        $orgname=Organizations::where('id',$orgid)->first()->org_name;
        //masukan kedalam data load import
        foreach ($dataloads as $key => $dataload) {
            $accdlcsr=new AccountDataLoadImportCSR;
            $accdlcsr->comp_id=$compid;
            $accdlcsr->task_id=$taskid;
            $accdlcsr->organization_name=$orgname;
            $accdlcsr->location_name=$dataload->Location;
            $accdlcsr->acc_style_caption=$dataload->Account_Style;
            $accdlcsr->csr_total=$dataload->Employee_Total;
            $accdlcsr->record_date_start=$dataload->Record_Start_YYYY_MM_DD;
            $accdlcsr->record_date_end=$dataload->Record_End_YYYY_MM_DD;
            $accdlcsr->csr_male=$dataload->Male;
            $accdlcsr->csr_female=$dataload->Female;
            $accdlcsr->csr_less_30=$dataload->_30_th;
            $accdlcsr->csr_between_30_50=$dataload->_30_50_th;
            $accdlcsr->csr_more_50=$dataload->_50th;
            $accdlcsr->csr_phd=$dataload->PhD;
            $accdlcsr->csr_post_graduate=$dataload->Post_Graduate;
            $accdlcsr->csr_bachelor_degree=$dataload->Baachelor_Degree;
            $accdlcsr->csr_high_school=$dataload->High_School;
            $accdlcsr->csr_junior_high_school=$dataload->Junior_High_School;
            $accdlcsr->csr_elementary_school=$dataload->Elementary_School;
            $accdlcsr->csr_eduction_other=$dataload->Others;
            $accdlcsr->csr_islam=$dataload->Islam;
            $accdlcsr->csr_budha=$dataload->Buddha;
            $accdlcsr->csr_hindu=$dataload->Hindu;
            $accdlcsr->csr_katolik=$dataload->Katolik;
            $accdlcsr->csr_kristen=$dataload->Kristen;
            $accdlcsr->csr_religion_other=$dataload->Other;
            $accdlcsr->csr_state='3';
            $accdlcsr->save();
        } 
     }
    public function sendProcessErrorEmail($orgid,$message){
        $org=Organizations::with('company')->where('id',$orgid)->first();
        $company=$org->company;
        $compid=$company->id; 
        $connector=Connector::where([['comp_id',$compid],['connect_type','ENVIZI'],['connect_protocol','S3']])->first();
        if(!$connector){
            return response()->json(['state' => 'error', 'message' => "Connector for ".$company->comp_name." Not Found please contact Agile", 'code' => 413]);
        }

        $email=$org->connector->connect_email;

        //tambah log upload
        $processdataload=new ProcessDataLogs;
        $processdataload->comp_id=$company->id;
        $processdataload->connect_id=$connector->id;
        $processdataload->process_data_type="ERROR";
        $processdataload->process_data_origin=$org->org_name;
        $processdataload->process_data_tittle="Upload for ".$org->org_name;
        $processdataload->process_data_note="Error Message For ".$message;
        $processdataload->process_data_process_state="FAILED";
        $processdataload->process_data_process_time=date("Y-m-d H:i:s");
        $processdataload->process_data_desc_state= "Upload Failed";
        $processdataload->process_data_state='3';
        $processdataload->save();

        Mail::to($email)->send(new ProcessDataLogError($processdataload));
    }

    public function postCSR(Request $request){
        $datacsr=json_decode(json_encode($request->DATA_CSR),false);
        foreach ($datacsr as $key => $dtcsr) {
            $csr=new CSR;
            $csr->csr_location_name=$dtcsr->Location;
            $csr->csr_account_style=$dtcsr->Account_style;
            $csr->csr_male=$dtcsr->Male;
            $csr->csr_female=$dtcsr->Female;
            $csr->csr_less_30=$dtcsr->_30_th;
            $csr->csr_between_30_50=$dtcsr->_30_50_th;
            $csr->csr_more_50=$dtcsr->_50th;
            $csr->csr_phd=$dtcsr->PhD;
            $csr->csr_post_graduate=$dtcsr->Post_Graduate;
            $csr->csr_bachelor_degree=$dtcsr->Baachelor_Degree;
            $csr->csr_high_school=$dtcsr->High_School;
            $csr->csr_junior_high_school=$dtcsr->Junior_High_School;
            $csr->csr_elementary_school=$dtcsr->Elementary_School;
            $csr->csr_others_school=$dtcsr->Others;
            $csr->csr_islam=$dtcsr->Islam;
            $csr->csr_budha=$dtcsr->Buddha;
            $csr->csr_hindu=$dtcsr->Hindu;
            $csr->csr_katolik=$dtcsr->Katolik;
            $csr->csr_kristen=$dtcsr->Kristen;
            $csr->csr_other_religion=$dtcsr->Other;
            $csr->csr_state='1';
            $csr->save();
        }
        return response()->json(['state' => 'success', 'message' => "penyimpanan berhasil", 'code' => 200]);
    }

     public function postMasterOrganization(Request $request){
        $org=new Organizations;
        $org->org_name=$request->ORGANIZATION;
        $org->org_group_type=$request->GROUP_TYPE;
        $org->org_group_hierarchy_name=$request->GROUP_HIERARCHY_NAME;
        $org->org_group_name_1=$request->GROUP_NAME_1;
        $org->org_group_name_2=$request->GROUP_NAME_2;
        $org->org_group_name_3=$request->GROUP_NAME_3;
        $org->org_location_name=$request->LOCATION;
        $org->org_location_ref=$request->LOCATION_REFERENCE;
        $org->org_location_ref_no=$request->LOCATION_REF_NO;
        $org->org_street_addres=$request->STREET_ADDRESS;
        $org->org_city=$request->CITY;
        $org->org_state_province=$request->STATE_PROVINCE;
        $org->org_postal_code=$request->POSTAL_CODE;
        $org->org_country=$request->COUNTRY;
        $org->org_latitude_y=$request->LATITUDE_Y;
        $org->org_longtitude_x=$request->LONGITUDE_X;
        $org->org_location_close_date=$request->LOCATION_CLOSE_DATE;
        $org->org_state='1';
        $org->save();
        return response()->json(['state' => 'success', 'message' => "penyimpanan berhasil", 'code' => 200]);
    }
}
