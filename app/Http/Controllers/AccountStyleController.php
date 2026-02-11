<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;  
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Companies; 
use App\Models\AccountStyles;
use App\Models\AccountStylesImport;
use App\Models\AccountStyleCompanies;
use App\Models\AccountStyleMapping;
use App\Imports\AccountStyleImportProcess;

class AccountStyleController extends Controller
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
      
      return view("accountstyle/list");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $accstyles=AccountStyles::where('acc_style_state','1')->get();
        foreach ($accstyles as $key => $accstyle) {
          if($accstyle->acc_style_link==null){
            $accstyle->acc_style_link="Try to Sync";
          }
        }
        return $accstyles;
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


    //import account style  
    public function importFileUpload(){
        return view("accountstyle/import/upload");
    }
  
    public function importFileProcess(Request $request){  
        $filexls = $request->file('files')[0];
       
        $new_name= "ACC_STYLE.".$filexls->getClientOriginalExtension();
        $tujuan_upload = public_path("images"); //daripada buat folder baru simpen di images ajah
        $filexls->move($tujuan_upload,$new_name); 
  
        $fileloc=$tujuan_upload."/".$new_name;

        // dd($fileloc);
  
        AccountStylesImport::truncate(); //pastikan data yang ada di hapus
        Excel::import(new AccountStyleImportProcess, $fileloc);
  
        // $accstyles=AccountStylesImport::all();
        return response()->json(['status' => 'success', 'message' => 'Data successfully Uploaded', 'code' => 200]);
    }
  
    public function importOpenList(Request $request){
        $accstylesimport=AccountStylesImport::all();
        return view("accountstyle/import/list",compact('accstylesimport'));
    }

    public function importSave(Request $request){
        $iaccstyles=json_decode(json_encode($request->accstylesimport,false));
        foreach ($iaccstyles as $key => $iaccstyle) {
          $this->saveAccountStyleImport($iaccstyle);
        }
        AccountStylesImport::truncate(); //pastikan data di hapus semua
        // return redirect()->action([HomeController::class,'index']);
        return response()->json(['status' => 'success', 'message' => 'Data successfully Imported', 'code' => 200]);

    }
  
    private function saveAccountStyleImport($iaccstyle){
        $loweracccaption = strtolower($iaccstyle->acc_style_caption);
  
        $accstyle=AccountStyles::whereRaw('lower(acc_style_caption) like (?)',["{$loweracccaption}"])->count();
        if($accstyle>0){
            return false;
        }
 
        $accstyle=new AccountStyles; 
        $accstyle->acc_style_caption=$iaccstyle->acc_style_caption; 
        $accstyle->acc_style_name=$iaccstyle->acc_style_name;
        $accstyle->acc_style_product=$iaccstyle->acc_style_product;
        $accstyle->acc_style_scope=$iaccstyle->acc_style_scope;
        $accstyle->acc_style_category=$iaccstyle->acc_style_category;
        $accstyle->acc_style_qty_uom=$iaccstyle->acc_style_qty_uom; 
        $accstyle->acc_style_cost_supported=$iaccstyle->acc_style_cost_supported;
        $exec = $accstyle->save();
        return true;
    }
   

    ///company account style open by ASRI
    public function companyAccountStyleIndex(){
        return view('accountstyle/company/index');

    }
    public function companyAccountStyleListOpen(Request $request){
        $comp=Companies::where('id',$request->comp_id)->first();
        $accstyles=AccountStyleCompanies::with('company')->where([['acc_style_comp_state','1'],['comp_id',$comp->id]])->get();
        return view('accountstyle/company/list',compact('comp','accstyles'));
    }
    public function companyAccountStyleLoad(){
        $accstyles=AccountStyleCompanies::with('company')->where('acc_style_comp_state','1')->get();
        return $accstyles;
    }
    
    //syncronize with company account style
    public function syncAccountStyle(){
      $accstyles=AccountStyles::where('acc_style_state','1')->get();  
      $accstylescomp=AccountStyleCompanies::where('acc_style_comp_state','1')->get();  

      foreach ($accstyles as $key => $accstyle) {
        $accstyleid=$accstyle->id;
        foreach ($accstylescomp as $key => $accstylecomp) {
          if($accstylecomp->acc_style_comp_caption==$accstyle->acc_style_caption){
            $link=$accstylecomp->acc_style_comp_link;
            AccountStyles::where('id',$accstyleid)->update(['acc_style_link'=>$link]);
          }
        }
      }
      return response()->json(['status' => 'success', 'message' => 'Data successfully Syncronize', 'code' => 200]);
    }

    //shared account style to partner
    public function sharedAccountStyleIndex(){
      return view("accountstyle/company/accstyle_shared");
    }
    public function sharedAccountStyleLoad(){
      $accstyles=AccountStyles::where('acc_style_state','1')->get();
      return $accstyles;
    }
    //company account style for partner/company
    public function partnerCompanyAccountStyleListOpen(){   
      $compid=$this->compid();
      $comp=Companies::where('id',$compid)->first();
      $accstyles=AccountStyleCompanies::with('company')->where([['acc_style_comp_state','1'],['comp_id',$compid]])->get();
      return view('accountstyle/company/list',compact('comp','accstyles'));
    }
     
     
}
