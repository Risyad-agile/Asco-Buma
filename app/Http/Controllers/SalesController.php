<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\Sales;
use App\Models\SalePayments;
use App\Models\Products;
use App\Models\Stores;
use App\Models\Payments;
use App\Models\ProductCategories;
use App\Models\Members;
use App\Models\Cards;
use App\Models\Promotions;
use App\Models\PromotionProducts;
use App\Models\NonStocks; 
use App\Http\Controllers\ProductsController;

use Session;
use DB;
use PDF;

class SalesController extends Controller
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
    public function username(){
        return Auth::user()->username;
    }
    public function storeid(){
        return Auth::user()->store->id;
    }
    public function compid(){
        return Auth::user()->store->company->id;
    }
    public function index()
    {

    }
    #--------------------------------
    #------PENJUALAN UMUM------------
    #--------------------------------
    public function commonSalesIndex(){
      $storeControl=new StoresController();
      $countStore=$storeControl->storeByCompanyCount();
      if($countStore["count"]=='1'){
          $storeid=$countStore["storeid"]; 
          $store=function ($query) use ($storeid){
            $query->where('id',$storeid);
          };
          $payments=Payments::whereHas('stores',$store)->get();
          if(count($payments)==0){
            $storename=Stores::where('id',$storeid)->first()->store_name;
            $message="Pembayaran untuk Toko ".$storename." belum diaktifkan, silakan aktifkan untuk melakukan transaksi";
            return view('screen_message',compact('message'));
          }
          return view('sales/common_sales',compact('storeid','payments'));
      }
      return view('sales/common_sales_index');
    }
    public function commonSalesMain(Request $request){
      $storeid=$request->get('store_id'); 
      $store=function ($query) use ($storeid){
        $query->where('id',$storeid);
      };
      $payments=Payments::whereHas('stores',$store)->get();
      if(count($payments)==0){
        $storename=Stores::where('id',$storeid)->first()->store_name;
        $message="Pembayaran untuk Toko ".$storename." belum diaktifkan, silakan aktifkan untuk melakukan transaksi";
        return view('screen_message',compact('message'));
      }
      return view('sales/common_sales',compact('storeid','payments'));
    }
    #--------------------------------
    #------PENJUALAN PERUSAHAAN------
    #--------------------------------
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
        $membercontrole=new MembersController;
        $arrProductStock=array();

        $storeid=$request->storeid;
        $autoNo=$this->autoSalesNo($storeid);
        $username=$this->username();
        $membercontrole->createMemberGuest($storeid);

        $memberid=$request->input('memberid');
        if($memberid=='NOTMEMBER'){
          $memberid=Members::where('store_id',$storeid)->first()->id;
        }
        
        $sales=new Sales;
        $sales->sale_no=$autoNo;
        $sales->store_id=$storeid;
        $sales->member_id=$memberid;
        $sales->sale_disc=$request->input('sale_disc');
        $totaltrans=$request->input('sale_total');
        $sales->sale_total=$totaltrans;
        $sales->sale_state='1';
        $sales->sale_date=date("Y-m-d H:i:s");
        $sales->save();

        //simpan produk
        $totaltrans=0;
        for ($i=0; $i < count($request->input('tabproduct')) ; $i++) {
          $product_id=$request->input('tabproduct')[$i]['product_id'];

          //harga beli ambil dari produk store
          $storebp=function ($query) use ($storeid){
            $query->where('id',$storeid);
          };
          $productstore=Products::with(['stores'=>$storebp])->whereHas('stores',$storebp)->where('id',$product_id)->first();
          $productbuyprice=$productstore->stores[0]['product_stores']['product_buy_price'];
          $productstockstate=$productstore->stores[0]['product_stores']['product_stock_state'];

          $products=Products::where('id',$product_id)->first();

          $prodqty=$request->input('tabproduct')[$i]['product_qty'];
          $prodprice=$request->input('tabproduct')[$i]['product_price'];
          $proddisc=$request->input('tabproduct')[$i]['product_disc'];
          // $totaltrans=$totaltrans+(($prodprice-$proddisc)*$prodqty);
          $sales->products()->attach([
          'product_id'=>$product_id],
          [
              'sale_id'=>$sales->id,
              'sale_product_qty'=>$prodqty,
              'sale_product_price'=>$prodprice,
              'sale_product_buy_price'=>$productbuyprice,
              'sale_product_disc'=>$request->input('tabproduct')[$i]['product_disc'],
              'sale_product_total'=>$request->input('tabproduct')[$i]['product_total']
          ]);

          //untuk non stok tidak terjadi pengurangan
          if($productstockstate=='1'){ 
            $arrProductStock[]=['product_id'=>$request->input('tabproduct')[$i]['product_id'],
            'product_qty'=>$request->input('tabproduct')[$i]['product_qty']];
          }
        }
        
        $prodControl=new ProductsController;
        $prodControl::updateProductStock($storeid,$arrProductStock,"OUT"); //update stock tabel produk stores
        
        for ($i=0; $i < count($request->input('tabpay')) ; $i++) {
          $payid=$request->input('tabpay')[$i]['pay_id'];
          $payed=$request->input('tabpay')[$i]['sale_pay_payed'];
          $payreturn=$payed-$totaltrans;
          
          //simpan bank yang di entry
          $payment=Payments::where('id',$payid)->first();
          if($payment->pay_state=="2"){
            $cardno=$request->input('tabpay')[$i]['card_no'];
            $cardbankissuer=$request->input('tabpay')[$i]['card_bank_issuer'];
            $cardholdername=$request->input('tabpay')[$i]['card_holder_name'];
          }

          $cardno="";
          
          //simpan kartu
          if($payid=='2' or $payid=='3'){
            $cardno=isset($request->input('tabpay')[$i]['card_no'])?$request->input('tabpay')[$i]['card_no']:"";
            $cardbankissuer=isset($request->input('tabpay')[$i]['card_bank_issuer'])?$request->input('tabpay')[$i]['card_bank_issuer']:"";
            $cardholdername=isset($request->input('tabpay')[$i]['card_holder_name'])?$request->input('tabpay')[$i]['card_holder_name']:"";

            //kartu baru disimpan, kalau sudah ada tidak perlu disimpan
            if(Cards::where('card_no','=',$cardno)->count()==0){
              $card=new Cards;
              $card->card_no=$cardno;
              $card->card_bank_issuer=$cardbankissuer;
              $card->card_holder_name=$cardholdername;
              $card->save();
            }
          }
          $payid=$request->input('tabpay')[$i]['pay_id'];
          $payment=Payments::where('id',$payid)->first();
          $salepay=new SalePayments;
          $salepay->sale_id=$sales->id;
          $salepay->pay_id=$payid;
          $salepay->card_no=$cardno;
          $salepay->pay_desc=$payment->pay_desc;
          $salepay->sale_pay_payed=$payed;
          $salepay->sale_pay_return=$payed-$totaltrans; //sebenernya engga perlu di simpen, kalaupun disimpen harusnya di tabel sales
          $salepay->sale_pay_purchase=$totaltrans;
          $salepay->save();
        }

        return response()->json(['status' => 'success', 'message' => $sales->id, 'code' => 200]);   
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        print_r("Show ".$id);
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
      $sale=Sales::with('products')->where('id',$id)->first(); 
      $prodControl=new ProductsController;   
      $arrProductStock=array();
      foreach ($sale->products as $product) {
          $arrProductStock[]= array('product_id' => $product->id,
              'product_qty' => $product->sale_products->sale_product_qty); 
      }
      $store_id=$sale->store_id; //toko 
      $prodControl::updateProductStock($store_id,$arrProductStock,"IN"); //Kurangi Stok
  
      //ubah status cancel pada sales
      Sales::where('id',$id)->update(['sale_state'=>'0']);
      return response()->json(['status' => 'success', 'message' => 'Pembatalan Transaksi Berhasil Dilakukan', 'code' => 200]);
    }

    


    
    #-----Pembatalan Penjualan------
    public function salesCancelIndex(){
        return view('sales/sales_cancel_index');
    }
    public function salesCancel(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first(); 
        return view('sales/sales_cancel',compact('store'));
    }
    public function salesCancelCancelByStoreNumber($storeid,$saleno){
      $tgl=date("Y-m-d");//date("dmY"); //ddmmyyyy
      $sale=Sales::with('products')->where([['store_id',$storeid],['sale_no',$saleno]])->first();
      if(!$sale){
        return response()->json(['status' => 'error', 'message' => 'Nomor Sales Tidak Ditemukan', 'code' => 404]);
      }
      if($tgl!=substr($sale->sale_date,0,10)){
        return response()->json(['status' => 'error', 'message' => 'Pembatalan tidak dapat dilanjutkan, Beda Hari', 'code' => 404]);
      }
      if($sale->sale_state=='0'){
        return response()->json(['status' => 'error', 'message' => 'Pembatalan tidak dapat dilanjutkan, Sudah Di Batalkan', 'code' => 404]);
      }
      return $sale;
    }


    #--------------------------------
    #------PENJUALAN UMUM TOKO-------
    #--------------------------------
    public function storeCommonSalesMain(){
      $storeid=$this->storeid(); 
      $store=function ($query) use ($storeid){
        $query->where('id',$storeid);
      };
      $payments=Payments::whereHas('stores',$store)->get();
      if(count($payments)==0){
        $storename=Stores::where('id',$storeid)->first()->store_name;
        $message="Pembayaran untuk Toko ".$storename." belum diaktifkan, silakan aktifkan untuk melakukan transaksi";
        return view('screen_message',compact('message'));
      }
      return view('sales/stores/common_sales',compact('storeid','payments'));
    }
    public function storeSalesCancel(Request $request){
      $storeid=$this->storeid(); 
      $store=Stores::where('id',$storeid)->first(); 
      return view('sales/stores/sales_cancel',compact('store'));
  }
    //------------------tools

    public function getpromos($id){
        //cari promo yang masih aktif dengan id produk yang dikirimkan
        //bisa jadi ada lebih dari satu promo
        $product=function ($query) use ($id){
          $query->where('products.id',$id);
        };
        $promos=Promotions::with('products')
        ->where('promo_state','1')
        ->whereIn('promo_type', ['1', '2']) 
        ->where('comp_id','=',$this->compid())
        ->whereHas('products',$product)->get(); 

        if(count($promos)==0){
            return ["NOTPROMO"];
        }

        return $promos;
    }
   

    // public function autoSalesNo($storeid)
    // {
    //   // aturan nomor Sales : StoreID-SALEyymm1234
    //   // $store_id=$this->storeid();
    //   $bulan=date("m");
    //   $tahun=date("y");
    //   $strNewId = 'SALE-'.$tahun.$bulan."0001";

    //   $lastno=Sales::where('store_id',$storeid)->max('sale_no');
    //   $intNewId=substr($lastno,-4)+1; 
    //   switch (strlen($intNewId)) {
    //       case 1:
    //           $strNewId='SALE-'.$tahun.$bulan.'000'.$intNewId;
    //           break;
    //       case 2:
    //           $strNewId='SALE-'.$tahun.$bulan.'00'.$intNewId;
    //           break;
    //       case 3:
    //           $strNewId='SALE-'.$tahun.$bulan.'0'.$intNewId;
    //           break;
    //       case 4:
    //           $strNewId='SALE-'.$tahun.$bulan.$intNewId;
    //           break;  
    //   }
    //   return $strNewId;
    // }


    public function autoSalesNo($storeid)
    {
      // aturan nomor Sales : StoreID-SALEyymm1234
      // $store_id=$this->storeid();
      $bulan=date("m");
      $tahun=date("y");
      $strNewId = 'SALE-'.$tahun.$bulan."000001";
      if(Sales::where('sale_no',$strNewId)->count()==0){
        return $strNewId;
      }

      $strLastId=DB::table('sales')->where('store_id',$storeid)
        ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$bulan.$tahun)
        ->orderBy('id','desc')
        ->first()->sale_no;

        $intNewId=substr($strLastId,-6)+1; 
        switch (strlen($intNewId)) {
          case 1:
              $strNewId='SALE-'.$tahun.$bulan.'00000'.$intNewId;
              break;
          case 2:
              $strNewId='SALE-'.$tahun.$bulan.'0000'.$intNewId;
              break;
          case 3:
              $strNewId='SALE-'.$tahun.$bulan.'000'.$intNewId;
              break;
          case 4:
              $strNewId='SALE-'.$tahun.$bulan.'00'.$intNewId;
              break;
          case 5:
              $strNewId='SALE-'.$tahun.$bulan.'0'.$intNewId;
              break;
          case 6:
              $strNewId='SALE-'.$tahun.$bulan.$intNewId;
              break;  
        }
    
      return $strNewId;
    }

    private function findSaleNO($saleno,$storeid){
        $sale=Sales::where([['sale_no',$saleno],['store_id',$storeid]])->first();
        if($sale){
          return true;
        }
        return false;
    }
}
