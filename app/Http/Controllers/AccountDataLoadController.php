<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Imports\AccountDataLoadImportProcess;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Models\Companies; 
use App\Models\Tasks; 

class AccountDataLoadController extends Controller
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
        return view('tasks/account/index');
    }

    public function listTaskOpen(Request $request){
        $taskcontrol=new TasksController;
        $compid=$request->comp_id;
        $company=Companies::where('id',$compid)->first();
        $tasks=Tasks::where([['comp_id',$compid],['task_state','1']])->get();
        foreach ($tasks as $key => $task) {
            $task->task_state=$taskcontrol->taskStateDescription($task->task_state);
        }
        return view('tasks/account/list_task',compact('company','tasks'));
    }
    public function main(Request $request){
        $state=$request->state;
        $taskid=$request->taskid;
        $task=Tasks::where('id',$taskid)->first();
        
        if($state=="ACCOUNTLOAD"){
            $accdataloadimport=AccountDataLoadImport::where('task_id',$taskid)->get();
            return view('tasks/account/list_account',compact('accdataloadimport','task'));
        }
        if($state="ACCOUNTLOADCSR"){
            $accdataloadimport=AccountDataLoadImport::where('task_id',$taskid)->get();
            return view('tasks/account/list_account_csr',compact('accdataloadimport','task'));
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



    //-------------------import account data load from envizi csv file
    public function importIndex(){
        return view("import/account/data_load_index");
      }
    public function importUpload(Request $request){
        $storeorigin=$request->storeorigin;
        $storedestination=$request->storedestination;
        $compid=Stores::where('id',$storeorigin)->first()->comp_id; //sama saja pake origin atau destination
  
        $filexls = $request->file('files')[0];
        $new_name= "PROD-".$storeorigin.".".$filexls->getClientOriginalExtension();
        $tujuan_upload = public_path("images"); //daripada buat folder baru simpen di images ajah
        $filexls->move($tujuan_upload,$new_name); 
  
        $fileloc=$tujuan_upload."/".$new_name;
  
        ProductImport::truncate(); //pastikan data yang ada di hapus
        Excel::import(new ProductImportProcess, $fileloc);
  
        ProductImport::query()->update(['comp_id'=>$compid,
            'store_origin'=>$storeorigin,
            'store_destination'=>$storedestination]);
        $prodimport=ProductImport::all();
        return $prodimport;
      }

}
