<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sales; 
use App\Models\Payments;
use App\Models\SalePayments;
use App\Models\SaleProducts;
use App\Models\Products;
use App\Models\ProductGroups;
use App\Models\Members;
use App\Models\Cards;
use App\Models\Promotions;
use App\Models\Promotion_Products;
use App\Models\NonStocks;
use App\Models\Stores;
use App\Models\Shifting;
use App\Models\PendingSales;  
use App\Models\Spots;
use App\Models\Dashboard;

use App\Services\StockCardServices;
use App\Services\NonStockServices;
use App\Services\MemberServices;

use League\Fractal\TransformerAbstract;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

use App\Http\Controllers\SalesController;
use App\Http\Controllers\APIPendingSales;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\APIGajaPayments;
use App\Http\Controllers\APIKootaPayments;
use App\Http\Controllers\DashboardController;

use Session;
use DB;
use \Carbon\Carbon;

class APISales extends Controller
{
    public function salesSave(Request $request)
    {    
        $salescontrol=new SalesController;    
        $pendingsales=new APIPendingSales;

        $arrProductStock=array();

        $sale_cashier=$request->input('sale_cashier'); 
        $storeid=$request->input('store_id');
        $saledate=date("Y-m-d H:i:s",strtotime($request->input('sale_date')));
        $autoNo=$salescontrol->autoSalesNo($storeid);

        // dd($autoNo);

        $sale_total=$request->input('sale_total'); 
        $memberno=$request->input('member_no'); 
        $pendingno=$request->input('sale_no'); 
        $spotid="";
        //hapus kalau ada sebelumnya di simpan sementara
        $pendingdate=date("Y-m-d H:i:s");  
        if($pendingno!=null){
           $pending=PendingSales::where([['pending_no',$pendingno],['store_id',$storeid]])->first();
           if($pending!=null){
              $pendingdate=$pending->pending_date;
              $spotid=$pendingno;
           }
        }

        $salesc=$request->input('sale_service_charge'); 
        $saledisc=$request->input('sale_disc'); 
        $saletax=$request->input('sale_tax'); 

    

        $sales=new Sales;
        $sales->sale_no=$autoNo;
        $sales->store_id=$storeid;
        $sales->member_id='0';
        $sales->sale_cashier=$sale_cashier;
        $sales->sale_disc=$saledisc;
        $sales->sale_total=$sale_total;
        $sales->sale_state='1';
        $sales->sale_date=$saledate;
        $sales->sale_tax=$saletax;
        $sales->sale_service_charge=$salesc;
        $sales->save();

        //simpan produk
        $totaltrans=$sale_total;//tanpa service charge,discount dan pajak;
        $totalpurchased=$sale_total+$salesc-$saledisc+$saletax;

        

        for ($i=0; $i < count($request->input('sale_products')) ; $i++) {  
          $product_id=$request->input('sale_products')[$i]['product_id']; 

          //harga beli ambil dari produk store
          $storebp=function ($query) use ($storeid){
            $query->where('id',$storeid);
          };
          $productstore=Products::with(['stores'=>$storebp])->whereHas('stores',$storebp)->where('id',$product_id)->first();
          
          $productbuyprice=$productstore->stores[0]['product_stores']['product_buy_price'];
          $productstockstate=$productstore->stores[0]['product_stores']['product_stock_state'];
          
          $products=Products::where('id',$product_id)->first();

          $prodqty=$request->input('sale_products')[$i]['sale_product_qty']; 
          $prodprice=$request->input('sale_products')[$i]['sale_product_price']; 
          $proddisc=$request->input('sale_products')[$i]['sale_product_disc']; 
          $note=isset($request->input('sale_products')[$i]['sale_product_note'])?$request->input('sale_products')[$i]['sale_product_note']:""; 
          
          $sales->products()->attach([
          'product_id'=>$product_id],
          [
              'sale_id'=>$sales->id,
              'sale_product_qty'=>$prodqty,
              'sale_product_price'=>$prodprice,
              'sale_product_buy_price'=>$productbuyprice,
              'sale_product_disc'=>$request->input('sale_products')[$i]['sale_product_disc'],
              'sale_product_total'=>$request->input('sale_products')[$i]['sale_product_total'],
              'sale_product_note'=>$note       
          ]);

          //untuk non stok tidak terjadi pengurangan
          if($productstockstate=='1'){ 
              $arrProductStock[]=[
                  'product_id'=>$request->input('sale_products')[$i]['product_id'],
                  'product_qty'=>$request->input('sale_products')[$i]['sale_product_qty']];
          }
        }
        
        //simpan promosi
        // $promotions=$request->promotions;
        // $promoset=isset($request->promotions)?$request->promotions:null;
        // if($promoset!=null){
        //   for($i=0;$i<count($request->input('promotions'));$i++){
        //     $promono=$request->input('promotions')[$i]['promo_no'];
        //     $sales->promotions()->attach([
        //       'promo_no'=>$promono],
        //       [
        //           'sale_no'=>$autoNo, 
        //           'sale_promo_qty'=>$request->input('promotions')[$i]['promo_qty'],
        //           'sale_promo_price'=>$request->input('promotions')[$i]['promo_price'],
        //       ]);
        //   }
        // }

        // //simpan pembayaran
        for ($i=0; $i < count($request->input('sale_payments')) ; $i++) { 
          $payid=$request->input('sale_payments')[$i]['pay_id']; 
          $payed=$request->input('sale_payments')[$i]['sale_pay_payed']; 
          $payreturn=$request->input('sale_payments')[$i]['sale_pay_return'];  
          if($payreturn<0){
            $payreturn=0; //untuk handle error pertamina (pembayaran berdasarkan rp bukan liter)
          }
          
          //simpan bank yang di entry
          $payment=Payments::where('id',$payid)->first();
          $paydesc=$payment->pay_desc;
          $cardno=$payment->pay_desc;
          if($payid=='2' or $payid=='3'){
            $cardno=isset($request->input('sale_payments')[$i]['card_no'])?$request->input('sale_payments')[$i]['card_no']:"";
            $cardbankissuer=isset($request->input('sale_payments')[$i]['card_bank_issuer'])?$request->input('sale_payments')[$i]['card_bank_issuer']:"";
            $cardholdername=isset($request->input('sale_payments')[$i]['card_holder_name'])?$request->input('sale_payments')[$i]['card_holder_name']:"";

            if(Cards::where('card_no',$cardno)->count()==0){
              $card=new Cards;
              $card->card_no=$cardno;
              $card->card_bank_issuer=$cardbankissuer;
              $card->card_holder_name=$cardholdername;
              $card->save();
            }
          } 

          //pembayaran dengan voucher
          // if($payid=='6'){
          //   $memberserv::updateMemberVoucher($memberno);
          // }

          //pembayaran dengan GAJA, update tabel gaja, masukan nomor sales 
          // if($payid=='18'){
          //   $gp=new APIGajaPayments;
          //   $paydesc=$request->input('payments')[$i]['pay_desc'];
          //   $gp->updateSaleNumber($paydesc,$autoNo);
          // }

          // //pembayaran dengan Koota, update tabel gaja, masukan nomor sales 
          // if($payid=='31'){
          //   $kp=new APIKootaPayments;
          //   $paydesc=$request->input('payments')[$i]['pay_desc'];
          //   $kp->updateSaleNumber($paydesc,$autoNo);
          // }
          
          $salepay=new SalePayments;
          $salepay->sale_id=$sales->id;
          $salepay->pay_id=$payid;
          $salepay->card_no=$cardno;
          $salepay->pay_desc=$paydesc; 
          $salepay->sale_pay_payed=$payed;
          $salepay->sale_pay_return=$payreturn; 
          $salepay->sale_pay_purchase=$totalpurchased;
          $salepay->save();
        }

        // $member=Members::where('member_no',$memberno)->first();
        // if($member!=null){
        //   $memberserv::saveMemberPointSales($memberno,$totaltrans);
        // }

        //update untuk produk yang menggunakan konsep stok
        $prodControl=new ProductsController;
        $prodControl::updateProductStock($storeid,$arrProductStock,"OUT"); 

        $sales=Sales::where('id',$sales->id)->with(['products','payments','store.company'])->first();

        //simpan toko dengan sistem shift
        $storetype=Stores::where('id',$storeid)->first()->store_type;
        if($storetype=="4" or $storetype=="5" or $storetype=="6" ){ 
          $this->saveShift($sales);
        }

        //update 11-09-2022 : periksa ritel/tidak ada spot
        //update 23-11-2020 : hapus pending sales
        if($pendingno!=null){
            $pending=PendingSales::where([['pending_no',$pendingno],['store_id',$storeid]])->first();
            if($pending!=null){
              $pendingsales->deletePendingSales($pendingno,$storeid);
            }
        }
        
        return response()->json($sales);
    }

