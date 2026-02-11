<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User; 
use App\Models\Companys;
use App\Models\Stores;
use App\Models\MutationIns;
use App\Models\Products;
use App\Models\ReportStockMutation;
use App\Models\Sales;
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
class ReportStoreController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function userid(){
      return Auth::user()->id;
  }
    public function username(){
        return Auth::user()->username;
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
    public function dailysales($tgl){
      $tglreport=substr($tgl,0,2).'-'.substr($tgl,2,2).'-'.substr($tgl,4,4);
      if($tgl=='NOW'){
        $tgl=date("dmY"); //ddmmyyyy
        $tglreport=date("d-m-Y");
      }

      $sales = DB::table('sale_products')
               ->join('sales','sale_products.sale_no','=','sales.sale_no')
               ->join('products','sale_products.product_id','=','products.product_id')
               ->select('sales.sale_no','sales.sale_date','products.product_id','products.product_desc',
               'sale_products.sale_product_qty','sale_products.sale_product_disc','sale_products.sale_buy_price',
               'sale_products.sale_product_price','sale_products.sale_product_total','products.product_plu')
               ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$tgl)
              //  ->where('sales.store_id','=',$this->storeid())
              //  ->where('sales.sale_state','!=','0')
               ->where([['sales.store_id','=',$this->storeid()],['sales.sale_state','!=','0']])
               ->orderby('sales.sale_no','asc')->get();
      return view("reports/store/dailysales", compact('sales','tglreport'));
      // return response()->json($sales);
    }
    public function invoicepdf($saleid){
        //pemisahan sales dan pembayaran
        $sales = DB::table('sale_products')->join('sales','sale_products.sale_id','=','sales.id')
                 ->join('products','sale_products.product_id','=','products.id')
                //  ->leftjoin('nonstocks','sale_products.sale_no','=','nonstocks.sale_no')
                 ->select('sales.sale_no','sales.sale_date','products.product_desc','sale_products.sale_product_qty',
                 'sale_products.sale_product_price','sale_products.sale_buy_price','sale_products.sale_product_disc',
                 'sales.sale_disc','sales.sale_total','products.product_stock_state')
                 ->where('sale_products.sale_id','=',$saleid)->get();
    
        $salespay=DB::table('sale_payments')->select('sale_id',DB::raw('SUM(sale_pay_payed) as sale_pay_payed'))
                  ->where('sale_id','=',$saleid)->groupBy('sale_id')->get();
    
        $salessize=0;
    
        $sale=Sales::with('stores')->where('id',$saleid)->first();
        $store=$sale->stores;
        $saleno=$sale->sale_no;
    
        foreach ($sales as $key => $value) {
          $salessize++;
        }
    
        $tinggi=325+($salessize*25);
        $pdf = PDF::loadView('reports/store/salesinv', compact('sales','salespay','store'))
                ->setPaper([0, 0, 230, $tinggi], 'potrait'); //dalam point unit(bukan mm)
                // ->setPaper([0, 0, 209, $tinggi], 'potrait'); //dalam point unit(bukan mm)
        $filename='invoice_'.$saleno.'.pdf';
        return $pdf->stream(); //output web
      }
      public function invoicelocal($saleno){
        $sale=Sales::with('stores')->where('sale_no','=',$saleno)->first();
        $tgl=$sale->sale_date;
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
            $printer -> text($sale->stores->store_name."\n");
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
            $printer -> text("powered byt : http://kidswa.web.id\n");
            $printer -> feed(2);
            // $printer -> text($date . "\n");
            
            
            $printer -> cut();
            $printer -> close();
        } catch (Exception $e) {
            echo "Couldn't print to this printer: " . $e -> getMessage() . "\n";
        }
    
        // return view("reports/salesinvlocal"); 
      }

  public function reportStockMutationIndex(){
    $userid=$this->userid(); 
    return view('reports/store/stockmutationindex',compact('userid'));
  }
  public function reportStockMutation(Request $request){
    $storeid=explode(",",$request->get('store_id'));
    $storename=Stores::where('store_id',$storeid)->first()->store_name;
    $store=function ($query) use ($storeid){
      $query->where('stores.store_id',$storeid);
    };
    $products=Products::where([['product_stockstate','1'],['product_state','!=','0']])
      ->whereHas('stores',$store)->get();

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
    $tglAkhir = date('Y-m-d',strtotime("-1 days"))." 23:59:59"; ; //kemarin

    //ubah object menjadi object json
    $reportStockMutationList = json_decode(json_encode($reportStockMutationList), FALSE);
    $productStartIn=$stockservice->stockCountStoreIn($storeid,$tglAwal,$tglAkhir);
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
    $productStartOut=$stockservice->stockCountStoreOut($storeid,$tglAwal,$tglAkhir);
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
    $productStartIn=$stockservice->stockCountStoreIn($storeid,$tglAwal,$tglAkhir);
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

    $productStartOut=$stockservice->stockCountStoreOut($storeid,$tglAwal,$tglAkhir);
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
    return view("reports/store/stockmutation", compact('reportStockMutationList','storename'));
  }

  
}
