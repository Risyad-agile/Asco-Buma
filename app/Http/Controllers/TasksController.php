<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel; 
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\User; 
use App\Models\Tasks; 
use App\Models\Companies; 
use App\Models\Connector;
use App\Models\AccountDataLoadImport; 
use App\Models\AccountDataLoadImportCSR; 
use App\Models\Organization;
use App\Models\Location;  
use App\Models\AccountStyles; 
use App\Models\ProcessDataLogs;
use App\Models\AccountDataLoad;
use App\Models\AccountDataLoadCSR;
use App\Models\AccountStyleGroupImport;
use App\Models\AccountStyleCompanies;
use App\Models\AccountStyleMapping;
use App\Imports\AccountDataLoadImportProcess;
use App\Imports\AccountDataLoadImportCollection;
use App\Imports\AccountDataLoadImportCSRCollection;
use App\Exports\AccountDataLoadExport;
use App\Exports\AccountDataLoadExportCSR;
use App\Exports\TaskDetailExportE;
use App\Exports\TaskDetailExportS;
use App\Mail\ProcessDataLogError;
use App\Mail\TaskMakerEmail;

use App\Models\TempSync;

use Validator;

class TasksController extends Controller
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
    public function orgid(){
        return Auth::user()->company->organization->id;
    }
    public function name(){
        return Auth::user()->name;
    }
    public function email(){
        return Auth::user()->email;
    }
    public function index()
    {
        return view('tasks/index');
    }
    public function listOpen(Request $request){
        $compid=$request->comp_id;
        $company=Companies::where('id',$compid)->first();
        $tasks=Tasks::where('comp_id',$compid)->get();
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }
        return view('tasks/list',compact('company','tasks'));
    }
     
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     * public function store(Request $request)
     * {
     *   //
     * }
     */
    

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

    #----PARTNER/ COMPANY/ CLIENT
    public function companyTaskList(){
        $company=Companies::where('id',$this->compid())->first();
        $tasks=Tasks::where('comp_id',$this->compid())->get();
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }
        return view('tasks/list',compact('company','tasks'));
    }
    
    
    #----ROLE MAKER ---------------------------------------------------------------------------------------------
    #--- Task Main
    public function createTaskMainE(){
        $tasks = Tasks::where([['comp_id', $this->compid()],['type_csr', 'N']])
            ->leftJoin(DB::raw('(SELECT task_id, SUM(acc_data_tot_cost) as total_cost 
                                FROM account_data_load_import 
                                GROUP BY task_id) as acc_data'), 
                    function($join) {
                $join->on('tasks.id', '=', 'acc_data.task_id');
            })
            ->orderby('task_maker_time', 'desc')
            ->select('tasks.*', DB::raw('COALESCE(acc_data.total_cost, 0) as total_cost'))
            ->get();

        $company = Companies::where('id', $this->compid())->first();
        $org = Organization::where('id', $company->org_id)->first();
        $orgexist = $org ? "EXIST" : "NOTEXIST";

        foreach ($tasks as $key => $task) {
            $task->task_state = $this->taskStateDescription($task->task_state);
            $task->input_methode = $this->taskMethodeDescription($task->input_methode);
        }

        return view("tasks/partner/create/create_task_listE", compact('tasks', 'orgexist'));
    }
    public function createTaskMainS(){
        $tasks = Tasks::where([['comp_id', $this->compid()],['type_csr', 'Y']])
            ->leftJoin(DB::raw('(SELECT task_id, SUM(acc_data_tot_cost) as total_cost 
                                FROM account_data_load_import 
                                GROUP BY task_id) as acc_data'), 
                    function($join) {
                $join->on('tasks.id', '=', 'acc_data.task_id');
            })
            ->orderby('task_maker_time', 'desc')
            ->select('tasks.*', DB::raw('COALESCE(acc_data.total_cost, 0) as total_cost'))
            ->get();
    
        $company = Companies::where('id', $this->compid())->first();
        $org = Organization::where('id', $company->org_id)->first();
        $orgexist = $org ? "EXIST" : "NOTEXIST";
    
        foreach ($tasks as $key => $task) {
            $task->task_state = $this->taskStateDescription($task->task_state);
            $task->input_methode = $this->taskMethodeDescription($task->input_methode);
        }
    
        return view("tasks/partner/create/create_task_listS", compact('tasks', 'orgexist'));
    }

    #--- Task CRUD 
    public function createTaskE(Request $request){
        $state=$request->state;

        if($state=="CREATE"){
            $tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-LEVEL'],['task_state','1']])->get();
            $company=Companies::where('id',$this->compid())->first();
            $org=Organization::where('id',$company->org_id)->first();
            $orgexist="EXIST";
            if(!$org){
                $orgexist="NOTEXIST";
            }
            foreach ($tasks as $key => $task) {
                $task->task_state=$this->taskStateDescription($task->task_state);
            }
            
            return view("tasks/partner/create/create_task_newE",compact('tasks','orgexist')); 
        } 

        if($state=="OPEN"){
            $taskid = $request->taskid;
            $task = Tasks::where('id', $taskid)->first();

            $accstyleimports = DB::table('account_data_load_import as adi')
                ->leftJoin('account_styles as mst', 'adi.acc_style_caption', '=', 'mst.acc_style_caption')
                ->leftJoin('account_style_mapping as map', 'map.acc_style_comp_id', '=', 'mst.id')
                ->select(
                    'adi.*',
                    'mst.acc_style_caption',
                    'map.acc_style_mtra_caption'
                )
                ->where('adi.comp_id', $this->compid())
                ->where('adi.task_id', $taskid)
                ->get();

            return view('tasks/partner/create/create_task_openE', compact('task', 'accstyleimports')); 
        }
        
        if($state=="FILEUPDATE"){
            $tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-LEVEL'],['task_state','1']])->get();
            $company=Companies::where('id',$this->compid())->first();
            $org=Organization::where('id',$company->org_id)->first();
            $orgexist="EXIST";
            if(!$org){
                $orgexist="NOTEXIST";
            }
            foreach ($tasks as $key => $task) {
                $task->task_state=$this->taskStateDescription($task->task_state);
            }
            return view("tasks/partner/create/create_task_uploadE");
        }
    }

    public function createTaskS(Request $request){
    $state=$request->state;
    // dd($state);
    if($state=="CREATE"){
        $tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-LEVEL'],['task_state','1']])->get();
        $company=Companies::where('id',$this->compid())->first();
        $org=Organization::where('id',$company->org_id)->first();
        $orgexist="EXIST";
        if(!$org){
            $orgexist="NOTEXIST";
        }
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }
        
        return view("tasks/partner/create/create_task_newS",compact('tasks','orgexist')); 
    } 

    if($state=="OPEN"){
        $taskid = $request->taskid;
        $task = Tasks::where('id', $taskid)->first();           
        $accstyleimports = AccountDataLoadImport::where([
            ['comp_id', $this->compid()],
            ['task_id', $taskid]
        ])->get();
        return view('tasks/partner/create/create_task_openS', compact('task'));
    }
    
    if($state=="FILEUPDATE"){
        $tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-LEVEL'],['task_state','1']])->get();
        $company=Companies::where('id',$this->compid())->first();
        $org=Organization::where('id',$company->org_id)->first();
        $orgexist="EXIST";
        if(!$org){
            $orgexist="NOTEXIST";
        }
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }
        return view("tasks/partner/create/create_task_uploadS");
    }
}

    #-- Task CRUD : Insert New Task 
    public function CreateNewTaskE(Request $request)
    {
        DB::beginTransaction();
        try {
            // Static Value 
            $prefix = 'E'; // ENVIRONMENT 
            $compid = Auth::user()->company->id; //compid(); // atau auth()->user()->comp_id;  
            $orgId = Companies::where('id', $compid)->value('org_id');
            $orgName = Organization::where('id', $orgId)->value('org_name');
            //
            $year = date('y'); // Ambil 2 digit tahun terakhir
            $month = date('m'); // Ambil 2 digit bulan 

            // Format org_id menjadi 3 digit dengan leading zero
            $orgIdFormatted = str_pad($orgId, 3, '0', STR_PAD_LEFT);
            $orgDateCode = $orgIdFormatted . '.' . $year . $month;


            // Ambil transaksi terakhir di bulan yang sama
            $lastTrans = Tasks::where('trans_no', 'like', "%$orgDateCode.%")
                            ->where(function($query) {
                                             $query->where('trans_no', 'like', 'E%'); 
                })
                ->orderBy('trans_no', 'desc')
                ->value('trans_no');

            if ($lastTrans) {
                // Ambil nomor urut terakhir dan tambah 1
                $lastNumber = (int) substr($lastTrans, -6);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            } else {
                // Jika belum ada transaksi di bulan ini, mulai dari 0000001
                $newNumber = '000001';
            }

            // Format TransNo sesuai kebutuhan
            $TransNo = $prefix . $orgDateCode . '.' . $newNumber;
            // Simpan ke Tasks (Header)
            $task = Tasks::create([
                'trans_no'=> $TransNo, 
                'comp_id' => $compid,
                'task_name' => 'Data Capture '.$orgName,
                'task_maker_name' => Auth::user()->name,
                'task_maker_message' => 'Task Created',
                'task_maker_time' => now(),
                'task_progress' => '0%',
                'task_last_message' => 'Task Initialized',
                'task_approval_type' => 'APV-LEVEL',
                'task_state' => '1', // Status Created
                'input_methode' => 'C', // Import
                'type_csr' => 'N', // Y:CSR, N:Non CSR 
            ]);

            // Mengambil task_id dari record header yang baru dibuat
            $task_id = $task->id;

            // Mengambil data yang dikirim
            $tasks = $request->input('tasks');  
            // Menyimpan data ke dalam database menggunakan bulk insert
            $insertData = [];
            foreach ($tasks as $taskDetail) {
                $locationName = Location::where('id', $taskDetail['id'])->value('location_name'); 
                // $accStyleCaption = AccountStyleCompanies::where('id', $taskDetail['acc_style_caption'])->value('acc_style_comp_caption');
                $accStyleCaption = AccountStyleCompanies::where(trim('acc_style_comp_caption'), $taskDetail['acc_style_caption'])->value('acc_style_comp_caption');
                $insertData[] = [
                    'comp_id' => $compid,
                    'task_id' => $task_id, // Gunakan task_id dari header yang baru dibuat
                    'organization_name' => $orgName,
                    'location_name' => $locationName, // $taskDetail['location_name'],
                    'acc_style_caption' => $taskDetail['acc_style_caption'], //  $accStyleCaption,
                    'acc_number' => $taskDetail['acc_number'],
                    'acc_supplier' => $taskDetail['acc_supplier'],
                    'record_date_start' => date('Y-m-d H:i:s', strtotime($taskDetail['record_date_start'])),
                    'record_date_end' => date('Y-m-d H:i:s', strtotime($taskDetail['record_date_end'])),  
                    'acc_data_qty' => (float) $taskDetail['acc_data_qty'], 
                    'acc_data_tot_cost' => (float) $taskDetail['acc_data_tot_cost'], 
                ];
            }

            // Simpan data ke account_data_load_import (DETAIL)
            AccountDataLoadImport::insert($insertData);

            DB::commit();
            return response()->json(['message' => 'Data berhasil disimpan!'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function CreateNewTaskUploadE(Request $request){
        $prefix = 'E'; // ENVIRONMENT
        $compid=$this->compid();
        $company=Companies::where('id',$compid)->first();  
        $orgname=Organization::where('id',$company->org_id)->first()->org_name;

        // Auto Number for TransNo -----------------------------------------------
        $year = date('y'); // Ambil 2 digit tahun terakhir
        $month = date('m'); // Ambil 2 digit bulan
        $orgId = Companies::where('id', $compid)->value('org_id');

        // Format org_id menjadi 3 digit dengan leading zero
        $orgIdFormatted = str_pad($orgId, 3, '0', STR_PAD_LEFT);
        $orgDateCode = $orgIdFormatted . '.' . $year . $month;
        
        // Ambil transaksi terakhir di bulan yang sama
        $lastTrans = Tasks::where('trans_no', 'like', "%$orgDateCode.%")
                            ->where(function($query) {
                                             $query->where('trans_no', 'like', 'E%'); 
                            })
                            ->orderBy('trans_no', 'desc')
                            ->value('trans_no');

        if ($lastTrans) {
                // Ambil nomor urut terakhir dan tambah 1
                $lastNumber = (int) substr($lastTrans, -6);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
                // Jika belum ada transaksi di bulan ini, mulai dari 0000001
                $newNumber = '000001';
        }

        // Format TransNo sesuai kebutuhan
        $TransNo = $prefix . $orgDateCode . '.' . $newNumber;
        // ------------------------------------------------------------------------

        $filexls = $request->file('files')[0];
        $new_name= "ACCDATALOAD.".$filexls->getClientOriginalExtension();
        $tujuan_upload = public_path("images"); //daripada buat folder baru simpen di images ajah
        $filexls->move($tujuan_upload,$new_name); 
  
        $fileloc=$tujuan_upload."/".$new_name;
  
        // AccountDataLoadImport::truncate(); //pastikan data yang ada di hapus
        $accdataloadimports= Excel::toCollection(new AccountDataLoadImportCollection, $fileloc);
        // dd($accdataloadimports["Records to load"]);

        $acsloadimports=json_decode(json_encode($accdataloadimports["Records to load"]),false);
        $orgfound=true;

        foreach ($acsloadimports as $key => $acsloadimport) {
            if($orgname!=$acsloadimport->organization){
                if($acsloadimport->organization==null){ //data kosong
                    continue;
                }
                $orgfound=false;
                break;
            }
        }

        if(!$orgfound){
            $processdataload=new ProcessDataLogs;
            $processdataload->comp_id=$company->id;
            $processdataload->connect_id='0';
            $processdataload->process_data_type="FAILED";
            $processdataload->process_data_origin='CLIENT UPLOAD';
            $processdataload->process_data_tittle="Failed Process for ".$orgname;
            $processdataload->process_data_note="Organization Not Found, File uploaded but cant processed...";
            $processdataload->process_data_process_state="FAILED";
            $processdataload->process_data_process_time=date("Y-m-d H:i:s");
            $processdataload->process_data_describe_state= "Import failed file :".$fileloc ;
            $processdataload->save();
            // return false;
            return response()->json(['state' => 'error', 'message' => "Fail save file", 'code' => 413]);
        }

        $tasks=new Tasks;
        $tasks->trans_no=$TransNo;
        $tasks->comp_id=$compid;
        $tasks->task_name="Upload Account Data ".$company->comp_name;
        $tasks->task_maker_name=$this->name();
        $tasks->task_maker_message="Success prepared...";
        $tasks->task_maker_time=date("Y-m-d H:i:s");
        $tasks->task_progress="30%";
        $tasks->task_file_name=$new_name;
        $tasks->task_approval_type="APV-LEVEL";
        $tasks->task_last_message="Success prepared...";
        $tasks->task_state="1";
        $tasks->input_methode="U";
        $tasks->save();

        foreach ($acsloadimports as $key => $acsloadimport) {
            if($acsloadimport->organization==null){ //data kosong
                continue;
            }
            $dataimport=new AccountDataLoadImport;
            $dataimport->comp_id=$compid;
            $dataimport->task_id=$tasks->id;
            $dataimport->organization_name= $acsloadimport->organization;
            $dataimport->location_name= $acsloadimport->location;  
            $dataimport->acc_style_caption= $acsloadimport->account_style_caption;
            $dataimport->acc_number= $acsloadimport->account_number;
            $dataimport->acc_reference= $acsloadimport->account_reference;
            $dataimport->acc_supplier= $acsloadimport->account_supplier; 
            $dataimport->record_date_start= \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($acsloadimport->record_start_yyyy_mm_dd);
            $dataimport->record_date_end=\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($acsloadimport->record_end_yyyy_mm_dd);
            $dataimport->acc_data_qty= $acsloadimport->quantity;
            $dataimport->acc_data_tot_cost= $acsloadimport->total_cost_incl_tax_in_local_currency; 
            $dataimport->record_reference= $acsloadimport->record_reference;
            $dataimport->record_inv_no= $acsloadimport->record_invoice_number;
            $dataimport->record_quality= $acsloadimport->record_data_quality; 
            $dataimport->save();
        }

        // Excel::import(new AccountDataLoadImportProcess, $fileloc);
        $processdataload=new ProcessDataLogs;
        $processdataload->comp_id=$company->id;
        $processdataload->connect_id='0';
        $processdataload->process_data_type="SUCCESS";
        $processdataload->process_data_origin='CLIENT UPLOAD';
        $processdataload->process_data_tittle="Success Import Process for ".$orgname;
        $processdataload->process_data_note="Success Imported from file to database...";
        $processdataload->process_data_process_state="SUCCESS";
        $processdataload->process_data_process_time=date("Y-m-d H:i:s");
        $processdataload->process_data_describe_state= "Import Success";
        $processdataload->save();

        return response()->json(['state' => 'success', 'message' => "Success Upload and save File", 'code' => 413]);
    }
    public function CreateNewTaskUploadS(Request $request){
        $prefix = 'S'; // SOSIAL
        $compid=$this->compid();
        $company=Companies::where('id',$compid)->first();  
        $orgname=Organization::where('id',$company->org_id)->first()->org_name;

        // Auto Number for TransNo -----------------------------------------------
        $year = date('y'); // Ambil 2 digit tahun terakhir
        $month = date('m'); // Ambil 2 digit bulan
        $orgId = Companies::where('id', $compid)->value('org_id');

        // Format org_id menjadi 3 digit dengan leading zero
        $orgIdFormatted = str_pad($orgId, 3, '0', STR_PAD_LEFT);
        $orgDateCode = $orgIdFormatted . '.' . $year . $month;
        
        // Ambil transaksi terakhir di bulan yang sama
        $lastTrans = Tasks::where('trans_no', 'like', "%$orgDateCode.%")
                            ->where(function($query) {
                                             $query->where('trans_no', 'like', 'S%'); 
                            })
                            ->orderBy('trans_no', 'desc')
                            ->value('trans_no');

        if ($lastTrans) {
                // Ambil nomor urut terakhir dan tambah 1
                $lastNumber = (int) substr($lastTrans, -6);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
                // Jika belum ada transaksi di bulan ini, mulai dari 0000001
                $newNumber = '000001';
        }

        // Format TransNo sesuai kebutuhan
        $TransNo = $prefix . $orgDateCode . '.' . $newNumber;
        // ------------------------------------------------------------------------

        $filexls = $request->file('files')[0];
        $new_name= "ACCDATACSR.".$filexls->getClientOriginalExtension();
        $tujuan_upload = public_path("images"); //daripada buat folder baru simpen di images ajah
        $filexls->move($tujuan_upload,$new_name); 
  
        $fileloc=$tujuan_upload."/".$new_name;
  
        // AccountDataLoadImport::truncate(); //pastikan data yang ada di hapus
        $accdataloadimports= Excel::toCollection(new AccountDataLoadImportCSRCollection, $fileloc);
        // $data = Excel::toCollection(null, $fileloc);
        // dd($accdataloadimports->keys());
        // dd($accdataloadimports["Records to load"]);

        $acsloadimports=json_decode(json_encode($accdataloadimports["Records to load"]),false);
        $orgfound=true;

        foreach ($acsloadimports as $key => $acsloadimport) {
            if($orgname!=$acsloadimport->organization){
                if($acsloadimport->organization==null){ //data kosong
                    continue;
                }
                $orgfound=false;
                break;
            }
        }
        // dd($orgfound);
        if(!$orgfound){
            $processdataload=new ProcessDataLogs;
            $processdataload->comp_id=$company->id;
            $processdataload->connect_id='0';
            $processdataload->process_data_type="FAILED";
            $processdataload->process_data_origin='CLIENT UPLOAD';
            $processdataload->process_data_tittle="Failed Process for ".$orgname;
            $processdataload->process_data_note="Organization Not Found, File uploaded but cant processed...";
            $processdataload->process_data_process_state="FAILED";
            $processdataload->process_data_process_time=date("Y-m-d H:i:s");
            $processdataload->process_data_describe_state= "Import failed file :".$fileloc ;
            $processdataload->save();
            // return false;
            return response()->json(['state' => 'error', 'message' => "Fail save file", 'code' => 413]);
        }

        $tasks=new Tasks;
        $tasks->trans_no=$TransNo;
        $tasks->comp_id=$compid;
        $tasks->task_name="Upload Account Data ".$company->comp_name;
        $tasks->task_maker_name=$this->name();
        $tasks->task_maker_message="Success prepared...";
        $tasks->task_maker_time=date("Y-m-d H:i:s");
        $tasks->task_progress="30%";
        $tasks->task_file_name=$new_name;
        $tasks->task_approval_type="APV-LEVEL";
        $tasks->task_last_message="Success prepared...";
        $tasks->task_state="1";
        $tasks->input_methode="U";
        $tasks->type_csr="Y"; // Y:CSR, N:Non CSR
        $tasks->save();

        foreach ($acsloadimports as $key => $acsloadimport) {
            if ($acsloadimport->organization == null) { // data kosong
                continue;
            }

            $data = new AccountDataLoadImportCSR();

            $data->comp_id = $compid;
            $data->task_id = $tasks->id;

            $data->organization_name      = $acsloadimport->organization ?? '';
            $data->location_name          = $acsloadimport->location ?? '';
            $data->acc_style_caption      = $acsloadimport->account_style_caption ?? '';
            $data->record_date_start      = $acsloadimport->{'record_start_yyyy-mm-dd'} ?? null;
            $data->record_date_end        = $acsloadimport->{'record_end_yyyy-mm-dd'} ?? null;

            $data->csr_total              = $acsloadimport->employee_total ?? 0;
            $data->csr_male               = $acsloadimport->male ?? 0;
            $data->csr_female             = $acsloadimport->female ?? 0;

            $data->csr_less_30            = $acsloadimport->{'age < 30'} ?? 0;
            $data->csr_between_30_50      = $acsloadimport->{'age 30 - 50'} ?? 0;
            $data->csr_more_50            = $acsloadimport->{'age > 50'} ?? 0;

            $data->csr_phd                = $acsloadimport->{'education phd'} ?? 0;
            $data->csr_post_graduate      = $acsloadimport->{'education post graduate'} ?? 0;
            $data->csr_bachelor_degree    = $acsloadimport->{'education baachelor degree'} ?? 0;
            $data->csr_high_school        = $acsloadimport->{'education high school'} ?? 0;
            $data->csr_junior_high_school = $acsloadimport->{'education junior high school'} ?? 0;
            $data->csr_elementary_school  = $acsloadimport->{'education elementary school'} ?? 0;
            $data->csr_eduction_other     = $acsloadimport->{'education others'} ?? 0;

            $data->csr_budha              = $acsloadimport->{'religius buddha'} ?? 0;
            $data->csr_hindu              = $acsloadimport->{'religius hindu'} ?? 0;
            $data->csr_islam              = $acsloadimport->{'religius moslem'} ?? 0;
            $data->csr_katolik            = $acsloadimport->{'religius catholic'} ?? 0;
            $data->csr_kristen            = $acsloadimport->{'religius christian'} ?? 0;
            $data->csr_religion_other     = $acsloadimport->{'religius others'} ?? 0;

            $data->save();
        }


        // Excel::import(new AccountDataLoadImportProcess, $fileloc);
        $processdataload=new ProcessDataLogs;
        $processdataload->comp_id=$company->id;
        $processdataload->connect_id='0';
        $processdataload->process_data_type="SUCCESS";
        $processdataload->process_data_origin='CLIENT UPLOAD';
        $processdataload->process_data_tittle="Success Import Process for ".$orgname;
        $processdataload->process_data_note="Success Imported from file to database...";
        $processdataload->process_data_process_state="SUCCESS";
        $processdataload->process_data_process_time=date("Y-m-d H:i:s");
        $processdataload->process_data_describe_state= "Import Success";
        $processdataload->save();

        return response()->json(['status' => true, 'message' => 'File uploaded successfully', ]);
        // return response()->json(['state' => 'success', 'message' => "Success Upload and save File", 'code' => 413]);
    }
    public function CreateNewTaskS(Request $request)
    {
        DB::beginTransaction();
        try {
            $prefix = 'S'; // SOSIAL 
            $compid = Auth::user()->company->id;
            $orgId = Companies::where('id', $compid)->value('org_id');
            $orgName = Organization::where('id', $orgId)->value('org_name');

            $year = date('y');
            $month = date('m');

            // Format org_id menjadi 3 digit dengan leading zero
            $orgIdFormatted = str_pad($orgId, 3, '0', STR_PAD_LEFT);
            $orgDateCode = $orgIdFormatted . '.' . $year . $month;

            // Ambil transaksi terakhir di bulan yang sama
            $lastTrans = Tasks::where('trans_no', 'like', "%$orgDateCode.%")
                            ->where(function($query) {
                                             $query->where('trans_no', 'like', 'S%'); 
                })
                ->orderBy('trans_no', 'desc')
                ->value('trans_no');

            if ($lastTrans) {
                // Ambil nomor urut terakhir dan tambah 1
                $lastNumber = (int) substr($lastTrans, -6);
                $newNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
            } else {
                // Jika belum ada transaksi di bulan ini, mulai dari 0000001
                $newNumber = '000001';
            }

            // Format TransNo sesuai kebutuhan
            $TransNo = $prefix . $orgDateCode . '.' . $newNumber;

            $task = Tasks::create([
                'trans_no'=> $TransNo, 
                'comp_id' => $compid,
                'task_name' => 'Data Capture ' . $orgName,
                'task_maker_name' => Auth::user()->name,
                'task_maker_message' => 'Task Created',
                'task_maker_time' => now(),
                'task_progress' => '0%',
                'task_last_message' => 'Task Initialized',
                'task_approval_type' => 'APV-LEVEL',
                'task_state' => '1',
                'input_methode' => 'C',
                'type_csr' => 'Y',
            ]);

            $task_id = $task->id;
            $tasks = $request->input('tasks');  
            $insertData = [];

            foreach ($tasks as $taskDetail) {
                $locationName = Location::where('location_name', $taskDetail['location_name'])->value('location_name');
                $accStyleCaption = AccountStyleCompanies::where('acc_style_comp_caption', $taskDetail['acc_style_caption'])->value('acc_style_comp_caption');

                $insertData[] = [
                    'comp_id' => $compid,
                    'task_id' => $task_id,
                    'organization_name' => $orgName,
                    'location_name' => $locationName,
                    'acc_style_caption' => $taskDetail['acc_style_caption'], //$accStyleCaption,
                    'record_date_start' => date('Y-m-d H:i:s', strtotime($taskDetail['record_date_start'])),
                    'record_date_end' => date('Y-m-d H:i:s', strtotime($taskDetail['record_date_end'])),
                    'csr_total' => ($taskDetail['csr_male'] ?? 0) + ($taskDetail['csr_female'] ?? 0), // opsional
                    'csr_male' => $taskDetail['csr_male'] ?? 0,
                    'csr_female' => $taskDetail['csr_female'] ?? 0,
                    'csr_less_30' => $taskDetail['csr_less_30'] ?? 0,
                    'csr_between_30_50' => $taskDetail['csr_between_30_50'] ?? 0,
                    'csr_more_50' => $taskDetail['csr_more_50'] ?? 0,
                    'csr_phd' => $taskDetail['csr_phd'] ?? 0,
                    'csr_post_graduate' => $taskDetail['csr_post_graduate'] ?? 0,
                    'csr_bachelor_degree' => $taskDetail['csr_bachelor_degree'] ?? 0,
                    'csr_high_school' => $taskDetail['csr_high_school'] ?? 0,
                    'csr_junior_high_school' => $taskDetail['csr_junior_high_school'] ?? 0,
                    'csr_elementary_school' => $taskDetail['csr_elementary_school'] ?? 0,
                    'csr_eduction_other' => $taskDetail['csr_eduction_other'] ?? 0,
                    'csr_islam' => $taskDetail['csr_islam'] ?? 0,
                    'csr_budha' => $taskDetail['csr_budha'] ?? 0,
                    'csr_hindu' => $taskDetail['csr_hindu'] ?? 0,
                    'csr_katolik' => $taskDetail['csr_katolik'] ?? 0,
                    'csr_kristen' => $taskDetail['csr_kristen'] ?? 0,
                    'csr_religion_other' => $taskDetail['csr_religion_other'] ?? 0,
                ];
            }

            AccountDataLoadImportCSR::insert($insertData);

            DB::commit();
            return response()->json(['message' => 'Data berhasil disimpan!'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    #--- Task CRUD : Load Task Selected  
    public function TaskSelectedLoadE($taskid,$transno){
        // dd($taskid,$transno);
        $compid = Auth::user()->company->id;
        $accstyleimports = AccountDataLoadImport::from('account_data_load_import as adi')
        ->leftJoin('account_style_companies as mst', 'mst.acc_style_comp_caption', '=', 'adi.acc_style_caption') // Step 1 
        ->select('adi.*', 'mst.acc_style_comp_reference as acc_style_mtra_caption')
        ->where([
            ['adi.comp_id', '=', $compid],
            ['adi.task_id', '=', $taskid],
            ['mst.comp_id', '=', $compid],
        ])
        ->orderBy('adi.id', 'asc')
        ->get();
        // dd($accstyleimports);
        //----
        // $compid = Auth::user()->company->id;
        // $accstyleimports = AccountDataLoadImport::from('account_data_load_import as adi')
        // ->leftJoin('account_styles as mst', 'mst.acc_style_caption', '=', 'adi.acc_style_caption') // Step 1
        // ->leftJoin('account_style_mapping as asm', 'asm.acc_style_comp_id', '=', 'mst.id')        // Step 2
        // ->select('adi.*', 'asm.acc_style_mtra_caption')
        // ->where([
        //     ['adi.comp_id', '=', $compid],
        //     ['adi.task_id', '=', $taskid],
        //     ['asm.comp_id', '=', $compid],
        // ])
        // ->orderBy('adi.id', 'asc')
        // ->get();
        //-----
        // $accstyleimports = AccountDataLoadImport::from('account_data_load_import as adi')
        // ->where([
        //     ['adi.comp_id', $compid],
        //     ['adi.task_id', $taskid]
        // ])
        // ->leftJoin('account_styles as mst', 'mst.acc_style_caption', '=', 'adi.acc_style_caption') // Step 1
        // ->leftJoin('account_style_mapping as asm', 'asm.acc_style_comp_id', '=', 'mst.id')        // Step 2
        // ->select('adi.*', 'asm.acc_style_mtra_caption')
        // ->where(['asm.comp_id', $this->compid()],['adi.task_id', $taskid])
        // ->orderBy('adi.id', 'asc')
        // ->get(); 
        // dd($accstyleimports);
        return $accstyleimports;    
    }
    public function TaskSelectedLoadS($taskid,$transno){
        $compid = Auth::user()->company->id;
        // dd($taskid,$transno, $compid);
        $accstyleimports = AccountDataLoadImportCSR::from('account_data_load_import_csr as adi')
        ->leftJoin('account_style_companies as mst', 'mst.acc_style_comp_caption', '=', 'adi.acc_style_caption') // Step 1 
        ->select('adi.*', 'mst.acc_style_comp_reference as acc_style_mtra_caption')
        ->where([
            ['adi.comp_id', '=', $compid],
            ['adi.task_id', '=', $taskid],
            ['mst.comp_id', '=', $compid],
        ])
        ->orderBy('adi.id', 'asc')
        ->get();
        // dd($accstyleimports);
        return $accstyleimports;    
    }
    // public function TaskSelectedLoadCSR($taskid,$transno){
    //     $compid = Auth::user()->company->id;
    //     // dd($taskid,$transno, $compid);
    //     $accstyleimports = AccountDataLoadImportCSR::from('account_data_load_import_csr as adi')
    //     ->where([
    //         ['adi.comp_id', $compid],
    //         ['adi.task_id', $taskid]
    //     ])
    //     ->leftJoin('account_styles as mst', 'adi.acc_style_caption', '=', 'mst.acc_style_caption') // Step 1
    //     ->leftJoin('account_style_mapping as asm', 'asm.acc_style_comp_id', '=', 'mst.id')        // Step 2
    //     ->select('adi.*', 'asm.acc_style_mtra_caption')
    //     ->where('asm.comp_id', $compid)
    //     ->orderBy('adi.id', 'asc')
    //     ->get(); 
    //     // dd($accstyleimports);
    //     return $accstyleimports;    
    // }
    #--- Task CRUD : Update Task Selected 
    public function TaskSelectedUpdate(Request $request, $id){ 
        $taskDetail = AccountDataLoadImport::findOrFail($id);
        $taskDetail->update($request->all());

        if ($taskDetail->task_id) {
            $taskHeader = Tasks::find($taskDetail->task_id);
            if ($taskHeader) {
                $taskHeader->update([
                    'task_checker_name' => Auth::user()->name,
                    'task_checker_time' => now() // Atau pakai $request->task_checker_time kalau datanya dari request
                ]);
            }
        }

        return response()->json(['message' => 'Task updated successfully']);
    }
    public function TaskSelectedUpdateCSR(Request $request, $id){ 
        $taskDetail = AccountDataLoadImportCSR::findOrFail($id);
        $taskDetail->update($request->all());

        if ($taskDetail->task_id) {
            $taskHeader = Tasks::find($taskDetail->task_id);
            if ($taskHeader) {
                $taskHeader->update([
                    'task_checker_name' => Auth::user()->name,
                    'task_checker_time' => now() // Atau pakai $request->task_checker_time kalau datanya dari request
                ]);
            }
        }

        return response()->json(['message' => 'Task updated successfully']);
    }
    #--- Task CRUD : Update Task Selected New Row 
    public function TaskSelectedInsertRow(Request $request)
    {
        DB::beginTransaction();
        try{
            $compid = Auth::user()->company->id; //compid(); // atau auth()->user()->comp_id;  
            $orgId = Companies::where('id', $compid)->value('org_id');
            $orgName = Organization::where('id', $orgId)->value('org_name'); 

            // Ambil data dari request  
            // dd("Raw Request:", $request->all(), "JSON:", json_decode($request->getContent(), true));
            // dd($request->all());
            $vDta = $request->input('data');
                         
            $locationName = Location::where('location_name', $vDta['location_name'])->value('location_name'); 
            $accStyleCaption = AccountStyleCompanies::where('acc_style_comp_caption', $vDta['acc_style_caption'])->value('acc_style_comp_caption');
            $insertData[] = [
                'comp_id' => $compid,
                'task_id' => $vDta["task_id"], // Gunakan task_id dari header yang baru dibuat
                'organization_name' => $orgName,
                'location_name' => $locationName, // $taskDetail['location_name'],
                'acc_style_caption' => $vDta['acc_style_caption'], // $accStyleCaption,
                'acc_number' => $vDta['acc_number'],
                'acc_supplier' => $vDta['acc_supplier'],
                'record_date_start' => date('Y-m-d H:i:s', strtotime($vDta['record_date_start'])),
                'record_date_end' => date('Y-m-d H:i:s', strtotime($vDta['record_date_end'])), 
                'acc_data_qty' => (float) $vDta['acc_data_qty'],
                'acc_data_tot_cost' => (float) $vDta['acc_data_tot_cost'], 
            ];
            
        
            AccountDataLoadImport::insert($insertData);
            DB::commit();
            return response()->json(["message" => "Data save succesfully "], 201);
        }catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Error ' . $e->getMessage()], 500);
        }
    }
    public function TaskSelectedInsertRowCSR(Request $request)
    {
        DB::beginTransaction();
        try {
            $compid = Auth::user()->company->id;
            $orgId = Companies::where('id', $compid)->value('org_id');
            $orgName = Organization::where('id', $orgId)->value('org_name');

            $vDta = $request->input('data');

            $locationName = Location::where('location_name', $vDta['location_name'])->value('location_name');
            $accStyleCaption = AccountStyleCompanies::where('acc_style_comp_caption', $vDta['acc_style_caption'])->value('acc_style_comp_caption');

            $insertData[] = [
                'comp_id' => $compid,
                'task_id' => $vDta["task_id"],
                'organization_name' => $orgName,
                'location_name' => $locationName,
                'acc_style_caption' => $vDta['acc_style_caption'], //$accStyleCaption,
                'record_date_start' => date('Y-m-d H:i:s', strtotime($vDta['record_date_start'])),
                'record_date_end' => date('Y-m-d H:i:s', strtotime($vDta['record_date_end'])),
                'csr_total' => ($vDta['csr_male'] ?? 0) + ($vDta['csr_female'] ?? 0),
                'csr_male' => (int) $vDta['csr_male'],
                'csr_female' => (int) $vDta['csr_female'],
                'csr_less_30' => (int) $vDta['csr_less_30'],
                'csr_between_30_50' => (int) $vDta['csr_between_30_50'],
                'csr_more_50' => (int) $vDta['csr_more_50'],
                'csr_phd' => (int) $vDta['csr_phd'],
                'csr_post_graduate' => (int) $vDta['csr_post_graduate'],
                'csr_bachelor_degree' => (int) $vDta['csr_bachelor_degree'],
                'csr_high_school' => (int) $vDta['csr_high_school'],
                'csr_junior_high_school' => (int) $vDta['csr_junior_high_school'],
                'csr_elementary_school' => (int) $vDta['csr_elementary_school'],
                'csr_eduction_other' => (int) $vDta['csr_eduction_other'],
                'csr_islam' => (int) $vDta['csr_islam'],
                'csr_budha' => (int) $vDta['csr_budha'],
                'csr_hindu' => (int) $vDta['csr_hindu'],
                'csr_katolik' => (int) $vDta['csr_katolik'],
                'csr_kristen' => (int) $vDta['csr_kristen'],
                'csr_religion_other' => (int) $vDta['csr_religion_other'], 
            ];

            AccountDataLoadImportCSR::insert($insertData);

            DB::commit();
            return response()->json(["message" => "Data CSR berhasil disimpan"], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Gagal menyimpan data CSR: ' . $e->getMessage()], 500);
        }
    }

    #--- Task CRUD : Delete Task Selected Row  
    public function TaskSelectedUpdateDel($id)
    {
        try {
            $task = AccountDataLoadImport::findOrFail($id);
            $task->delete();
            return response()->json(['success' => 'Data successfully deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete data: ' . $e->getMessage()], 500);
        }
    }
    public function TaskSelectedUpdateDelCSR($id)
    {
        try {
            $task = AccountDataLoadImportCSR::findOrFail($id);
            $task->delete();
            return response()->json(['success' => 'Data successfully deleted']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete data: ' . $e->getMessage()], 500);
        }
    }
    #--- Task CRUD : Update Task 
    public function updateTaskStateUp(Request $request)
    {
        // dd($request);
        $task = Tasks::find((int) $request->task_id); 
        // dd($request); 
        if (!$task) {
            return response()->json(['error' => 'Task not found', 'id' => $request->task_id], 404);
        }

        // Konversi dari string ke angka task_state
        $taskStateValue = $this->getTaskStateValue($request->task_state);
        // dd($taskStateValue);
        if ($taskStateValue === null) {
            return response()->json(['error' => 'Invalid task state'], 400);
        }
        // Change Status From 'Ready to Submit' into 'Ready to Verify', name & time is already process when save by user  
        
        if ($taskStateValue == 2){ // Change Status From 'Ready to Verify' into 'Ready to Approve' 
            $task->task_maker_name = Auth::user()->name;    
            // $task->task_maker_time = now(); tanggal pembuatan tetap di pertahankan 
            $task->task_maker_message = 'Task Already Submitted';
            $task->task_last_message = 'Task Already Submitted';
        }
        if ($taskStateValue == 3){ // Change Status From 'Ready to Verify' into 'Ready to Approve' 
            $task->task_checker_name = Auth::user()->name;    
            $task->task_checker_time = now();
            $task->task_checker_message = 'Task Already Verified';
            $task->task_last_message = 'Task Already Verified';
        }
        if ($taskStateValue == 4){ // Change Status from 'Ready to Approve' into 'Approved'
            $task->task_approval_name = Auth::user()->name;  
            $task->task_approval_time = now() ;
            $task->task_approval_message = 'Task Already Approved';
            $task->task_last_message = 'Task Already Approved';
        } 

        $task->task_state = $taskStateValue;
        $task->save();

        return response()->json(['success' => 'Task state updated successfully', 'task_state' => $task->task_state]);
    }
    public function updateTaskStateDown(Request $request)
    {
        // dd($request);
        $task = Tasks::find((int) $request->task_id);
        $reason = $request->reason;
        // dd($request); 
        if (!$task) {
            return response()->json(['error' => 'Task not found', 'id' => $request->task_id], 404);
        }

        // Konversi dari string ke angka task_state
        $taskStateValue = $this->getTaskStateValue($request->task_state);
        // dd($taskStateValue);
        if ($taskStateValue === null) {
            return response()->json(['error' => 'Invalid task state'], 400);
        }
        // Change Status From 'Ready to Submit' into 'Ready to Verify', name & time is already process when save by user  
        if ($taskStateValue == 1) { // Change Status
            $task->task_checker_time = Auth::user()->name ; 
            $task->task_checker_time = now();
            $task->task_checker_message = Auth::user()->name . ': Task Revision Request';
            $task->task_last_message = Auth::user()->name . ': ' . ($reason ?? '');
            $task->task_state = $taskStateValue;
        }        
        if ($taskStateValue == 2){ // Change Status From 'Ready to Approve' into  'Ready to Verify'
            $task->task_approval_time = Auth::user()->name ; 
            $task->task_approval_time = now();
            $task->task_approval_message = Auth::user()->name . ': Task Revision Request';
            $task->task_last_message = Auth::user()->name . ': ' . ($reason ?? '');
            $task->task_state = '1' ;
        }
        
        $task->save();

        return response()->json(['success' => 'Task state updated successfully', 'task_state' => $task->task_state]);
    }
    public function updateTaskStateApprovalE(Request $request){
        $task = Tasks::find((int) $request->task_id);
    
        if (!$task) {
            return response()->json(['error' => 'Task not found', 'id' => $request->task_id], 404);
        }
    
        $taskStateValue = $this->getTaskStateValue($request->task_state);
        if ($taskStateValue === null) {
            return response()->json(['error' => 'Invalid task state'], 400);
        }
    
        // Status transitions
        if ($taskStateValue == 2) {
            $task->task_maker_name = Auth::user()->name;
            $task->task_maker_message = 'Task Already Submitted';
            $task->task_last_message = 'Task Already Submitted';
        } elseif ($taskStateValue == 3) {
            $task->task_checker_name = Auth::user()->name;
            $task->task_checker_time = now();
            $task->task_checker_message = 'Task Already Verified';
            $task->task_last_message = 'Task Already Verified';
        } elseif ($taskStateValue == 4) {
            $task->task_approval_name = Auth::user()->name;
            $task->task_approval_time = now();
            $task->task_approval_message = 'Task Already Approved';
            $task->task_last_message = 'Task Already Approved';
    
            // Proses upload ke S3 dan clone data
            $company = Companies::find($this->compid());
            $org = Organization::find($company->org_id);
            $connector = Connector::where([
                ['comp_id', $this->compid()],
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
    
            // Create log proses upload
            $processdataload = new ProcessDataLogs();
            $processdataload->comp_id = $company->id;
            $processdataload->connect_id = $connector->id;
            $processdataload->process_data_type = "UPLOADED";
            $processdataload->process_data_origin = 'CLIENT UPLOAD';
            $processdataload->process_data_tittle = "Upload for " . $org->org_name;
            $processdataload->process_data_note = 'SUCCESS Upload Data using ' . $connector->connect_name . ' via ' . $connector->connect_protocol . ' driver, to ' . $connector->connect_storage_code;
            $processdataload->process_data_process_state = "SUCCESS";
            $processdataload->process_data_process_time = now();
            $processdataload->process_data_describe_state = "Success Upload from Client";
            $processdataload->save();
    
            $accdataloadimports = AccountDataLoadImport::where('task_id', $task->id)->get();
            $arrId = [];
    
            foreach ($accdataloadimports as $import) {
                $loc = Location::where('location_name', $import->location_name)->first();
                $style = AccountStyleCompanies::where('acc_style_comp_caption', $import->acc_style_caption)->first();
    
                $accdl = new AccountDataLoad();
                $accdl->org_id = $org->id;
                $accdl->loc_id = $loc->id ?? null;
                $accdl->comp_id = $company->id;
                $accdl->task_id = $import->task_id;
                $accdl->acc_style_id = $style->id ?? null;
                $accdl->organization_link = $org->org_link;
                $accdl->organization_name = $import->organization_name;
                $accdl->location_name = $import->location_name;
                $accdl->acc_style_link = $style->acc_style_comp_link ?? '';
                $accdl->acc_style_caption = $import->acc_style_caption;
                $accdl->acc_number = $import->acc_number;
                $accdl->acc_reference = $import->acc_reference;
                $accdl->acc_supplier = $import->acc_supplier;
                $accdl->record_date_start = $import->record_date_start;
                $accdl->record_date_end = $import->record_date_end;
                $accdl->acc_data_qty = $import->acc_data_qty;
                $accdl->acc_data_tot_cost = $import->acc_data_tot_cost;
                $accdl->record_reference = $import->record_reference;
                $accdl->record_inv_no = $import->record_inv_no;
                $accdl->record_quality = $import->record_quality;
                $accdl->save();
    
                $arrId[] = $accdl->id;
            }
    
            // test 1 ------------------------ Failed 
            // Simpan Excel ke S3
            // $folder = $connector->connect_remote_folder;
            $folder = rtrim($connector->connect_remote_folder, '/') . '/';
            $filename = $connector->connect_data_load_name;
            $storagecode = $connector->connect_storage_code;
            Excel::store(new TaskDetailExportE($task->id), $folder . $filename, $storagecode); 

            // Update status data
            AccountDataLoad::whereIn('id', $arrId)->update(['acc_data_state' => '2']);
            AccountDataLoadImport::where('task_id', $task->id)->update(['acc_data_state' => '3']);
        }
    
        $task->task_state = $taskStateValue;
        $task->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task_state' => $task->task_state
        ]);
    }
    public function updateTaskStateApprovalS(Request $request){
        $task = Tasks::find((int) $request->task_id);
    
        if (!$task) {
            return response()->json(['error' => 'Task not found', 'id' => $request->task_id], 404);
        }
    
        $taskStateValue = $this->getTaskStateValue($request->task_state);
        if ($taskStateValue === null) {
            return response()->json(['error' => 'Invalid task state'], 400);
        }
    
        // Status transitions
        if ($taskStateValue == 2) {
            $task->task_maker_name = Auth::user()->name;
            $task->task_maker_message = 'Task Already Submitted';
            $task->task_last_message = 'Task Already Submitted';
        } elseif ($taskStateValue == 3) {
            $task->task_checker_name = Auth::user()->name;
            $task->task_checker_time = now();
            $task->task_checker_message = 'Task Already Verified';
            $task->task_last_message = 'Task Already Verified';
        } elseif ($taskStateValue == 4) {
            $task->task_approval_name = Auth::user()->name;
            $task->task_approval_time = now();
            $task->task_approval_message = 'Task Already Approved';
            $task->task_last_message = 'Task Already Approved';
    
            // Proses upload ke S3 dan clone data
            $company = Companies::find($this->compid());
            $org = Organization::find($company->org_id);
            $connector = Connector::where([
                ['comp_id', $this->compid()],
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
    
            // Create log proses upload
            $processdataload = new ProcessDataLogs();
            $processdataload->comp_id = $company->id;
            $processdataload->connect_id = $connector->id;
            $processdataload->process_data_type = "UPLOADED";
            $processdataload->process_data_origin = 'CLIENT UPLOAD';
            $processdataload->process_data_tittle = "Upload for " . $org->org_name;
            $processdataload->process_data_note = 'SUCCESS Upload Data using ' . $connector->connect_name . ' via ' . $connector->connect_protocol . ' driver, to ' . $connector->connect_storage_code;
            $processdataload->process_data_process_state = "SUCCESS";
            $processdataload->process_data_process_time = now();
            $processdataload->process_data_describe_state = "Success Upload from Client";
            $processdataload->save();
    
            $accdataloadimports = AccountDataLoadImportCSR::where('task_id', $task->id)->get();
            $arrId = [];
    
            foreach ($accdataloadimports as $import) {
                $loc = Location::where('location_name', $import->location_name)->first();
                $style = AccountStyleCompanies::where('acc_style_comp_caption', $import->acc_style_caption)->first();
    
                $accdl = new AccountDataLoadCSR();
                $accdl->org_id = $org->id;
                $accdl->loc_id = $loc->id ?? null;
                $accdl->comp_id = $company->id;
                $accdl->task_id = $import->task_id;
                $accdl->acc_style_id = $style->id ?? null;
                $accdl->organization_link = $org->org_link;
                $accdl->organization_name = $import->organization_name;
                $accdl->location_name = $import->location_name;
                $accdl->location_ref = $import->location_ref ?? null;
                $accdl->acc_style_link = $style->acc_style_comp_link ?? '';
                $accdl->acc_style_caption = $import->acc_style_caption;
                $accdl->acc_subtype = $import->acc_subtype ?? null;
                $accdl->acc_number = $import->acc_number;
                $accdl->acc_reference = $import->acc_reference;
                $accdl->acc_supplier = $import->acc_supplier;
                $accdl->acc_reader = $import->acc_reader ?? null;
                $accdl->record_date_start = $import->record_date_start;
                $accdl->record_date_end = $import->record_date_end;
                $accdl->acc_data_qty = $import->acc_data_qty;
                $accdl->acc_data_tot_cost = $import->acc_data_tot_cost;
                $accdl->record_reference = $import->record_reference;
                $accdl->record_inv_no = $import->record_inv_no;
                $accdl->record_quality = $import->record_quality;
                // Tambahkan semua kolom CSR tambahan:
                $accdl->csr_total = $import->csr_total;
                $accdl->csr_male = $import->csr_male;
                $accdl->csr_female = $import->csr_female;
                $accdl->csr_less_30 = $import->csr_less_30;
                $accdl->csr_between_30_50 = $import->csr_between_30_50;
                $accdl->csr_more_50 = $import->csr_more_50;
                $accdl->csr_phd = $import->csr_phd;
                $accdl->csr_post_graduate = $import->csr_post_graduate;
                $accdl->csr_bachelor_degree = $import->csr_bachelor_degree;
                $accdl->csr_high_school = $import->csr_high_school;
                $accdl->csr_junior_high_school = $import->csr_junior_high_school;
                $accdl->csr_elementary_school = $import->csr_elementary_school;
                $accdl->csr_eduction_other = $import->csr_eduction_other;
                $accdl->csr_islam = $import->csr_islam;
                $accdl->csr_budha = $import->csr_budha;
                $accdl->csr_hindu = $import->csr_hindu;
                $accdl->csr_katolik = $import->csr_katolik;
                $accdl->csr_kristen = $import->csr_kristen;
                $accdl->csr_religion_other = $import->csr_religion_other;
                $accdl->csr_state = 1;
                $accdl->save();
    
                $arrId[] = $accdl->id;
            }
    
            // test 1 ------------------------ Failed 
            // Simpan Excel ke S3
            // $folder = $connector->connect_remote_folder;
            $folder = rtrim($connector->connect_remote_folder, '/') . '/';
            $filename = $connector->connect_data_load_name_csr;
            $storagecode = $connector->connect_storage_code; 
            Excel::store(new TaskDetailExportS($task->id), $folder . $filename, $storagecode); 

            // Update status data
            AccountDataLoadCSR::whereIn('id', $arrId)->update(['csr_state' => '2']);
            AccountDataLoadImportCSR::where('task_id', $task->id)->update(['csr_state' => '3']);
        }
    
        $task->task_state = $taskStateValue;
        $task->save();
    
        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'task_state' => $task->task_state
        ]);
    }
        
#--- Task CRUD CSR






// --- CSR BATAS
    #--- EXPORT DATA  
    public function exportExcel(Request $request)
    {
        $connector = Connector::where([
            ['comp_id', $this->compid()],
            ['connect_type', 'ENVIZI'],
            ['connect_protocol', 'S3']
        ])->first();
        $taskId = $request->task_id;
        $filename = $connector->connect_data_load_name;
        return Excel::download(new TaskDetailExportE($taskId), $filename);
    }
    #---
    public function makerTaskIndex(){
        $company=Companies::where('id',$this->compid())->first();
        //$tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-AUTO'],['task_state','1']])->get();
        $tasks=Tasks::where([['comp_id',$this->compid()]])->get();
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
            $task->input_methode=$this->taskMethodeDescription($task->input_methode);
        }
        return view("tasks/partner/task_index",compact('company','tasks'));
    }
    public function makerTaskUpload(){
        $tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-LEVEL'],['task_state','1']])->get();
        $company=Companies::where('id',$this->compid())->first();
        $org=Organization::where('id',$company->org_id)->first();
        $orgexist="EXIST";
        if(!$org){
            $orgexist="NOTEXIST";
        }
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }

        return view('tasks/partner/maker/task_index',compact('tasks','orgexist'));
    }

    public function makerDataCaptureIndex(){  
        return view("tasks/partner/maker/capture/list");
    }
    public function makerDataCaptureLoad(){

    }
    public function makerDataCaptureUpdate(){

    }
    public function makerDataCaptureSave(){

    }

    public function makerRetrieveTask(){
        $company=Companies::where('id',$this->compid())->first();
        $tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-AUTO'],['task_state','1']])->get();
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }
        return view('tasks/partner/maker/retrieve/list_task',compact('company','tasks'));
    }
    public function makerRetrieveDataOpen(Request $request){
        $taskid=$request->taskid; 
        $accstyleimports=AccountDataLoadImport::where([['comp_id',$this->compid()],['task_id',$taskid]])->get();

        //periksa apakah ada data di import yang belum di update account stylenya
        $notupdate=AccountDataLoadImport::where([['comp_id',$this->compid()],['task_id',$taskid],['acc_data_state','1']])->count();
        return view('tasks/partner/maker/retrieve/list_detail',compact('accstyleimports','taskid','notupdate'));
    }
    public function makerRetrieveDataMaping(Request $request){
        $taskid=$request->taskid;
        $state=$request->state;
        if($state=="MAPPING"){
            $accstyles=AccountStyles::where('acc_style_state','1')->get();
            $accstylegroups=AccountStyleGroupImport::where([['comp_id',$this->compid()],['task_id',$taskid]])->get();
            return view('tasks/partner/maker/retrieve/mapping',compact('accstyles','taskid'));
        }

        Tasks::where('id',$taskid)->update(["task_last_message"=>'Success Created, waiting for checker',
            "task_state"=>'2']);
        return redirect()->action([TasksController::class,'makerRetrieveTask']);
    }
    public function makerRetrieveLoad($taskid){
        // $accstylegroups=AccountStyleGroupImport::where([['comp_id',$this->compid()],['task_id',$taskid],
        //     ['acc_style_group_state','1']])->get();
        $accstylegroups=AccountStyleGroupImport::where([['comp_id',$this->compid()],['task_id',$taskid]])->get();
        return $accstylegroups;
    }

    public function makerRetrieveUpdate(Request $request,$accstyleid){
        $acc_style_caption=$request->acc_style_caption;
        AccountStyleGroupImport::where('id',$accstyleid)->update([
            'acc_style_caption'=>$acc_style_caption,
            'acc_style_group_state'=>'2',
        ]);

        //update account style company
        $acc_style_group=AccountStyleGroupImport::where('id',$accstyleid)->first();
        $taskid=$acc_style_group->task_id;
        $acc_style_caption_import=$acc_style_group->acc_style_caption_import;
        $adscomp=AccountStyleCompanies::where('acc_style_comp_caption',$acc_style_caption)->first();
        //jika tidak ada di Account Style Company buat baru, selain itu update
        if(!$adscomp){
            $accstyle=new AccountStyleCompanies;
            $accstyle->comp_id=$this->compid();
            $accstyle->acc_style_comp_caption=$acc_style_caption;
            $accstyle->acc_style_comp_caption_import=$acc_style_caption_import;
            $accstyle->save(); 
        }else{
            AccountStyleCompanies::where('acc_style_comp_caption',$acc_style_caption)
                ->update(['acc_style_comp_caption_import'=>$acc_style_caption_import]);
        }
       
        //update import list
        AccountDataLoadImport::where([['task_id',$taskid],['acc_style_caption',$acc_style_caption_import]])
            ->update(['acc_style_caption'=>$acc_style_caption,'acc_data_state'=>'2']);
        return response()->json(['status' => 'success', 'message' => 'Success Update', 'code' => 200]);
    }
    public function makerUploadTask(){
        $tasks=Tasks::where([['comp_id',$this->compid()],['task_approval_type','APV-LEVEL'],['task_state','1']])->get();
        $company=Companies::where('id',$this->compid())->first();
        $org=Organization::where('id',$company->org_id)->first();
        $orgexist="EXIST";
        if(!$org){
            $orgexist="NOTEXIST";
        }
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }

        return view('tasks/partner/maker/list_task',compact('tasks','orgexist'));
    }
    public function makerUploadMain(Request $request){
        $state=$request->state;
        if($state=="DATACREATOR"){
            // return view("tasks/partner/maker/upload/file-upload");
        }
        if($state=="FILEUPDATE"){
            return view("tasks/partner/maker/upload/file-upload");
        }
        if($state=="OPEN"){
            $taskid=$request->taskid; 
            $task=Tasks::where('id',$taskid)->first();
            $accstyleimports=AccountDataLoadImport::where([['comp_id',$this->compid()],['task_id',$taskid]])->get();
            return view('tasks/partner/maker/upload/list_detail',compact('accstyleimports','task'));
        }
    }
    public function makerUploadFileOpen(){
        return view("tasks/partner/maker/upload/file-upload");
    }
    public function makerUploadFileProcess(Request $request){
        $compid=$this->compid();
        $company=Companies::where('id',$compid)->first();  
        $orgname=Organization::where('id',$company->org_id)->first()->org_name;

        // Auto Number for TransNo -----------------------------------------------
        $year = date('y'); // Ambil 2 digit tahun terakhir
        $month = date('m'); // Ambil 2 digit bulan
        $orgId = Companies::where('id', $compid)->value('org_id');

        // Format org_id menjadi 3 digit dengan leading zero
        $orgIdFormatted = str_pad($orgId, 3, '0', STR_PAD_LEFT);

        // Ambil transaksi terakhir di bulan yang sama
        $lastTrans = Tasks::where('trans_no', 'like', "$orgIdFormatted.$year$month.%")
                            ->orderBy('trans_no', 'desc')
                            ->value('trans_no');

        if ($lastTrans) {
            // Ambil nomor urut terakhir dan tambah 1
            $lastNumber = (int) substr($lastTrans, -7);
            $newNumber = str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
        } else {
            // Jika belum ada transaksi di bulan ini, mulai dari 0000001
            $newNumber = '0000001';
        }

        // Format TransNo sesuai kebutuhan
        $TransNo = "$orgIdFormatted.$year$month.$newNumber"; 
        // ------------------------------------------------------------------------

        $filexls = $request->file('files')[0];
        $new_name= "ACCDATALOAD.".$filexls->getClientOriginalExtension();
        $tujuan_upload = public_path("images"); //daripada buat folder baru simpen di images ajah
        $filexls->move($tujuan_upload,$new_name); 
  
        $fileloc=$tujuan_upload."/".$new_name;
  
        // AccountDataLoadImport::truncate(); //pastikan data yang ada di hapus
        $accdataloadimports= Excel::toCollection(new AccountDataLoadImportCollection, $fileloc);
        // dd($accdataloadimports["Records to load"]);

        $acsloadimports=json_decode(json_encode($accdataloadimports["Records to load"]),false);
        $orgfound=true;

        foreach ($acsloadimports as $key => $acsloadimport) {
            if($orgname!=$acsloadimport->organization){
                if($acsloadimport->organization==null){ //data kosong
                    continue;
                }
                $orgfound=false;
                break;
            }
        }

        if(!$orgfound){
            $processdataload=new ProcessDataLogs;
            $processdataload->comp_id=$company->id;
            $processdataload->connect_id='0';
            $processdataload->process_data_type="FAILED";
            $processdataload->process_data_origin='CLIENT UPLOAD';
            $processdataload->process_data_tittle="Failed Process for ".$orgname;
            $processdataload->process_data_note="Organization Not Found, File uploaded but cant processed...";
            $processdataload->process_data_process_state="FAILED";
            $processdataload->process_data_process_time=date("Y-m-d H:i:s");
            $processdataload->process_data_describe_state= "Import failed file :".$fileloc ;
            $processdataload->save();
            // return false;
            return response()->json(['state' => 'error', 'message' => "Fail save file", 'code' => 413]);
        }

        $tasks=new Tasks;
        $tasks->trans_no=$TransNo;
        $tasks->comp_id=$compid;
        $tasks->task_name="Upload Account Data ".$company->comp_name;
        $tasks->task_maker_name=$this->name();
        $tasks->task_maker_message="Success prepared...";
        $tasks->task_maker_time=date("Y-m-d H:i:s");
        $tasks->task_progress="30%";
        $tasks->task_file_name=$new_name;
        $tasks->task_approval_type="APV-LEVEL";
        $tasks->task_last_message="Success prepared...";
        $tasks->task_state="1";
        $tasks->input_methode="U";
        $tasks->save();

        foreach ($acsloadimports as $key => $acsloadimport) {
            if($acsloadimport->organization==null){ //data kosong
                continue;
            }
            $dataimport=new AccountDataLoadImport;
            $dataimport->comp_id=$compid;
            $dataimport->task_id=$tasks->id;
            $dataimport->organization_name= $acsloadimport->organization;
            $dataimport->location_name= $acsloadimport->location;  
            $dataimport->acc_style_caption= $acsloadimport->account_style_caption;
            $dataimport->acc_number= $acsloadimport->account_number;
            $dataimport->acc_reference= $acsloadimport->account_reference;
            $dataimport->acc_supplier= $acsloadimport->account_supplier; 
            $dataimport->record_date_start= \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($acsloadimport->record_start_yyyy_mm_dd);
            $dataimport->record_date_end=\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($acsloadimport->record_end_yyyy_mm_dd);
            $dataimport->acc_data_qty= $acsloadimport->quantity;
            $dataimport->acc_data_tot_cost= $acsloadimport->total_cost_incl_tax_in_local_currency; 
            $dataimport->record_reference= $acsloadimport->record_reference;
            $dataimport->record_inv_no= $acsloadimport->record_invoice_number;
            $dataimport->record_quality= $acsloadimport->record_data_quality; 
            $dataimport->save();
        }

        // Excel::import(new AccountDataLoadImportProcess, $fileloc);
        $processdataload=new ProcessDataLogs;
        $processdataload->comp_id=$company->id;
        $processdataload->connect_id='0';
        $processdataload->process_data_type="SUCCESS";
        $processdataload->process_data_origin='CLIENT UPLOAD';
        $processdataload->process_data_tittle="Success Import Process for ".$orgname;
        $processdataload->process_data_note="Success Imported from file to database...";
        $processdataload->process_data_process_state="SUCCESS";
        $processdataload->process_data_process_time=date("Y-m-d H:i:s");
        $processdataload->process_data_describe_state= "Import Success";
        $processdataload->save();

        return response()->json(['state' => 'success', 'message' => "Success Upload and save File", 'code' => 413]);
    }
    public function makerUploadCreated(Request $request, string $id){
        // $taskid=$request->id;
        // $task=Tasks::where('id',$id)->first();
        Tasks::where('id',$id)->update([
            "task_maker_name" => $this->name(),
            "task_maker_message" => "Success created, waiting for checker...",
            "task_maker_time" => date("Y-m-d H:i:s"),
            "task_progress" => "30%",
            "task_last_message"=>"Success created, waiting for checker...",
            "task_state" => "2",
        ]);

        //send ke email atasannya (belum bisa ke banyak penerima, sementara single email dulu)
        
        $emails=array();
        $task=Tasks::with('company')->where('id',$id)->first();
        $compid=$task->comp_id;
        $users=User::where('comp_id',$compid)->get(); 
        foreach ($users as $key => $user) {
            $emails[]=[$user->email];
            $task->task_checker_name=$user->name;
        }

        // sementara single email dulu)
        $email=Companies::where('id',$compid)->first()->comp_email;
        Mail::to($email)->send(new TaskMakerEmail($task));
        return response()->json(['status' => 'success', 'message' => 'Data successfully Created', 'code' => 200]);
    }
    #----ROLE TASK VERIFICATION 
    public function verifyTaskMainE(){  
        $tasks = Tasks::where([['comp_id', $this->compid()],['type_csr', 'N']])
                      ->leftJoin(DB::raw('(SELECT task_id, SUM(acc_data_tot_cost) as total_cost 
                                           FROM account_data_load_import 
                                           GROUP BY task_id) as acc_data'), 
                                function($join) {
                                    $join->on('tasks.id', '=', 'acc_data.task_id');
                                })
                      ->whereIn('task_state', ['2', '3', '4', '5'])
                      ->orderby('task_maker_time', 'desc')
                      ->select('tasks.*', DB::raw('COALESCE(acc_data.total_cost, 0) as total_cost'))
                      ->get();
        
        $company = Companies::where('id', $this->compid())->first();
        $org = Organization::where('id', $company->org_id)->first();
        $orgexist = $org ? "EXIST" : "NOTEXIST";

        foreach ($tasks as $key => $task) {
            $task->task_state = $this->taskStateDescription($task->task_state);
            $task->input_methode = $this->taskMethodeDescription($task->input_methode);
        }

        return view('tasks/partner/verify/verify_task_listE',compact('tasks'));
    }
    public function verifyTaskMainS(){
        $tasks = Tasks::where([['comp_id', $this->compid()],['type_csr', 'Y']])
                      ->leftJoin(DB::raw('(SELECT task_id, SUM(acc_data_tot_cost) as total_cost 
                                           FROM account_data_load_import 
                                           GROUP BY task_id) as acc_data'), 
                                function($join) {
                                    $join->on('tasks.id', '=', 'acc_data.task_id');
                                })
                      ->whereIn('task_state', ['2', '3', '4', '5'])
                      ->orderby('task_maker_time', 'desc')
                      ->select('tasks.*', DB::raw('COALESCE(acc_data.total_cost, 0) as total_cost'))
                      ->get();
        $company = Companies::where('id', $this->compid())->first();
        $org = Organization::where('id', $company->org_id)->first();
        $orgexist = $org ? "EXIST" : "NOTEXIST";

        foreach ($tasks as $key => $task) {
            $task->task_state = $this->taskStateDescription($task->task_state);
            $task->input_methode = $this->taskMethodeDescription($task->input_methode);
        }

        return view('tasks/partner/verify/verify_task_listS',compact('tasks'));
    }

    public function verifyTaskCRUDE(Request $request){
        $state=$request->state;
        
        if($state=="OPEN"){
            $taskid = $request->taskid;
            $task = Tasks::where('id', $taskid)->first();           
            $accstyleimports = AccountDataLoadImport::where([
                ['comp_id', $this->compid()],
                ['task_id', $taskid]
            ])->get();
            return view('tasks/partner/verify/verify_task_openE', compact('task'));
        }
        
    }    
    public function verifyTaskCRUDS(Request $request){
        $state=$request->state;
        
        if($state=="OPEN"){
            $taskid = $request->taskid;
            $task = Tasks::where('id', $taskid)->first();           
            $accstyleimports = AccountDataLoadImportCSR::where([
                ['comp_id', $this->compid()],
                ['task_id', $taskid]
            ])->get();
            return view('tasks/partner/verify/verify_task_openS', compact('task'));
        }
        
    }
    #----ROLE CHECKER
    public function checkerTask(){
        $company=Companies::where('id',$this->compid())->first();
        $tasks = Tasks::where('comp_id', $this->compid())
                      ->whereIn('task_state', ['2', '3', '4', '5'])
                      ->get();
        foreach ($tasks as $key => $task) {
            $task->task_state=$this->taskStateDescription($task->task_state);
        }
        return view('tasks/partner/checker/checker_task_list',compact('tasks'));
        // return view('tasks/partner/checker/list_task',compact('tasks'));
    }

    public function checkerMain(Request $request){
        $taskid=$request->taskid;
        $task=Tasks::where('id',$taskid)->first();
        $accdataloadimport=AccountDataLoadImport::where('task_id',$taskid)->get();
        return view('tasks/partner/checker/list_detail',compact('accdataloadimport','task'));
    }

    public function checkerContinue(Request $request, string $id){
        $taskid=$request->id;
        $task=Tasks::where('id',$id)->first();

        Tasks::where('id',$id)->update([
            "task_checker_name" => $this->name(),
            "task_checker_message" => "Data has been check, waiting to approve and submit...",
            "task_checker_time" => date("Y-m-d H:i:s"),
            "task_progress" => "60%",
            "task_last_message"=>"Data has been check, waiting to approve and submit...",
            "task_state" => "3",
        ]);
        return response()->json(['status' => 'success', 'message' => 'Data successfully checked', 'code' => 200]);
    }

    public function checkerReject(Request $request){
        $taskid=$request->taskid;
        $message=$request->reject_reason;
        $uploadtask=Tasks::where('id',$taskid)->first();

        Tasks::where('id',$taskid)->update([
            "task_checker_name" => $this->name(),
            "task_checker_message" => "Data has been Rejected...",
            "task_checker_time" => date("Y-m-d H:i:s"),
            "task_progress" => "0%",
            "task_last_message"=>$message,
            "task_state" => "5",
        ]);
 
        return response()->json(['status' => 'success', 'message' => 'Data successfully rejected', 'code' => 200]);
    }

#----ROLE APPROVAL
#----ROLE TASK VERIFICATION 
    public function approveTaskMainE(){
        $tasks = Tasks::where([['comp_id', $this->compid()],['type_csr', 'N']])
                    ->leftJoin(DB::raw('(SELECT task_id, SUM(acc_data_tot_cost) as total_cost 
                                        FROM account_data_load_import 
                                        GROUP BY task_id) as acc_data'), 
                                function($join) {
                                    $join->on('tasks.id', '=', 'acc_data.task_id');
                                })
                    ->whereIn('task_state', ['3', '4', '5'])
                    ->orderby('task_maker_time', 'desc')
                    ->select('tasks.*', DB::raw('COALESCE(acc_data.total_cost, 0) as total_cost'))
                    ->get();

        $company = Companies::where('id', $this->compid())->first();
        $org = Organization::where('id', $company->org_id)->first();
        $orgexist = $org ? "EXIST" : "NOTEXIST";

        foreach ($tasks as $key => $task) {
            $task->task_state = $this->taskStateDescription($task->task_state);
            $task->input_methode = $this->taskMethodeDescription($task->input_methode);
        }
        // dd($tasks);
        return view('tasks/partner/approval/approve_task_listE',compact('tasks'));
    }

    public function approveTaskMainS(){
        $tasks = Tasks::where([['comp_id', $this->compid()],['type_csr', 'Y']])
                    ->leftJoin(DB::raw('(SELECT task_id, SUM(acc_data_tot_cost) as total_cost 
                                        FROM account_data_load_import 
                                        GROUP BY task_id) as acc_data'), 
                                function($join) {
                                    $join->on('tasks.id', '=', 'acc_data.task_id');
                                })
                    ->whereIn('task_state', ['3', '4', '5'])
                    ->orderby('task_maker_time', 'desc')
                    ->select('tasks.*', DB::raw('COALESCE(acc_data.total_cost, 0) as total_cost'))
                    ->get();

        $company = Companies::where('id', $this->compid())->first();
        $org = Organization::where('id', $company->org_id)->first();
        $orgexist = $org ? "EXIST" : "NOTEXIST";

        foreach ($tasks as $key => $task) {
            $task->task_state = $this->taskStateDescription($task->task_state);
            $task->input_methode = $this->taskMethodeDescription($task->input_methode);
        }

        return view('tasks/partner/approval/approve_task_listS',compact('tasks'));
    }
    public function approveTaskCRUDE(Request $request){
        $state=$request->state;
        
        if($state=="OPEN"){
            $taskid = $request->taskid;
            $task = Tasks::where('id', $taskid)->first();           
            $accstyleimports = AccountDataLoadImport::where([
                ['comp_id', $this->compid()],
                ['task_id', $taskid]
            ])->get();
            return view('tasks/partner/approval/approve_task_openE', compact('task'));
        } 
    }   
    public function approveTaskCRUDS(Request $request){
        $state=$request->state;
        
        if($state=="OPEN"){
            $taskid = $request->taskid;
            $task = Tasks::where('id', $taskid)->first();           
            $accstyleimports = AccountDataLoadImportCSR::where([
                ['comp_id', $this->compid()],
                ['task_id', $taskid]
            ])->get();
            return view('tasks/partner/approval/approve_task_openS', compact('task'));
        }
    }   
// public function approvalTask(){
//     $company=Companies::where('id',$this->compid())->first();
//     $tasks=Tasks::where([['comp_id',$this->compid()],['task_state','3']])->get();
//     foreach ($tasks as $key => $task) {
//         $task->task_state=$this->taskStateDescription($task->task_state);
//     }
//     return view('tasks/partner/approval/list_task',compact('tasks'));
// }

public function approvalMain(Request $request){
    $taskid=$request->taskid;
    $task=Tasks::where('id',$taskid)->first();
    $accdataloadimport=AccountDataLoadImport::where('task_id',$taskid)->get();
    return view('tasks/partner/approval/list_detail',compact('accdataloadimport','task'));
}

public function approvalSubmit(Request $request, string $id){
    $uploadtask=Tasks::where('id',$id)->first();
    $company=Companies::where('id',$this->compid())->first(); 
    $org=Organization::where('id',$company->org_id)->first();

    $connector=Connector::where([['comp_id',$this->compid()],['connect_type','ENVIZI'],['connect_protocol','S3']])->first();
    if(!$connector){
        return response()->json(['state' => 'error', 'message' => "Connector for ".$company->comp_name." Not Found please contact Agile", 'code' => 413]);
    }

    Tasks::where('id',$id)->update([
        "task_approval_name" => $this->name(),
        "task_approval_message" => "Data has been approved and submit...",
        "task_approval_time" => date("Y-m-d H:i:s"),
        "task_progress" => "100%",
        "task_last_message"=>"Data has been approved and submit...",
        "task_state" => "4",
    ]);

    //insert from import to data load
    $accdataloadimports=AccountDataLoadImport::where('task_id',$id)->get();
    $processdataload=new ProcessDataLogs;
    $processdataload->comp_id=$company->id;
    $processdataload->connect_id=$connector->id;
    $processdataload->process_data_type="UPLOADED";
    $processdataload->process_data_origin='CLIENT UPLOAD';
    $processdataload->process_data_tittle="Upload for ".$org->org_name;
    $processdataload->process_data_note='SUCCESS Upload Data using '.$connector->connect_name.' via '.$connector->connect_protocol.'driver, to '.$connector->connect_storage_code;
    $processdataload->process_data_process_state="SUCCESS";
    $processdataload->process_data_process_time=date("Y-m-d H:i:s");
    $processdataload->process_data_describe_state= "Success Upload from Client";
    $processdataload->save();

    $arrId=array();
    foreach ($accdataloadimports as $key => $accdataloadimport) {
        $loc_name=$accdataloadimport->location_name;
        $loc=Location::where('location_name',$loc_name)->first();
        $accstylecaption=$accdataloadimport->acc_style_caption;
        $accountstyle=AccountStyles::where('acc_style_caption',$accstylecaption)->first();

        $accdl=new AccountDataLoad;
        $accdl->org_id=$org->id;
        $accdl->loc_id=$loc->id;
        $accdl->comp_id=$company->id;
        $accdl->task_id=$accdataloadimport->task_id;
        $accdl->acc_style_id=$accountstyle->id;
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
    // Excel::store(new AccountDataLoadExport, $folder.$filename,'public');
    Excel::store(new AccountDataLoadExport, $folder.$filename,$storagecode);
    //perbaharui status menjadi telah di upload
    AccountDataLoad::whereIn('id',$arrId)->update(['acc_data_state'=>'2']);
    AccountDataLoadImport::where('task_id',$id)->update(['acc_data_state'=>'3']); //update status uploaded
    return response()->json(['status' => 'success', 'message' => 'Data successfully approved', 'code' => 200]);
}

    public function approvalReject(Request $request){
    $taskid=$request->taskid;
    $message=$request->reject_reason;
        Tasks::where('id',$taskid)->update([
            "task_approval_name" => $this->name(),
            "task_approval_message" => "Data has been Rejected...",
            "task_approval_time" => date("Y-m-d H:i:s"),
            "task_progress" => "0%",
            "task_last_message"=>$message,
            "task_state" => "6",
        ]);

        return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
    }
    // Support Methode 
    public function GetLocations(){
        // Ambil comp_id dari user yang sedang login
        // $compId = auth()->user()->organization->comp_id;
        
        // Ambil org_id dari tabel companies berdasarkan comp_id
        // $orgId = Companies::where('id', $compId)->value('org_id');
        $orgName = auth()->user()->company->organization->org_name;

        // Jika tidak ditemukan org_id, kembalikan response kosong
        if (!$orgName) {
            return response()->json([]);
        } 
        // Ambil lokasi berdasarkan org_id
        $locations = Location::select('id', 'location_name')
            ->where('org_name', $orgName)
            ->get(); 
        return response()->json($locations);
    } 
    

    
    public function GetAccountStyles(){
        $compId = auth()->user()->comp_id; 
        $accstyle = AccountStyleCompanies::select('id','acc_style_comp_caption')
            ->where('comp_id',$compId)
            ->get(); 
        return response()->json($accstyle);
    }

    public function GetAccountNumber(){
        $compId = auth()->user()->comp_id ;
        $accnum = AccountStyleMapping::select('id','acc_number')
            ->where('comp_id',$compId)
            ->get(); 
        return response()->json($accnum);    
    }

    public function GetAccountStylesClientE() {
        $compId = auth()->user()->comp_id;
    
        $accstyle = DB::table('account_style_companies')
            ->select(
                'account_style_companies.id',
                'account_style_companies.acc_style_comp_reference as acc_style_mtra_caption',
                'account_style_companies.acc_style_comp_number as acc_number',
                'account_style_companies.comp_id as acc_style_comp_id',
                'account_style_companies.acc_style_comp_caption as acc_style_caption'
            ) 
            ->where('account_style_companies.comp_id', $compId)
            ->whereNotIn('account_style_companies.acc_style_scope',['Social Metrics'])
            ->get();   
        return response()->json($accstyle);  
    }
    
    public function GetAccountStylesClientS() {
        $compId = auth()->user()->comp_id;
    
        $accstyle = DB::table('account_style_companies')
            ->select(
                'account_style_companies.id',
                'account_style_companies.acc_style_comp_reference as acc_style_mtra_caption',
                'account_style_companies.acc_style_comp_number as acc_number',
                'account_style_companies.comp_id as acc_style_comp_id',
                'account_style_companies.acc_style_comp_caption as acc_style_caption'
            ) 
            ->where('account_style_companies.comp_id', $compId)
            ->where('account_style_companies.acc_style_scope',['Social Metrics'])
            ->get();   
        return response()->json($accstyle);  
    }

    // public function GetAccountStylesClientS() {
    //     $compId = auth()->user()->comp_id;
    
    //     $accstyle = DB::table('account_style_mapping')
    //         ->select(
    //             'account_style_mapping.id',
    //             'account_style_mapping.acc_style_mtra_caption',
    //             'account_style_mapping.acc_number',
    //             'account_style_mapping.acc_style_comp_id',
    //             'account_styles.acc_style_caption as acc_style_caption'
    //         )
    //         ->join('account_styles', 'account_styles.id', '=', 'account_style_mapping.acc_style_comp_id')
    //         ->where('account_style_mapping.comp_id', $compId)
    //         ->where('account_style_mapping.acc_style_type','S')
    //         ->get();   
    //     return response()->json($accstyle);  
    // }    
    
   // public function GetAccountStylesClientE() {
    //     $compId = auth()->user()->comp_id;
    
    //     $accstyle = DB::table('account_style_mapping')
    //         ->select(
    //             'account_style_mapping.id',
    //             'account_style_mapping.acc_style_mtra_caption',
    //             'account_style_mapping.acc_number',
    //             'account_style_mapping.acc_style_comp_id',
    //             'account_styles.acc_style_caption as acc_style_caption'
    //         )
    //         ->join('account_styles', 'account_styles.id', '=', 'account_style_mapping.acc_style_comp_id')
    //         ->where('account_style_mapping.comp_id', $compId)
    //         ->where('account_style_mapping.acc_style_type','E')
    //         ->get();   
    //     return response()->json($accstyle);  
    // }

    public function GetAccountStylesClientOLD(){
        $compId = auth()->user()->comp_id; 
        $accstyle = AccountStyleMapping::select('id','acc_style_mtra_caption')
            ->where('comp_id',$compId)
            ->get(); 
        return response()->json($accstyle);
    }


    public function getAccountStyleByID(Request $request)
    {
         
        $accstyleid = trim($request->input('id')); 
        
        $mapping = DB::table('account_style_mapping')
            ->where('id', $accstyleid)
            ->where('comp_id', $this->compid())
            ->first(); 

        $style = DB::table('account_styles')
            ->where('id', $mapping->acc_style_comp_id)
            ->first();

        return response()->json([
            'acc_style_caption' => $style->acc_style_caption ?? '',
            'acc_number' => $mapping->acc_number ?? ''
        ]);
    }

    public function taskStateDescription($taskstate){
        if($taskstate=='1'){
            $taskstate='Ready to Submit'; //'Prepared'; 
        }
        if($taskstate=='2'){
            $taskstate='Ready to Verify'; //'Created'; 
        }
        if($taskstate=='3'){
            $taskstate='Ready to Approve';//'Checked'; 
        }
        if($taskstate=='4'){
            $taskstate='Approved'; 
        }
        if($taskstate=='5'){
            $taskstate='Rejected'; //'Rejected on Check'; 
        }
        // if($taskstate=='6'){
        //     $taskstate='Rejected on Approve'; 
        // }
        return $taskstate;
    }
    public function getTaskStateValue($taskStateText)
    {
        $states = [
            'Ready to Submit' => 1,
            'Ready to Verify' => 2,
            'Ready to Approve' => 3,
            'Approved' => 4,
            'Rejected' => 5
        ];

        return $states[$taskStateText] ?? null; // Return null kalau tidak ditemukan
    }

    public function taskMethodeDescription($methode){
        if($methode=='U'){
            $methode='Data Upload'; 
        }
        if($methode=='C'){
            $methode='Data Capture'; 
        }
        if($methode=='A'){
            $methode='API Syncronize'; 
        }
        return $methode;
    }

    public function sync(Request $request)
    {
    
        $company = Companies::find($this->compid());
            $org = Organization::find($company->org_id);
            $connector = Connector::where([
                ['comp_id', $this->compid()],
                ['connect_type', 'CLIENT-ACCSTYLE'],
                ['connect_protocol', 'API']
            ])->first(); 

        $connectBody = json_decode($connector->connect_body, true);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $connector->connect_token_value
        ])->post($connector->connect_url, [
            'uid' => $connectBody['uid']
        ]);  
         
        // dd($response->json()); // Debugging untuk melihat isi response
        $masterLocations = DB::table('locations')
        ->select('id', 'location_name')
        ->where('comp_id',$this->compid())
        ->get()
        // ->pluck('id', 'location_name') // ['location_name' => id]
        ->mapWithKeys(function ($loc) {
            return [strtolower(trim($loc->location_name)) => $loc->id];
        })
        ->toArray(); 
        // dd($masterLocations); // Debugging untuk melihat isi $masterLocations

        $notFoundLocations = []; // << Tambahin ini untuk catat lokasi yang gak ketemu

        $styleMappings = DB::table('account_style_mapping')
        ->join('account_styles', 'account_style_mapping.acc_style_comp_id', '=', 'account_styles.id')
        ->where('account_style_mapping.comp_id', $this->compid())
        ->select('account_style_mapping.acc_style_mtra_caption', 'account_styles.acc_style_caption')
        ->get()
        ->mapWithKeys(function ($row) {
            return [preg_replace('/\s+/', ' ', strtolower(trim($row->acc_style_mtra_caption))) => $row->acc_style_caption];
        })
        ->toArray();
        // dd($styleMappings); // Debugging untuk melihat isi $styleMappings

        $rawData = $response->json();  
        $formatted = collect($rawData['result']['content'] ?? [])->map(function ($item) use ($masterLocations, $styleMappings) {

            $locationKey = strtolower(trim($item['location']));
            $mtraCaption = strtolower(trim($item['accountStyleCaption']));

            return [
                'id'                     => $masterLocations[$locationKey] ?? null, 
                'location_name'          => $item['location'],
                'acc_number'             => $item['accountNumber'],
                'acc_style_caption'      => $styleMappings[$mtraCaption] ?? null,
                'acc_style_mtra_caption' => $item['accountStyleCaption'],
                'acc_supplier'           => $item['accountSupplier'],
                'record_date_start'      => substr($item['recordStartYyyyMmDd'], 0, 10),
                'record_date_end'        => substr($item['recordEndYyyyMmDd'], 0, 10),
                'acc_data_qty'           => $item['quantity'],
                'acc_data_tot_cost'      => $item['totalCostInclTaxInLocalCurrency'],
            ];
        });
         
        return response()->json([
            'data' => $formatted,
            'not_found_locations' => $notFoundLocations
        ]);

        // return response()->json($formatted);
         
    }

     
    

     #---- Task Open 
    // public function taskOpenIndex(Request $request){
    //     $taskid=$request->taskid; 
    //     $task=Tasks::where('id',$taskid)->first();
    //     $accstyleimports=AccountDataLoadImport::where([['comp_id',$this->compid()],['task_id',$taskid]])->get();
    //     return view('tasks/partner/maker/task_detail',compact('accstyleimports','task'));
    // }
}