    //------------------------ PEMBATALAN PENJUALAN
    public function salesCancelFind($storeid,$saleno){
      $tgl=date("Y-m-d");//date("dmY"); //ddmmyyyy
      $sale=Sales::with('products')->where([['store_id',$storeid],['sale_no',$saleno]])->first();
      if(!$sale){
        $sale=new Sales;
        $sale->sale_no='error';
        $sale->sale_note="Nomor Sales Tidak Ditemukan"; 
      }
      if($tgl!=substr($sale->sale_date,0,10)){
        $sale=new Sales;
        $sale->sale_no='error';
        $sale->sale_note="Pembatalan tidak dapat dilanjutkan, Beda Hari"; 
      }
      if($sale->sale_state=='0'){
        $sale=new Sales;
        $sale->sale_no='error';
        $sale->sale_note="Pembatalan tidak dapat dilanjutkan, Sudah Di Batalkan'"; 
      }
      return $sale;
    }
    public function salesCancel($id){
      $sale=Sales::with('products.stores')->where('id',$id)->first(); 
      $prodControl=new ProductsController;   
      $arrProductStock=array();

     
      foreach ($sale->products as $product) {
          $arrProductStock[]= array('product_id' => $product->id,
              'product_qty' => $product->sale_products->sale_product_qty
            ); 
      }

      $store_id=$sale->store_id; //toko 
      $prodControl::updateProductStock($store_id,$arrProductStock,"IN"); //kembalikan Stok
  
      //ubah status cancel pada sales
      Sales::where('id',$id)->update(['sale_state'=>'0']);
      
      return response()->json($sale);
  }


