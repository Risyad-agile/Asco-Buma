<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Warehouses;

class APIWarehouses extends Controller
{
    public function getWarehouseByCompany($compid){
        $warehouses=Warehouses::with('companys')->where('comp_id',$compid)->orderBy('warehouse_index','asc')->get();
        return response()->json($warehouses);
    }
    public function getWarehouseById($warehouseid){
        $warehouse=Warehouses::with('companys')->where('warehouse_id','=' ,$warehouseid)->first();
        return response()->json($warehouse);
    }
    public function updateWarehouseIP(Request $request, $warehouse_id){
        $warehouseip=$request->warehouse_ip;
        $compid=$request->comp_id;
        Warehouses::where('warehouse_id','=' ,$warehouse_id)->update(array('warehouse_ip'=>$warehouseip));
        $warehouses=Warehouses::with('companys')->where('comp_id',$compid)->orderBy('warehouse_index','asc')->get();
        return response()->json($warehouses);
    }
}
