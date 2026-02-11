<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Companies;
use App\Models\Brands;
use Entrust;
use Session;
use Validator;

class BrandsController extends Controller
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
        return Auth::user()->stores->companies->comp_id;
    }

    public function index()
    { 
        return view('masters/brands');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $brands=Brands::where('brand_state','1')->get();
        return $brands;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'brand_name' => 'required|string',
        ],[ 
            'brand_name.required' => 'Silakan Masukan Merek', 
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }
       

        try {
            $brands=new Brands;
            $brands->brand_name=$request->get('brand_name'); 
            $brands->brand_state='1';
            $brands->save();
            if (!$brands) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Data successfully Saved', 'code' => 200]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);   
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

       $res = Brands::where('id', $id)->update($request->except(['id']));

        if (!$res) {
            return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
        }

        return response()->json(['status' => 'success', 'message' => 'Data successfully Updated', 'code' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Brands::where('id',$id)->update(['brand_state'=>'0']); 
        return response()->json(['status' => 'success', 'message' => 'Data successfully Deleted', 'code' => 200]);
    }

    #-----PRODUCT CATEGORY---- //akses manajer masing masing apotik
    public function companyBrandIndex(){
        return view('masters/company/brands');
    }

    public function brandImportPredictedName($brandname){
        $lowerbrandname = strtolower($brandname);
        $brand=Brands::whereRaw('lower(brand_name) like (?)',["%{$lowerbrandname}%"])->first();
        if($brand!=null){
            return $brand->brand_name;
        }
        return "NEW";
    }

    public function brandImportAdd($brandname){
        //update, pilihan diserahkan kepada user, jika di lewat berarti buat baru
        //jika sudah ada dengan nama yang sama tidak perlu di buat
        $lowerbrandname = strtolower($brandname);
        $brand=Brands::whereRaw('lower(brand_name) like (?)',["{$lowerbrandname}"])->first();
        if($brand!=null){
            return $brand->id;
        }

        $brand=new Brands; 
        $brand->brand_name=$brandname; 
        $brand->brand_state='1';
        $brand->save();
        return $brand->id;
    }

    public function autoBrandId()
    {
      // aturan kode toko : comp_idB123
      $comp_id=$this->compid();
      $strNewId = $comp_id."B001";
      // kalau belum ada record sebelumnya berarti ini yang pertama
      if(Brands::where('brand_id',$strNewId)->count()==0){
        return $strNewId;
      }

      $strLastId= Brands::where([['comp_id',$this->compid()],['brand_state','1']])->get()->last()->brand_id;
      $intNewId= substr($strLastId,-3)+1;

      $strNewId=strlen($intNewId);
      switch (strlen($intNewId)) {
          case 1:
              $strNewId=$comp_id.'B00'.$intNewId;
              break;
          case 2:
              $strNewId=$comp_id.'B0'.$intNewId;
              break;
          case 3:
              $strNewId=$comp_id.'B'.$intNewId;
              break;
      }
      return $strNewId;
    }
}