  //------------------------ PROSES RETRIEVE DATA
  public function salesById($saleid){
      $sales=Sales::where('id',$saleid)->with(['products','payments','store.company'])->first();  
      return response()->json($sales); 
  }
  public function salesTodayStore($storeid){
    //untuk sales yang tidak menggunakan shift
    $sales=Sales::whereDate('created_at',\Carbon\Carbon::today())
          ->with(['products','payments','store.company'])
          ->where([['store_id',$storeid],['sale_state','!=',0]])
          ->orderby('sales.id','desc')->get();

    //untuk store menggunakan sistem shift
    $storetype=Stores::where('id',$storeid)->first()->store_type;
    if($storetype=="4" or $storetype=="5" or $storetype=="6" ){ 
      //shift aktif
      $shifting=function ($query) use ($storeid){
        $query->where([['store_id',$storeid],['shift_state',1]]);
      };
      $sales=Sales::with(['products','payments','stores.companys','shifting'=>$shifting])
            ->whereHas('shifting',$shifting)
            ->where([['store_id',$storeid],['sale_state','!=',0]])->get();
    }
    return $sales;
  }

    public function salesnewnumber($storeid){
        $salescontrol=new SalesController;  
        $salesNo=$salescontrol->autoSalesNo($storeid);
        $sales=new Sales;
        $sales->sale_no=$salesNo;
        return response()->json($sales); 
    }



