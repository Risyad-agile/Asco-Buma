<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ProcessDataLogs;
use App\Models\AccountDataLoad;
use App\Models\AccountDataLoadCSR;

class ProcessDataLogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
        return view('process-data/index');
    }
    public function listOpen(Request $request){
        $compid=$request->comp_id;
        $processdata=ProcessDataLogs::where([['comp_id',$compid],['process_data_state','!=','3']])->get();
        return view('process-data/list',compact('processdata'));
    }

    public function main(Request $request){
        $processid=$request->processid;
        $accountdataloads=AccountDataLoad::where('process_id',$processid)->get(); //engga bisa di cek pake !

        if(count($accountdataloads)==0){
            // csr
            $accountdataloads=AccountDataLoadCSR::where('process_id',$processid)->get();
            return view('process-data/list_account_data_load_csr',compact('accountdataloads'));
        }
        return view('process-data/list_account_data_load',compact('accountdataloads'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function errorIndex(){
        return view('process/error_index');
    }
    public function errorlistOpen(Request $request){
        $compid=$request->comp_id;
        $processdata=ProcessDataLogs::where([['comp_id',$compid],['process_state','3']])->get();
        return view('process/error_list',compact('processdata'));
    }


    #----PARTNER PROCESS DATA LOG
    public function  partnerProcessDataLogIndex(){
        $compid=$this->compid();
        $processdata=ProcessDataLogs::where([['comp_id',$compid],['process_data_state','!=','3']])->get();
        return view('process-data/partner/list',compact('processdata'));
    }
    public function partnerProcessDataLogMain(Request $request){
        $processid=$request->processid;
        $accountdataloads=AccountDataLoad::where('process_id',$processid)->get(); //engga bisa di cek pake !

        if(count($accountdataloads)==0){
            // csr
            $accountdataloads=AccountDataLoadCSR::where('process_id',$processid)->get();
            return view('process-data/list_account_data_load_csr',compact('accountdataloads'));
        }
        return view('process-data/list_account_data_load',compact('accountdataloads'));
    }
}
