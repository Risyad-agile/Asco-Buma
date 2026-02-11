<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Companys;
use App\Models\Stores;
use App\Models\Suppliers;
use App\Models\Returns;
use App\Models\Products;
use App\Services\StockCardServices; 
use Carbon\Carbon;
use Session;
use DB;
use Entrust;

class ReturnsController extends Controller
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
        return Auth::user()->store->company->id;
    }
    public function userid(){
        return Auth::user()->id;
    }
    public function username(){
        return Auth::user()->username;
    }
    public function storeid(){
        return Auth::user()->stores->id;
    }

    public function index()
    {
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first();
            $suppliers=Suppliers::where('comp_id','=',$this->compid())->get();  
            return view('purchases/retur',compact('suppliers','store'));
        }
        return view('purchases/retur_index');
    }
    public function main(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $suppliers=Suppliers::where('comp_id',$this->compid())->get();  //ambil supplier dalam satu perusahaan
        return view('purchases/retur',compact('suppliers','store'));
    }
    public function listindex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $returs=Returns::with('supplier','store','products')->where([['store_id',$storeid],['retur_state','1']])
                        ->orderby('retur_no','desc')->get();
            return view('purchases/retur_list',compact('returs','store'));
        }
        return view('purchases/retur_list_index'); 
    }
    public function list(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $returs=Returns::with('supplier','store','products')->where([['store_id',$storeid],['retur_state','1']])
                  ->orderby('retur_no','desc')->get();
        return view('purchases/retur_list',compact('returs','store'));
    }

    public function listbystore($storeid){ 
        $store=Stores::where('id',$storeid)->first();
        $returs=Returns::with('supplier','store','products')->where([['store_id',$storeid],['retur_state','1']])
                  ->orderby('retur_no','desc')->get();
        return view('purchases/retur_list',compact('returs','store'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $prodControl=new ProductsController;
        $arrProductStock=array();

        //lokasi tujuan penempatan barang
        $store_id=$request->input('storeid');

        $autoNo=$this->autoReturNo($store_id);
        $retur=new Returns;
        $retur->retur_no=$autoNo;
        $retur->store_id=$store_id;
        $retur->retur_date=$request->input('form')['retur_date'];
        $retur->supplier_id=$request->input('form')['supplier_id'];
        $retur->retur_reff_no=$request->input('form')['retur_reff_no'];
        $retur->retur_note=$request->input('form')['retur_note'];
        $retur->retur_type='1';
        $retur->retur_state='1';
        $retur->save();

        for ($i=0; $i < count($request->input('products')) ; $i++) {
          $retur->products()->attach([
          'product_id'=>$request->input('products')[$i]['product_id']],
          [
              'retur_id'=>$retur->id,
              'retur_product_price'=>$request->input('products')[$i]['product_price'],
              'retur_product_qty'=>$request->input('products')[$i]['product_qty']
          ]);

          $arrProductStock[]=['product_id'=>$request->input('products')[$i]['product_id'],
            'product_qty'=>$request->input('products')[$i]['product_qty']];
        }

        //kurangi stok
        $prodControl::updateProductStock($store_id,$arrProductStock,"OUT"); //update stock tabel produk stores

        return response()->json(['status' => 'success', 'message' => 'Data successfully Updated', 'code' => 200]);    
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
        //hanya bisa di batalkan untuk transaksi hari yang sama

        //ambil data dari transaksi yang di maksud
        $retur=Returns::with('products')->where('id',$id)->first();
        $prodControl=new ProductsController;
        $arrProductStock=array();

        foreach ($retur->products as $product) {
            $arrProductStock[]= array('product_id' => $product->id,
                'product_qty' => $product->return_products->retur_product_qty); 
        }

        $store_id=$retur->store_id; //lokasi tujuan pembelian 
        $prodControl::updateProductStock($store_id,$arrProductStock,"IN"); //Kembalikan Stok
    
        //ubah status cancel pada sales
        Returns::where('id',$id)->update(['retur_state'=>'0']);
        return response()->json(['status' => 'success', 'message' => 'Data successfully canceled', 'code' => 200]); 
    }

    #--------Pembatalan-------
    public function returCancelIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            return view('purchases/retur_cancel',compact('store'));
        }
        return view('purchases/retur_cancel_index');
    }
    public function returCancel(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first(); 
        return view('purchases/retur_cancel',compact('store'));
    }
    public function returCancelByStoreNumber($storeid,$returno){
        $tgl=date("Y-m-d")." 00:00:00";//date("dmY"); //ddmmyyyy
        $retur=Returns::with('supplier','products')
            ->where('retur_no',$returno)
            ->first();
        
        //hanya dibatalkan pada hari yang sama
        if($retur==null){
            return "NOTFOUND";
        }
        //hanya dibatalkan pada hari yang sama
        if($retur->retur_date!=$tgl){
            return "BEDAHARI";
        }
        //periksa apakah sudah dibatalkan
        if($retur->retur_state=='0'){
            return "BATAL";
        }
        $prodControl=new ProductsController;
        foreach ($retur->products as $product) {
            $product_id=$product->id;
            $prod=$prodControl->productStoreById($storeid,$product_id);
            if(!$prod){
                return "TERHAPUS"; //sebagian produk terhapus
            }
        }
        return $retur;
    }  
    
    #-------RETUR PEMBELIAN DI TOKO-----------
    public function storeReturMain(){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first();
        $suppliers=Suppliers::where('comp_id',$this->compid())->get();  //ambil supplier dalam satu perusahaan
        return view('purchases/stores/retur',compact('suppliers','store'));
    }
    public function storeReturList(){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first(); 
        $returs=Returns::with('supplier','store','products')->where([['store_id',$storeid],['retur_state','1']])
                  ->orderby('retur_no','desc')->get();
        return view('purchases/stores/retur_list',compact('returs','store'));
    }
    public function storeReturCancel(){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first();
        return view('purchases/stores/retur_cancel',compact('store'));
    }
    #-------RETUR KONSINYASI-----------
    public function returConsignmentIndex()
    {
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first();
            $suppliers=Suppliers::where([['comp_id',$this->compid()],['supplier_consignment_state','1']])->get();  
            return view('consignment/retur',compact('suppliers','store'));
        }
        return view('consignment/retur_index');
    }
    public function returConsignmentMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $suppliers=Suppliers::where([['comp_id',$this->compid()],['supplier_consignment_state','1']])->get(); //ambil supplier dalam satu perusahaan
        return view('consignment/retur',compact('suppliers','store'));
    }
    public function returConsignmentListIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $returs=Returns::with('supplier','store','products')->where([['store_id',$storeid],['retur_state','1']])
                        ->orderby('retur_no','desc')->get();
            return view('consignment/retur_list',compact('returs','store'));
        }
        return view('consignment/retur_list_index'); 
    }
    public function returConsignmentList(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $returs=Returns::with('supplier','store','products')->where([['store_id',$storeid],['retur_state','1']])
                  ->orderby('retur_no','desc')->get();
        return view('consignment/retur_list',compact('returs','store'));
    }

    public function returConsignmentListByStore($storeid){ 
        $store=Stores::where('id',$storeid)->first();
        $returs=Returns::with('supplier','store','products')->where([['store_id',$storeid],['retur_state','1']])
                  ->orderby('retur_no','desc')->get();
        return view('consignment/retur_list',compact('returs','store'));
    }
    public function returConsignmentCancelIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            return view('consignment/retur_cancel',compact('store'));
        }
        return view('consignment/retur_cancel_index');
    }
    public function returConsignmentCancel(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first(); 
        return view('consignment/retur_cancel',compact('store'));
    } 





    public function autoReturNo($storeid)
    {
      $bulan=date("m");
      $tahun=date("y");
      $strNewId = "RTRP-".$tahun.$bulan."0001";

      while($this->findNO($strNewId,$storeid)){
        $intNewId=substr($strNewId,-4)+1;
        switch (strlen($intNewId)) {
            case 1:
                $strNewId="RTRP-".$tahun.$bulan.'000'.$intNewId;
                break;
            case 2:
                $strNewId="RTRP-".$tahun.$bulan.'00'.$intNewId;
                break;
            case 3:
                $strNewId="RTRP-".$tahun.$bulan.'0'.$intNewId;
                break;
            case 4:
                $strNewId="RTRP-".$tahun.$bulan.$intNewId;
                break;
        }
      }
      return $strNewId;
    }
    private function findNO($rtrno,$storeid){
        $receive=Returns::where([['retur_no',$rtrno],['store_id',$storeid]])->first();
        if($receive){
          return true;
        }
        return false;
    }
}