  public function salescompanytoday($compid){
    $today=Carbon::today();
    $stores=function ($query) use ($compid){
        $query->where('stores.comp_id','=',$compid);
    };
    $sales=Sales::whereDate('created_at',$today)
          ->with(['stores'=>$stores,'products'])
          ->where('sale_state','!=',0)
          ->whereHas('stores',$stores)->get();
    return response()->json($sales); 
  }
  public function salescompanytodayrecap($compid){
    $today=Carbon::today();
    $stores=function ($query) use ($compid){
        $query->where('stores.comp_id','=',$compid);
    };
    $sales=Sales::whereDate('created_at',$today)
          ->with(['stores'=>$stores,'products'])
          ->where('sale_state','!=',0)
          ->whereHas('stores',$stores)->sum('sale_total');
    return response()->json($sales); 
  }
  public function salescompanyproducttopten($compid){
    $blnthn = date("m").date("y");
    $sales=DB::table('sale_products')
      ->join('sales','sale_products.sale_no','=','sales.sale_no')
      ->join('products','sale_products.product_id','=','products.product_id')
      ->select('products.product_shortdesc as product_desc',
          DB::raw('SUM(sale_product_qty) as product_sales_qty'),
          DB::raw('SUM(sale_product_price) as product_sales_total'))
      ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
      ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
      ->groupby('products.product_id','products.product_shortdesc')
      ->orderby('product_sales_qty','desc')
      ->take(10)
      ->get();
    return response()->json($sales); 
  }
  public function salesstoreproducttopten($storeid){
    $blnthn = date("m").date("y");
    $sales=DB::table('sale_products')
      ->join('sales','sale_products.sale_no','=','sales.sale_no')
      ->join('products','sale_products.product_id','=','products.product_id')
      ->select('products.product_shortdesc as product_desc', 
          'products.product_file_loc as product_file_loc',
          DB::raw('SUM(sale_product_qty) as product_sales_qty'),
          DB::raw('SUM(sale_product_price) as product_sales_total'))
      ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
      ->where(DB::raw('sales.store_id'),'=',$storeid)
      ->groupby('products.product_id','products.product_shortdesc')
      ->orderby('product_sales_qty','desc')
      ->take(10)
      ->get();
    return response()->json($sales); 
  }

  public function salesTodayStoreTopTen($storeid){
    $tgl=date("dmY"); //ddmmyyyy     
    $sales=DB::table('sale_products')
      ->join('sales','sale_products.sale_id','=','sales.id')
      ->join('products','sale_products.product_id','=','products.id')
      ->select('products.id','products.product_plu','products.product_name as product_name', 
          'products.product_pic_loc as product_pic_loc',
          DB::raw('SUM(sale_product_qty) as product_stock'),
          DB::raw('SUM(sale_product_price) as product_price'))
      ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$tgl)
      ->where('sales.store_id',$storeid)
      ->groupby('products.id','products.product_name')
      ->orderby('product_stock','desc')
      ->take(10)
      ->get();
    //   $stores=function ($query) use ($storeid){
    //     $query->where('stores.id',$storeid);
    // };
    // $products=Products::with(['stores'=>$stores,'productcategory','brand'])->whereHas('stores',$stores)->get();
    // foreach ($products as $key => $product) {
    //     $product->product_buy_price=$product->stores[0]['product_stores']['product_buy_price'];
    //     $product->product_price=$product->stores[0]['product_stores']['product_price'];
    //     $product->product_stock=$product->stores[0]['product_stores']['product_stock'];
    //     $product->product_buffer_stock=$product->stores[0]['product_stores']['product_buffer_stock'];
    //     $product->product_promo_state='0';
    // }
    // return response()->json($products);

    return response()->json($sales); 
  }
