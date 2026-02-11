<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Companys;
use App\Models\Stores;
use App\Models\Sales;
use App\Models\MutationIns;
use App\Models\Products;
use App\Models\Promotions;
use App\Models\Shifting;
use App\Models\ReportStockMutation;
use App\Services\StockCardServices;
use App\Services\ProductServices;
use App\Services\StoreServices;
use App\Services\StockService;
use Carbon\Carbon;
use Session;
use DB;
use Entrust;
use PDF;

#-------tidak digunakan lagi digabung antara office dan store
class ReportOfficeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function username(){
        return Auth::user()->username;
    }
    public function compid(){
      return Auth::user()->stores->companys->comp_id;
    }
    public function storeid(){
        return Auth::user()->stores->store_id;
    }
    public function activeuserstoreid(){
        return Auth::user()->stores->store_id;
    }

    public function productstock(){
        $products=ProductServices::getProductStoresByStoreId($this->activeuserstoreid());
        $storename=Auth::user()->stores->store_name;
        return view("reports/store/productstocks", compact('products','storename'));

        // return $products;
    }



    

 //-----laporan sales periodik 
  
    public function invoicepdf($saleno){
        //pemisahan sales dan pembayaran
        $sales = DB::table('sale_products')->join('sales','sale_products.sale_no','=','sales.sale_no')
                 ->join('products','sale_products.product_id','=','products.product_id')
                 ->select('sales.sale_no','sales.sale_date','products.product_desc','sale_products.sale_product_qty',
                 'sale_products.sale_product_price','sale_products.sale_buy_price','sale_products.sale_product_disc')
                 ->where('sale_products.sale_no','=',$saleno)->get();
    
        $salespay=DB::table('sale_payments')->select('sale_no',DB::raw('SUM(sale_pay_payed) as sale_pay_payed'))
                  ->where('sale_no','=',$saleno)->groupBy('sale_no')->get();
    
        $salessize=0;
    
    
        foreach ($sales as $key => $value) {
          $salessize++;
        }
    
        $tinggi=325+($salessize*25);
        $pdf = PDF::loadView('reports/store/salesinv', compact('sales','salespay'))
                ->setPaper([0, 0, 210, $tinggi], 'potrait'); //dalam point unit(bukan mm)
                // ->setPaper([0, 0, 209, $tinggi], 'potrait'); //dalam point unit(bukan mm)
        $filename='invoice_'.$saleno.'.pdf';
        return $pdf->stream(); //output web
    
      }
      public function invoicelocal($saleno){
        $tgl=Sales::where('sale_no','=',$saleno)->first()->sale_date;
        $tgltrans=substr($tgl,8,2).'-'.substr($tgl,5,2).'-'.substr($tgl,0,4);
        $sales = DB::table('sale_products')->join('sales','sale_products.sale_no','=','sales.sale_no')
                 ->join('products','sale_products.product_id','=','products.product_id')
                 ->select('sales.sale_no','sales.sale_date','products.product_desc','sale_products.sale_prod_qty',
                 'sale_products.sale_prod_price','sale_products.sale_buy_price','sale_products.sale_prod_disc')
                 ->where('sale_products.sale_no','=',$saleno)->get();
    
        $salespay=DB::table('sale_payments')->select('sale_no',DB::raw('SUM(sale_pay_payed) as sale_pay_payed'))
                  ->where('sale_no','=',$saleno)->groupBy('sale_no')->get();
        try {
            $date = "Monday 6th of April 2015 02:56:25 PM";
            $logo = EscposImage::load('../public/images/kidswa_logos.png', false);
            $connector = new WindowsPrintConnector("struk");
            $printer = new Printer($connector);
            
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            // $printer -> graphics($logo);
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> text("\n");
            $printer -> text("Kidswa Shop\n");
            $printer -> selectPrintMode();
            $printer -> feed();
            /* Title of receipt */
            $printer -> setEmphasis(true);
            $printer -> text($saleno."\n");
            // $printer -> text("SALES INVOICE\n");
            $printer -> setEmphasis(false);
            $printer -> text($tgltrans."\n");
    
            /* Items */
            $printer -> setJustification(Printer::JUSTIFY_LEFT);
            $printer -> setEmphasis(true);
            // $printer -> text(new item('', '$'));
            $printer -> setEmphasis(false);
            $totdisc=0;
            $totpurchase=0;
            foreach ($sales as $key => $sale) {
              $printer -> setJustification(Printer::JUSTIFY_LEFT);
              $printer -> text($sale->product_desc."\n");
              $printer -> setJustification(Printer::JUSTIFY_RIGHT);
              $printer -> text($sale->sale_prod_qty." x ");
              $printer -> text(number_format($sale->sale_prod_price)."    ");
              $printer -> text(number_format(($sale->sale_prod_price*$sale->sale_prod_qty)));
              $printer -> text("\n");
              if ($sale->sale_prod_disc!=0) {
                $printer -> text("Disc :".number_format($sale->sale_prod_disc));
                $printer -> text("\n");
              } 
              $totdisc=$totdisc+$sale->sale_prod_disc;
              $totpurchase=$totpurchase+($sale->sale_prod_price*$sale->sale_prod_qty);
            }
            $printer -> setEmphasis(true);
            /* Tax and total */
            // $printer -> text($tax);
            // $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer -> text("Total Belanja :".number_format($totpurchase)."\n");
            if($totdisc!=0){
              $printer -> text("Hemat (Disc):".number_format($totdisc)."\n");
            }
            // $printer -> selectPrintMode();
            $printer -> text("Total :".number_format($totpurchase-$totdisc)."\n");
            foreach ($salespay as $key => $salepay){
              $printer -> text("Bayar :".number_format($salepay->sale_pay_payed)."\n");
              $printer -> text("Kembali :".number_format($salepay->sale_pay_payed-($totpurchase-$totdisc))."\n");
            }
            $printer -> feed(2);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> text("Terimakasih \n");
            $printer -> text("http://kidswa.web.id\n");
            $printer -> feed(2);
            // $printer -> text($date . "\n");
            
            
            $printer -> cut();
            $printer -> close();
        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }
    
        // return view("reports/salesinvlocal"); 
      }

      public function plgross($blnthn){

        $compid=$this->compid();
        if($blnthn=='NOW'){
          $blnthn=date("mY"); //mmyyyy
        }
        $bulantahun=substr($blnthn,0,2).substr($blnthn,4,2);
        $bulan = array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli',
        '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'Nopember', '12'=>'Desember');
        $bulanlap=$bulan[substr($blnthn,0,2)];
        $tahunlap=substr($blnthn,2,4);
    
        $bln=substr($blnthn,0,2);
        $thn=substr($blnthn,4,2);
    
        $blnthn = $bln.$thn;   
        
        //ada masalah, belum di revisi 041219 totalnya beda dengan lap penjalan perioik

        $plgross=DB::table('sale_products')
                 ->join('sales','sale_products.sale_no','=','sales.sale_no') 
                 ->join('products','sale_products.product_id','=','products.product_id')
                 ->select(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y") as tglsales'),
                 DB::raw('SUM(sale_buy_price*sale_product_qty) as tot_product_buy_price'),
                 DB::raw('SUM(sale_product_price*sale_product_qty) as tot_product_sales_price'),
                 DB::raw('SUM(sale_product_disc*sale_product_qty) as tot_product_disc'),
                 DB::raw('SUM((sale_product_price-sale_product_disc)*sale_product_qty) as tot_product_sales_disc'),
                 DB::raw('SUM(((sale_product_price-sale_product_disc)-sale_buy_price)*sale_product_qty) as total_pl'))
                 ->where([[DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn],
                 [DB::raw('LEFT(sales.store_id,4)'),'=',$this->compid()],
                 ['sales.sale_state','!=','0']])
                //  ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
                //  ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$this->compid())
                 ->groupby('tglsales')
                 ->get();
       
          // return $plgross;
          return view('/reports/office/plgross',compact('plgross','bulanlap','tahunlap'));   
      } 

      //---------menu resto
      public function menuproductlist(){
        $prodstate='3';
        $product=function($query) use ($prodstate){
          $query->where('product_state',$prodstate);
        };
        // $promos=Promotions::with('products')->where([['comp_id',$this->compid()],[]])->get();

        $promos=Promotions::with(['products'=>$product])->where('comp_id',$this->compid())->whereHas('products',$product)->get();

        foreach ($promos as $key => $promo) {
          $promoid=$promo->promo_no;
          $product=Products::with("productcats")->where('product_plu','=',$promoid)->first();
          if($product!=null){
            $prodcat=$product->productcats->prodcat_desc;
            $promo->promo_rule=$prodcat; //titip objek kriteria 

            // dd($promo->promo_rule);
          }
          
          
        }
        return view('/reports/office/menuproductlist',compact('promos'));
      }
    //---Laporan Penjualan Detail
    public function salespaymentindex(){
      return view('/reports/office/salespaymentindex');
    }
    public function salespaymentopen(Request $request){
      $selectedstores=explode(",",$request->get('store_id'));
      $monthyear=$request->get("month_year");
      $stores=Stores::where('store_id',$selectedstores)->first();   
      $storeid=$stores->store_id; 
      
      $bulan = array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni', '07'=>'Juli',
      '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'Nopember', '12'=>'Desember');
      $bulanlap=$bulan[substr($monthyear,0,2)]." ".substr($monthyear,2,4);

      $salesdiv=DB::table('sale_products')
               ->join('products','sale_products.product_id','=','products.product_id')
               ->join('product_categorys','product_categorys.prodcat_id','=','products.prodcat_id')
               ->select('sale_no',
                 DB::raw('SUM(IF (prodcat_division="0",sale_product_price*sale_product_qty,0)) AS sale_prod'),
                 DB::raw('SUM(IF (prodcat_division="1",sale_product_price*sale_product_qty,0)) AS sale_food'),
                 DB::raw('SUM(IF (prodcat_division="2",sale_product_price*sale_product_qty,0)) AS sale_beverage')
               )->groupBy('sale_no');


     $sales = DB::table('sales')
               ->joinSub($salesdiv,'salesdiv',function($join){
                 $join->on('sales.sale_no','=','salesdiv.sale_no');})
               ->select(DB::raw('SUBSTRING(sales.sale_no,9) AS sale_no'),'sales.sale_date','salesdiv.sale_prod','salesdiv.sale_food','salesdiv.sale_beverage',
                        'sales.sale_disc','sales.sale_total','sales.sale_tax','sales.sale_service_charge',
                        DB::raw('((sale_total-sale_disc)+sale_tax+sale_service_charge) AS totalsales'))
               ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%Y")'),'=',$monthyear)
               ->where('sales.store_id',$storeid)->get();
      
      return view("reports/office/salespayment", compact('stores','sales','bulanlap'));
    }

    public function salespayment($storeid,$tgl){
      $tglreport=substr($tgl,0,2).'-'.substr($tgl,2,2).'-'.substr($tgl,4,4);
      if($tgl=='NOW'){
        $tgl=date("dmY"); //ddmmyyyy
        $tglreport=date("d-m-Y");
      }
      $stores=Stores::where('store_id',$storeid)->first();   


      $salesdiv=DB::table('sale_products')
                ->join('products','sale_products.product_id','=','products.product_id')
                ->join('product_categorys','product_categorys.prodcat_id','=','products.prodcat_id')
                ->select('sale_no',
                  DB::raw('SUM(IF (prodcat_division="0",sale_product_price*sale_product_qty,0)) AS sale_prod'),
                  DB::raw('SUM(IF (prodcat_division="1",sale_product_price*sale_product_qty,0)) AS sale_food'),
                  DB::raw('SUM(IF (prodcat_division="2",sale_product_price*sale_product_qty,0)) AS sale_beverage')
                )->groupBy('sale_no');


      $sales = DB::table('sales')
                ->joinSub($salesdiv,'salesdiv',function($join){
                  $join->on('sales.sale_no','=','salesdiv.sale_no');})
                ->select('sales.sale_no','sale_date','salesdiv.sale_prod','salesdiv.sale_food','salesdiv.sale_beverage',
                         'sale_disc","sale_total","sale_tax","sale_service_charge',
                         DB::raw('((sale_total-sale_disc)+sale_tax+sale_service_charge) AS totalsales'))
                ->where('sales.store_id',$storeid)->get();
               
                dd($sales);

               	
               	


      dd($sales);
      return view("reports/office/salespayment", compact('stores','sales','tglreport'));
      // return response()->json($sales);
    }

   #laporan sales shift 28-11-21
   public function reportSalesShiftIndex(){
      return view("reports/office/shiftsalesindex");
   }    
   public function reportSalesShiftList(Request $request){
      $storeid=$request->get('store_id');
      $store=Stores::where('store_id',$storeid)->first();
      $blnthn=date("mY"); //mmyyyy
      $shifts=Shifting::with('stores')->where([[DB::raw('DATE_FORMAT(shift_date,"%m%Y")'),$blnthn],['store_id',$storeid]])
              ->orderBy('shift_date','desc')->get();
      return view("reports/office/shiftsaleslist",compact("shifts","store"));
   }
   public function reportSalesShiftMain(Request $request){
     $shiftno=$request->shiftno;
     $shift=Shifting::with('stores')->where('shift_no',$shiftno)->first();
     $storeid=$shift->store_id;
     $store=Stores::where('store_id',$storeid)->first();
     $shifting=function ($query) use ($shiftno){
       $query->where('shifting.shift_no',$shiftno);
     };
     $sales=Sales::with(['shifting'=>$shifting,'products'])->whereHas('shifting',$shifting)
            ->where('sale_state','1')->get();
     return view("reports/office/shiftsales",compact("store","shift","sales"));

   }

  public function reportStockMutation(){
    $products=Products::with('productcats')->where([['product_stockstate','1'],['product_state','!=','0'],
          ['comp_id',$this->compid()]])->get();
    //inisialiasi data produk untuk Report Stock Mutation
    //inisialiasi adata dalam bentuk array
    $reportStockMutationList=array();
    foreach ($products as $key => $product) {
      $reportStockMutation=new ReportStockMutation;
      $reportStockMutationList[]=[
        'comp_id'=>$product->comp_id,
        'rsm_product_id'=>$product->product_id,
        'rsm_product_desc'=>$product->product_desc,
        'rsm_product_category_id'=>$product->productcats->prodcat_id,
        'rsm_product_category'=>$product->productcats->prodcat_desc,
        'rsm_product_start_in_qty'=>0,
        'rsm_product_start_in_price'=>$product->product_price,
        'rsm_product_start_out_qty'=>0,
        'rsm_product_start_out_price'=>0,
        'rsm_product_in_qty'=>0,
        'rsm_product_in_price'=>0,
        'rsm_product_out_qty'=>0,
        'rsm_product_out_price'=>0,
        'rsm_product_final_qty'=>0,
        'rsm_product_final_price'=>0,
      ];
    }

    $stockservice=new StockService;
    $tglAwal = date('2018-01-01')." 00:00:00";  
    // $tglAkhir = date('Y-m-d'); 
    $tglAkhir = date('Y-m-d',strtotime("-1 days"))." 23:59:59"; ; //kemarin

    //ubah object menjadi object json
    $reportStockMutationList = json_decode(json_encode($reportStockMutationList), FALSE);
    $productStartIn=$stockservice->stockCountIn($this->compid(),$tglAwal,$tglAkhir);
    foreach ($productStartIn as $key => $startIn) {
      foreach ($reportStockMutationList as $key => $rptStockmutation) {
          if($rptStockmutation->rsm_product_id==$startIn->product_id){
            $rptStockmutation->rsm_product_start_in_qty=$startIn->product_stock_purchase+
                $startIn->product_stock_mutation_in+
                $startIn->product_stock_offmemo_plus;
            $rptStockmutation->rsm_product_start_in_price=$startIn->product_price;
          }
      }  
    }

    //ubah object menjadi object json
    $productStartOut=$stockservice->stockCountOut($this->compid(),$tglAwal,$tglAkhir);
    foreach ($productStartOut as $key => $startOut) {
      foreach ($reportStockMutationList as $key => $rptStockmutation) {
          if($rptStockmutation->rsm_product_id==$startOut->product_id){
            $rptStockmutation->rsm_product_start_out_qty=$startOut->product_stock_retur+
                $startOut->product_stock_mutation_out+
                $startOut->product_stock_sales+
                $startOut->product_stock_offmemo_minus;
            $rptStockmutation->rsm_product_start_out_price=$startOut->product_price;
          }
      }  
    }
  
    #perhitungan stok yang berubah pada hari ini
    $tglAwal = date('Y-m-d')." 00:00:00"; 
    $tglAkhir = date('Y-m-d')." 23:59:59"; 
    // $tglAkhir = date('Y-m-d',strtotime("1 days")); //besok
    $productStartIn=$stockservice->stockCountIn($this->compid(),$tglAwal,$tglAkhir);
    foreach ($productStartIn as $key => $startIn) {
      foreach ($reportStockMutationList as $key => $rptStockmutation) {
          if($rptStockmutation->rsm_product_id==$startIn->product_id){
            $rptStockmutation->rsm_product_in_qty=$startIn->product_stock_purchase+
                $startIn->product_stock_mutation_in+
                $startIn->product_stock_offmemo_plus;
            $rptStockmutation->rsm_product_in_price=$startIn->product_price;
          }
      }  
    }

    $productStartOut=$stockservice->stockCountOut($this->compid(),$tglAwal,$tglAkhir);
    foreach ($productStartOut as $key => $startOut) {
      foreach ($reportStockMutationList as $key => $rptStockmutation) {
          if($rptStockmutation->rsm_product_id==$startOut->product_id){
            $rptStockmutation->rsm_product_out_qty=$startOut->product_stock_retur+
              $startOut->product_stock_mutation_out+
              $startOut->product_stock_sales+
              $startOut->product_stock_offmemo_minus;
            $rptStockmutation->rsm_product_out_price=$startOut->product_price;
          }
      }  
    }
  
    // return $reportStockMutationList;
    $reportStockMutationList=json_encode($reportStockMutationList);
    return view("reports/office/stockmutation", compact('reportStockMutationList'));
  }

    
}


