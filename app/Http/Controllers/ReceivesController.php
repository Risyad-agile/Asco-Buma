<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Companies;
use App\Models\Stores;
use App\Models\UserStores;
use App\Models\Suppliers;
use App\Models\Receives;
use App\Models\Products;
use App\Models\ProductSuppliers;
use App\Services\StockCardServices; 
use Carbon\Carbon;
use Session;
use DB;
use Entrust;

class ReceivesController extends Controller
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
        return Auth::user()->store->id;
    }
    public function index()
    {
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first();
            $suppliers=Suppliers::where('comp_id',$this->compid())->get();  
            return view('purchases/receive',compact('suppliers','store'));
        }
        return view('purchases/receive_index');
    }
    public function main(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $suppliers=Suppliers::where('comp_id',$this->compid())->get();  //ambil supplier dalam satu perusahaan
        return view('purchases/receive',compact('suppliers','store'));
    }
    public function listindex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $receives=Receives::with('supplier','store','products')->where([['store_id',$storeid],['receive_state','1'],
                    ['receive_type','1']])->orderby('receive_date','desc')->get();
            return view('purchases/receive_list',compact('receives','store'));
        }
        return view('purchases/receive_list_index');
    }

    public function list(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $receives=Receives::with('supplier','store','products')->where([['store_id',$storeid],['receive_state','1'],
                  ['receive_type','1']])->orderby('receive_date','desc')->get();
        return view('purchases/receive_list',compact('receives','store'));
    }

    public function listbystore($storeid){ 
        $store=Stores::where('id',$storeid)->first();
        $receives=Receives::with('supplier','store','products')->where([['store_id',$storeid],['receive_state','1'],
                  ['receive_type','1']])->orderby('receive_date','desc')->get();
        return view('purchases/receive_list',compact('receives','store'));
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
        $supplierid=$request->input('form')['supplier_id'];
        $receive_type=$request->input('receive_type');

        $autoNo=$this->autoReceiveNo($store_id,$receive_type); //pembelian
        $receive=new Receives;
        $receive->receive_no=$autoNo;
        $receive->store_id=$store_id;
        $receive->receive_date=$request->input('form')['receive_date'];
        $receive->receive_docno=$request->input('form')['receive_docno'];
        $receive->receive_docdate=$request->input('form')['receive_docdate'];
        $receive->supplier_id=$supplierid;
        $receive->receive_note=$request->input('form')['receive_note'];
        $receive->receive_payment=$request->input('receive_payment');
        $receive->receive_cashback=$request->input('receive_cashback');
        $receive->receive_total=$request->input('receive_total');
        $receive->receive_type=$receive_type; 
        $receive->receive_state='1';
        $receive->save();

        for ($i=0; $i < count($request->input('products')) ; $i++) {
          $receive->products()->attach([
          'product_id'=>$request->input('products')[$i]['product_id']],
          [
              'receive_id'=>$receive->id,
              'receive_product_price'=>$request->input('products')[$i]['product_price'],
              'receive_product_qty'=>$request->input('products')[$i]['product_qty']
          ]);
          $arrProductStock[]=['product_id'=>$request->input('products')[$i]['product_id'],
            'product_qty'=>$request->input('products')[$i]['product_qty']];
        }

        //tambahkan stok
        $prodControl::updateProductStock($store_id,$arrProductStock,"IN"); //update stock tabel produk stores

        //jika konsinyasi simpan sebagai supplier produk konsinyask
        $this->saveProductSupplier($supplierid,$request->input('products'));

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
        $receive=Receives::with('products')->where('id',$id)->first();
        $prodControl=new ProductsController;
        $arrProductStock=array();

        foreach ($receive->products as $product) {
            $arrProductStock[]=['product_id'=>$product->id,
                                'product_qty'=>$product->receive_products->receive_product_qty];
        }

        $storeid=$receive->store_id; //lokasi tujuan pembelian  
        $prodControl::updateProductStock($storeid,$arrProductStock,"OUT"); //update stock tabel produk stores

        //ubah status cancel pada penerimaan menjadi batal
        Receives::where('id',$id)->update(['receive_state'=>'0']);
        return response()->json(['status' => 'success', 'message' => 'Data successfully canceled', 'code' => 200]); 
        
    }
    
    #--------Pembatalan-------
    public function receiveCancelIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            return view('purchases/receive_cancel',compact('store'));
        }
        return view('purchases/receive_cancel_index');
    }

    public function receiveCancel(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        return view('purchases/receive_cancel',compact('store'));
    }

    public function receiveCancelByStoreNumber($storeid,$receiveno){
        $tgl=date("Y-m-d")." 00:00:00";//date("dmY"); //ddmmyyyy
        $receive=Receives::with('supplier','products')
            ->where([['store_id',$storeid],['receive_no',$receiveno]])
            ->first();
        
        //tidak ditemukan
        if($receive==null){
            return "NOTFOUND";
        }

        //hanya dibatalkan pada hari yang sama
        if($receive->receive_date!=$tgl){
            return "BEDAHARI";
        }
        //periksa apakah sudah dibatalkan
        if($receive->receive_state=='0'){
            return "BATAL";
        }
        
        // periksa apakah stok mencukup jika dibatalkan
        $prodControl=new ProductsController;
        
        foreach ($receive->products as $product) {
            $product_id=$product->id;
            $receivestock=$product->receive_products->receive_product_qty;
            $prod=$prodControl->productStoreById($storeid,$product_id);
            if(!$prod){
                return "TERHAPUS"; //sebagian produk terhapus
            }
            if($receivestock>$prod->product_stock){
                return "OVERSTOCK";
            }
        }
        return $receive;
    }
 
    #-------PENERIMAAN PEMBELIAN DI TOKO-----------
    public function storeReceiveMain(){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first();
        $suppliers=Suppliers::where('comp_id','=',$this->compid())->get();  //ambil supplier dalam satu perusahaan
        return view('purchases/stores/receive',compact('suppliers','store'));
    }
    public function storeReceiveList(){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first();
        $receives=Receives::with('supplier','store','products')->where([['store_id',$storeid],['receive_state','1']])
                  ->orderby('receive_date','desc')->get();
        return view('purchases/stores/receive_list',compact('receives','store'));
    }
    public function storeReceiveCancel(){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first();
        return view('purchases/stores/receive_cancel',compact('store'));
    }

    #-------PENERIMAAN KONSINYASI-----------
    public function receiveConsignmentIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first();
            $suppliers=Suppliers::where([['comp_id',$this->compid()],['supplier_consignment_state','1']])->get(); 
            return view('consignment/receive',compact('suppliers','store'));
        }
        return view('consignment/receive_index');
    }
    public function receiveConsignmentMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $suppliers=Suppliers::where([['comp_id',$this->compid()],['supplier_consignment_state','1']])->get();  //ambil supplier dalam satu perusahaan
        return view('consignment/receive',compact('suppliers','store'));
    }
    public function receiveConsignmentListindex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $receives=Receives::with('supplier','store','products')->where([['store_id',$storeid],['receive_state','1'],
                    ['receive_type','2']])->orderby('receive_date','desc')->get();
            return view('consignment/receive_list',compact('receives','store'));
        }
        return view('consignment/receive_list_index');
    }

    public function receiveConsignmentList(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        $receives=Receives::with('supplier','store','products')->where([['store_id',$storeid],['receive_state','1'],
                    ['receive_type','2']])->orderby('receive_date','desc')->get();
        return view('consignment/receive_list',compact('receives','store'));
    }

    public function receiveConsignmentListByStore($storeid){ 
        $store=Stores::where('id',$storeid)->first();
        $receives=Receives::with('supplier','store','products')->where([['store_id',$storeid],['receive_state','1'],
                    ['receive_type','2']])->orderby('receive_date','desc')->get();
        return view('consignment/receive_list',compact('receives','store'));
    }
    public function receiveConsignmentCancelIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            return view('consignment/receive_cancel',compact('store'));
        }
        return view('consignment/receive_cancel_index');
    }

    public function receiveConsignmentCancel(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();
        return view('consignment/receive_cancel',compact('store'));
    }

    public function saveProductSupplier($supplierid,$arrproducts){
        $products = json_decode(json_encode($arrproducts),false); //array to object 
        $supplier=Suppliers::where('id',$supplierid)->first();
        foreach ($products as $key => $product) {
            $productid=$product->product_id;
            $foundstore=ProductSuppliers::where([['supplier_id',$supplierid],['product_id',$productid]])->count();
            if($foundstore==0){
                $supplier->products()->attach([
                    'product_id'=>$productid],
                    [
                        'supplier_id'=>$supplierid, 
                        'product_supllier_state'=>'1'
                    ]);
            }
        }
        return;
    }
    
    public function autoReceiveNo($storeid,$rcvtype)
    {
        $bulan=date("m");
        $tahun=date("y");
        $strNewId = "RCVP-".$rcvtype.$tahun.$bulan."0001";
        while($this->findNO($strNewId,$storeid)){
            $intNewId=substr($strNewId,-4)+1;
            switch (strlen($intNewId)) {
                case 1:
                    $strNewId="RCVP-".$rcvtype.$tahun.$bulan.'000'.$intNewId;
                    break;
                case 2:
                    $strNewId="RCVP-".$rcvtype.$tahun.$bulan.'00'.$intNewId;
                    break;
                case 3:
                    $strNewId="RCVP-".$rcvtype.$tahun.$bulan.'0'.$intNewId;
                    break;
                case 4:
                    $strNewId="RCVP-".$rcvtype.$tahun.$bulan.$intNewId;
                    break;
            }
        }
        return $strNewId;
    }

    private function findNO($rcvno,$storeid){
        $receive=Receives::where([['receive_no',$rcvno],['store_id',$storeid]])->first();
        if($receive){
          return true;
        }
        return false;
    }
}