//------------------------ PROSES SHIFT
public function saveShift($sales){
  $storeid=$sales->store_id;
  $shift=Shifting::where([['shift_state',"1"],['store_id',$storeid]])->first(); //shift aktif
  if(!$shift){
    return false;
  }
  $shift_no=$shift->shift_no;
  $shift_index=$shift->shift_index;
  $sale_no=$sales->sale_no;
  $total_sales=$sales->sale_total;
  $total_disc=$sales->sale_disc;
  $total_tax=$sales->sale_tax;
  $total_service_charge=$sales->sale_service_charge;

  $shift->sales()->attach([
    'shift_no'=>$shift_no],
    [
        'sale_no'=>$sale_no,
        'shift_index'=>$shift_index,
        'shift_sale_total'=>$total_sales,
        'shift_sale_disc'=>$total_disc, 
        'shift_sale_tax'=>$total_tax,
        'shift_sale_service_charge'=>$total_service_charge,
    ]);

  return true;
}
  //------------------------ PROSES SIMPAN OFFLINE SALES 
  //simpan offline sales per sales, looping ada disisi client
  public function offlineSalesSave(Request $request){
    $salescontrol=new SalesController;   
    $stockcard=new StockCardServices;
    $nonstocks=new NonStockServices;
    $memberserv=new MemberServices;
    $pendingsales=new APIPendingSales;

    $arrStock=array();
    // $arrnonstock=array();

    $sale_cashier=$request->input('sale_cashier'); 
    $store_id=$request->input('store_id');     
    $autoNo=$request->input('sale_no');
    $sale_total=$request->input('sale_total');
    $memberno=$request->input('member_no');
    $saledate=$request->input('sale_date'); 
    $salesc=$request->input('sale_service_charge');
    $saledisc=$request->input('sale_disc');  
    $saletax=$request->input('sale_tax');  

    $sales=new Sales;
    $sales->sale_no=$autoNo;
    $sales->store_id=$store_id;
    $sales->member_no=$memberno;
    $sales->sale_cashier=$sale_cashier;
    $sales->sale_disc=$saledisc;
    $sales->sale_total=$sale_total; 
    $sales->sale_state='1';
    $sales->sale_date=$saledate;
    $sales->sale_tax=$saletax;
    $sales->sale_service_charge=$salesc;
    $sales->save();

    //simpan produk
    $totaltrans=$sale_total;//tanpa service charge,discount dan pajak;
    $totalpurchased=$sale_total+$salesc-$saledisc+$saletax;


    for ($i=0; $i < count($request->input('products')) ; $i++) {
      $product_id=$request->input('products')[$i]['product_id'];
      $products=Products::where('product_id','=',$product_id)->first();
      $prodqty=$request->input('products')[$i]['product_sales_qty'];
      $prodprice=$request->input('products')[$i]['product_price'];
      $proddisc=$request->input('products')[$i]['product_sales_disc'];
      $note=isset($request->input('products')[$i]['product_sales_note'])?$request->input('products')[$i]['product_sales_note']:null;
      
      if($products->product_state=="3"){ //perlakuan penyimpanan khusus untuk mengurangi stok produk pembangun
        $mentono=$products->product_plu;
        $this->saveMenuResto($mentono,$store_id,$sale_cashier,$prodqty);
      }

      $sales->products()->attach([
      'product_id'=>$product_id],
      [
          'sale_no'=>$autoNo,
          'sale_product_qty'=>$prodqty,
          'sale_product_price'=>$prodprice,
          'sale_buy_price'=>$products->product_buy_price,
          'sale_product_disc'=>$request->input('products')[$i]['product_sales_disc'],
          'sale_product_total'=>$request->input('products')[$i]['product_sales_total'],
          'sale_product_note'=>$note       
      ]);
      $arrStock[]= array('product_id' => $request->input('products')[$i]['product_id'],
                       'trans_no'=>$autoNo,
                       'trans_date' => $saledate,
                       'stock_qty' => $request->input('products')[$i]['product_sales_qty']
                  );
      $noreff=isset($request->input('products')[$i]['reff_nonstock'])?$request->input('products')[$i]['reff_nonstock']:null;
      if($products->product_stockstate=='0'){ 
          // $arrnonstock[]=array('sale_no' => $autoNo,
          //                       'product_id' => $product_id,
          //                       'ns_date' => $saledate,
          //                       'ns_reff' => $noreff,
          //                       'ns_fee' => 0,
          //                       'ns_price' => $prodprice,
          //                       'ns_qty' => $prodqty);
                               
          // $nonstocks::saveNonStock($sale_cashier,$store_id,$arrnonstock); 
          $nonstocks::updateProductNonStock($store_id,$product_id,$prodqty); //ditambahkan agar bisa di kurangi proses stock card
      }

    }
    
    $stockcard::updateProductStock($store_id,$arrStock,"SUB"); //update stock tabel produk
    $stockcard::updateStockCard($sale_cashier,$arrStock,"SALES"); //tambahkan histori di table stockcard

    //simpan promosi
    $promotions=$request->promotions;
    $promoset=isset($request->promotions)?$request->promotions:null;
    if($promoset!=null){
      for($i=0;$i<count($request->input('promotions'));$i++){
        $promono=$request->input('promotions')[$i]['promo_no'];
        $sales->promotions()->attach([
          'promo_no'=>$promono],
          [
              'sale_no'=>$autoNo, 
              'sale_promo_qty'=>$request->input('promotions')[$i]['promo_qty'],
              'sale_promo_price'=>$request->input('promotions')[$i]['promo_price'],
          ]);
      }
    }

    // //simpan pembayaran
    for ($i=0; $i < count($request->input('payments')) ; $i++) {
      $payid=$request->input('payments')[$i]['pay_id'];
      $payed=$request->input('payments')[$i]['pay_payed'];
      $payreturn=$payed-$totalpurchased;
      if($payreturn<0){
        $payreturn=0; //untuk handle error pertamina (pembayaran berdasarkan rp bukan liter)
      }
      
      
      //simpan bank yang di entry
      $payment=Payments::where('pay_id',$payid)->first();
      $paydesc=$payment->pay_desc;
      $cardno=$payment->pay_desc;
      if($payment->pay_state=="2"){
        $cardno=substr($autoNo,-8).$i;
        $cardbankissuer=$request->input('payments')[$i]['card_bank_issuer'];
        $cardholdername=$request->input('payments')[$i]['card_holder_name'];

        if(Cards::where('card_no','=',$cardno)->count()==0){
          $card=new Cards;
          $card->card_no=$cardno;
          $card->card_bank_issuer=$cardbankissuer;
          $card->card_holder_name=$cardholdername;
          $card->save();
        }
      } 

      //pembayaran dengan voucher
      if($payid=='6'){
        $memberserv::updateMemberVoucher($memberno);
      }

      $salepay=new SalePayments;
      $salepay->sale_no=$autoNo;
      $salepay->pay_id=$payid;
      $salepay->card_no=$cardno;
      $salepay->pay_desc=$paydesc; 
      $salepay->sale_pay_payed=$payed;
      $salepay->sale_pay_return=$payreturn; //sebenernya engga perlu di simpen, kalaupun disimpen harusnya di tabel sales
      $salepay->sale_pay_purchase=$totalpurchased;
      $salepay->save();
    }

    //proses keanggotaan
    $memberserv->createMemberGuest($store_id);
    $member=Members::where('member_no',$memberno)->first();

    if($member!=null){
      $memberserv::saveMemberPointSales($memberno,$totaltrans);
    }

    $sales=Sales::where('sale_no','=',$autoNo)->with(['products','payments','stores.companys'])->first();

    //simpan toko dengan sistem shift
    $storetype=Stores::where('store_id',$store_id)->first()->store_type;
    if($storetype=="4" or $storetype=="5" or $storetype=="6" ){ 
      $this->saveShift($sales);
    }


    return response()->json($sales);
  }

   #-----simpan sales dari amanda (untuk cloud yang memiliki client/idependent server)
   public function saveSalesFromAmanda($sale){
    $findsale=Sales::where('sale_no',$sale->sale_no)->first();
    if($findsale!=null){
        return;
    }


    $salescontrol=new SalesController;   
    $stockcard=new StockCardServices;
    $nonstocks=new NonStockServices;
    $memberserv=new MemberServices;
    $pendingsales=new APIPendingSales;

    $arrStock=array();
    // $arrnonstock=array();

    $sale_cashier=$sale->sale_cashier; 
    $store_id=$sale->store_id;     
    $saleno=$sale->sale_no;
    $sale_total=$sale->sale_total;
    $memberno=$sale->member_no;
    $saledate=$sale->sale_date; 
    $salesc=$sale->sale_service_charge;
    $saledisc=$sale->sale_disc;  
    $saletax=$sale->sale_tax;  

    $sales=new Sales;
    $sales->sale_no=$saleno;
    $sales->store_id=$store_id;
    $sales->member_no=$memberno;
    $sales->sale_cashier=$sale_cashier;
    $sales->sale_disc=$saledisc;
    $sales->sale_total=$sale_total; 
    $sales->sale_state=$sale->sale_state;
    $sales->sale_date=$saledate;
    $sales->sale_tax=$saletax;
    $sales->sale_service_charge=$salesc;
    $sales->save();

    //simpan produk
    $totaltrans=$sale_total;//tanpa service charge,discount dan pajak;
    $totalpurchased=$sale_total+$salesc-$saledisc+$saletax;

    $productcontrol=new ProductsController;
    foreach($sale->products as $ket => $product ){
      //simpan produk pada tabel master produk
      $productcontrol->saveProductFromAmanda($product);

      $product_id=$product->product_id;
      $products=Products::where('product_id','=',$product_id)->first();
      $prodqty=$product->saleproducts->sale_product_qty;
      $prodprice=$product->saleproducts->sale_product_price;
      $proddisc=$product->saleproducts->sale_product_disc;
      $note=isset($product->saleproducts->sale_product_note)?$product->saleproducts->sale_product_note:null;
      
      if($products->product_state=="3"){ //perlakuan penyimpanan khusus untuk mengurangi stok produk pembangun
        $mentono=$products->product_plu;
        $this->saveMenuResto($mentono,$store_id,$sale_cashier,$prodqty);
      }

      $sales->products()->attach([
      'product_id'=>$product_id],
      [
          'sale_no'=>$saleno,
          'sale_product_qty'=>$prodqty,
          'sale_product_price'=>$prodprice,
          'sale_buy_price'=>$products->product_buy_price,
          'sale_product_disc'=>$proddisc,
          'sale_product_total'=>$product->saleproducts->sale_product_total,
          'sale_product_note'=>$note       
      ]);
      $arrStock[]= array('product_id' => $product_id,
                       'trans_no'=>$saleno,
                       'trans_date' => $saledate,
                       'stock_qty' => $prodqty
                  );
      $noreff="";
      if($products->product_stockstate=='0'){ 
          // $arrnonstock[]=array('sale_no' => $saleno,
          //                       'product_id' => $product_id,
          //                       'ns_date' => $saledate,
          //                       'ns_reff' => $noreff,
          //                       'ns_fee' => 0,
          //                       'ns_price' => $prodprice,
          //                       'ns_qty' => $prodqty);
                               
          // $nonstocks::saveNonStock($sale_cashier,$store_id,$arrnonstock); 
          $nonstocks::updateProductNonStock($store_id,$product_id,$prodqty); //ditambahkan agar bisa di kurangi proses stock card
      }
    }
    
    $stockcard::updateProductStock($store_id,$arrStock,"SUB"); //update stock tabel produk
    $stockcard::updateStockCard($sale_cashier,$arrStock,"SALES"); //tambahkan histori di table stockcard

    //simpan promosi
    $promoset=isset($sale->promotions)?$sale->promotions:null;
    if($promoset!=null){
      foreach($sale->promotions as $key => $promotion){
        $promono=$promotion->promo_no;
        $sale->promotions()->attach([
          'promo_no'=>$promono],
          [
              'sale_no'=>$saleno, 
              'sale_promo_qty'=>$promotion->salepromotions->sale_promo_qty,
              'sale_promo_price'=>$promotion->salepromotions->sale_promo_price,
          ]);
      }
    }

    // //simpan pembayaran
    foreach($sale->payments as $key => $payment) {
      $payid=$payment->pay_id;
      $paydesc=$payment->pay_desc;
      $payed=$payment->salepayments->sale_pay_payed;
      $cardno=$payment->salepayments->card_no;
      $payreturn=$payed-$totalpurchased;
      if($payreturn<0){
        $payreturn=0; //untuk handle error pertamina (pembayaran berdasarkan rp bukan liter)
      }

      $salepay=new SalePayments;
      $salepay->sale_no=$saleno;
      $salepay->pay_id=$payid;
      $salepay->card_no=$cardno;
      $salepay->pay_desc=$paydesc; 
      $salepay->sale_pay_payed=$payed;
      $salepay->sale_pay_return=$payreturn; //sebenernya engga perlu di simpen, kalaupun disimpen harusnya di tabel sales
      $salepay->sale_pay_purchase=$totalpurchased;
      $salepay->save();
    }

    //proses keanggotaan
    $memberserv->createMemberGuest($store_id);
    $member=Members::where('member_no',$memberno)->first();

    if($member!=null){
      $memberserv::saveMemberPointSales($memberno,$totaltrans);
    }

    //simpan toko dengan sistem shift
    $sales=Sales::where('sale_no','=',$saleno)->with(['products','payments','stores.companys'])->first();
    $storetype=Stores::where('store_id',$store_id)->first()->store_type;
    if($storetype=="4" or $storetype=="5" or $storetype=="6" ){ 
      $this->saveShift($sales);
    }

    return;
  }

  #------------------------------------------------
  #AGILE MANAGER 
  #------------------------------------------------

}

