<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Warehouses;
use App\Models\Suppliers;
use Session;
use Validator;

class WarehousesController extends Controller
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
        return Auth::user()->stores->companys->comp_id;
    }

    public function activeuserstoreid(){
      return Auth::user()->stores->store_id;
    }
    public function index()
    {
        return view('masters/warehouses');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $warehouses=Warehouses::where([['comp_id',$this->compid()],['warehouse_state','1']])->get();
        return $warehouses;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['status' => 'error', 'message' => 'Request is not permitted', 'code' => 404]);
        }
        $validator = Validator::make($request->all(), [
            'warehouse_name' => 'required|string',  
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }

        try {
            $compid=$this->compid();
            $warehouse=new Warehouses;
            $warehouse_id=$this->autoWarehouseId($compid);
            $warehouse->warehouse_id=$warehouse_id;
            $warehouse->comp_id=$compid;
            $warehouse->warehouse_name=$request->get('warehouse_name');
            $warehouse->warehouse_desc=$request->get('warehouse_desc');
            $warehouse->warehouse_index=$request->get('warehouse_index');
            $warehouse->warehouse_ip=$request->get('warehouse_ip');
            $warehouse->warehouse_state="1"; 

            $exec=$warehouse->save();
            if (!$exec) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }

            return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);

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
        $res = Warehouses::where('warehouse_id', $id)->update($request->except(['warehouse_id','comp_id']));

        if (!$res) {
            return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
        }

        return response()->json(['status' => 'success', 'message' => 'Data successfully edited', 'code' => 200]);
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
    public function autoWarehouseId()
    {
      // aturan kode supplier : comp_idW123
      $comp_id=$this->compid();
      $strNewId = $comp_id."W001";
      // kalau belum ada record sebelumnya berarti ini yang pertama
      if(Warehouses::where('warehouse_id','=',$strNewId)->count()==0){
        return $strNewId;
      }

      $strLastId= Warehouses::where('comp_id','=',$comp_id)->get()->last()->warehouse_id;
      $intNewId= substr($strLastId,-3)+1;

      $strNewId=strlen($intNewId);
      switch (strlen($intNewId)) {
          case 1:
              $strNewId=$comp_id.'W00'.$intNewId;
              break;
          case 2:
              $strNewId=$comp_id.'W0'.$intNewId;
              break;
          case 3:
              $strNewId=$comp_id.'W'.$intNewId;
              break;
      }
      return $strNewId;
    }    
}
