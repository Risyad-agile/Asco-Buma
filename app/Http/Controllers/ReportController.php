<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountMatrix; 
use App\Models\Organizations; 
use App\Models\CSR;
use DB;
use PDF;
class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function comp(){
        return Auth::user()->company->id;
    }

    public function reportDataMatrixIndex(){
        $acm=AccountMatrix::all();
        return view('reports/import/data_matrix',compact('acm'));
    }

    public function reportDataCSRIndex(){
        $csr=CSR::all();
        return view('reports/import/data_csr',compact('csr'));
    }





    #--------------------------------
    #---------LAPORAN MANAJER--------
    #--------------------------------
    #----struk pdf
    public function commonSalesReceiptPDF($saleid){
        $sale=Sales::with('store','products','payments')->where('id',$saleid)->first();
        $salessize=$sale->products->count();
        $tinggi=325+($salessize*25);
        $pdf = PDF::loadView('reports/shared/pdf_sales_receipt', compact('sale'))
                ->setPaper([0, 0, 230, $tinggi], 'potrait'); //dalam point unit(bukan mm)
                // ->setPaper([0, 0, 209, $tinggi], 'potrait'); //dalam point unit(bukan mm)
        $filename='receipt_'.$sale->sale_no.'.pdf';
        return $pdf->stream(); //output web
    }

    //-----laporan stok
    public function productStockIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $prodControl=new ProductsController;   
            $products=$prodControl->productByStore($storeid);
            return view("reports/shared/product/stock", compact('products','store'));
        }
        return view('reports/shared/product/stock_index');
    }
    public function productStockMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();  
        $prodControl=new ProductsController;   
        $products=$prodControl->productByStore($storeid);
        return view("reports/shared/product/stock", compact('products','store'));
    }
    //---saldo stok
    public function productStockValueIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $prodControl=new ProductsController;   
            $products=$prodControl->productByStore($storeid);
            $tglreport=date("d-m-Y");
            return view("reports/shared/product/value_stock", compact('products','store','tglreport'));
        }
        return view('reports/shared/product/value_stock_index');
    }
    public function productStockValueMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first(); 
        $prodControl=new ProductsController;   
        $products=$prodControl->productByStore($storeid);
        $tglreport=date("d-m-Y");
        return view("reports/shared/product/value_stock", compact('products','store','tglreport'));
    }
    //---buffer stok
    public function productBufferStockIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $prodControl=new ProductsController;   
            $products=$prodControl->productByStore($storeid);
            $arrProduct=array();
            foreach ($products as $key => $product) {
                $product_stock=$product->stores[0]['product_stores']['product_stock'];
                $product_buffer_stock=$product->stores[0]['product_stores']['product_buffer_stock'];
                if($product_stock<=$product_buffer_stock){
                    $arrProduct[]=$product;
                }
            }
            $products=json_encode($arrProduct);  //karena kirim pake compact
            return view("reports/shared/buffer_stock", compact('products','store'));
        }
        return view('reports/shared/buffer_stock_index');
    }
    public function productBufferStockMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();  
        $prodControl=new ProductsController;   
        $products=$prodControl->productByStore($storeid);
        $arrProduct=array();
        foreach ($products as $key => $product) {
            $product_stock=$product->stores[0]['product_stores']['product_stock'];
            $product_buffer_stock=$product->stores[0]['product_stores']['product_buffer_stock'];
            if($product_stock<=$product_buffer_stock){
                $arrProduct[]=$product;
            }
        }
        $products=json_encode($arrProduct);  //karena kirim pake compact
        return view("reports/shared/buffer_stock", compact('products','store'));
    }   
    //---konsinyasi
    public function productConsignmentStockIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $prodControl=new ProductsController;   
            $products=$prodControl->productByStore($storeid);
            $arrProduct=array();
            foreach ($products as $key => $product) {
                $product_consignment_state=$product->stores[0]['product_stores']['product_consignment_state'];
                if($product_consignment_state=='1'){
                  $arrProduct[]=$product;
                }
            }
            $products=json_encode($arrProduct);  //karena kirim pake compact
            return view("reports/shared/consignment", compact('products','store'));
        }
        return view('reports/shared/consignment_index');
    } 
    public function productConsignmentStockMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();  
        $prodControl=new ProductsController;   
        $products=$prodControl->productByStore($storeid);
        $arrProduct=array();
        foreach ($products as $key => $product) {
            $product_consignment_state=$product->stores[0]['product_stores']['product_consignment_state'];
            if($product_consignment_state=='1'){
              $arrProduct[]=$product;
            }
        }
        $products=json_encode($arrProduct);  //karena kirim pake compact

        return view("reports/shared/buffer_stock", compact('products','store'));
    }   
    //------ laporan pembelian berdasarkan supplier
    public function purchaseSupplierIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];   
            $store=Stores::where('store_id',$storeid)->first(); 
            $tglAwal = date('Y-m-d')." 00:00:00"; 
            $tglAkhir = date('Y-m-d')." 23:59:59"; 
            $tglreport=substr($tglAwal,8,2).'-'.substr($tglAwal,5,2).'-'.substr($tglAwal,0,4)
                    .' s.d '.substr($tglAkhir,8,2).'-'.substr($tglAkhir,5,2).'-'.substr($tglAkhir,0,4);
            $receives=$this->purchaseSupplierData($storeid,$tglAwal,$tglAkhir);
        return view('reports/office/receive/supplier',compact('receives','store','tglreport'));
        }
        return view('reports/office/receive/supplier_index');
    }
//   public function purchaseSupplierMain(Request $request){
//     $storeid=$request->store_id;
//     $store=Stores::where('store_id',$storeid)->first(); 
//     $tglAwal = date('Y-m-d')." 00:00:00"; 
//     $tglAkhir = date('Y-m-d')." 23:59:59"; 
//     $tglreport=substr($tglAwal,8,2).'-'.substr($tglAwal,5,2).'-'.substr($tglAwal,0,4)
//                .' s.d '.substr($tglAkhir,8,2).'-'.substr($tglAkhir,5,2).'-'.substr($tglAkhir,0,4);
//     $receives=$this->purchaseSupplierData($storeid,$tglAwal,$tglAkhir);
//     return view('reports/office/receive/supplier',compact('receives','store','tglreport'));
//   }
//   public function purchaseSupplierOpen($storeid,$tglAwal,$tglAkhir){
//     $tglreport=substr($tglAwal,8,2).'-'.substr($tglAwal,5,2).'-'.substr($tglAwal,0,4)
//                 .' s.d '.substr($tglAkhir,8,2).'-'.substr($tglAkhir,5,2).'-'.substr($tglAkhir,0,4);
//     $tglAwal=date($tglAwal)." 00:00:00"; //ddmmyyyy
//     $tglAkhir=date($tglAkhir)." 23:59:59"; //ddmmyyyy
//     $store=Stores::where('store_id',$storeid)->first();   
//     $receives=$this->purchaseSupplierData($storeid,$tglAwal,$tglAkhir);
//     return view("reports/office/receive/supplier", compact('store','receives','tglreport'));
//   }
//   private function purchaseSupplierData($storeid,$tglAwal,$tglAkhir){
//     $receives=ReceivePurchases::with('suppliers','products')
//         ->where([['store_id',$storeid],['receive_state','1']])
//         ->whereBetween('receive_date',[$tglAwal,$tglAkhir])
//         ->orderby('receive_date','desc')->get();
//     return $receives;
//   }
    //-----laporan sales harian
    public function dailySalesIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first(); 
            $tgl=date("dmY"); //ddmmyyyy     
            $tglreport=date("d-m-Y");
            $sales = DB::table('sale_products')
                        ->join('sales','sale_products.sale_id','=','sales.id')
                        ->join('products','sale_products.product_id','=','products.id')
                        ->join('product_categories','product_categories.id','=','products.prodcat_id')
                        ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                        'prodcat_desc','products.id','products.product_name',
                        'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                        'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                        ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$tgl)
                        ->where('sales.store_id',$storeid)
                        ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                        ->orderby('sales.sale_no','asc')
                        ->get();

            return view("reports/office/dailysales", compact('store','sales','tglreport'));
        }
        return view('reports/office/dailysales_index');
    }
    public function dailySalesMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();  
        $tgl=date("dmY"); //ddmmyyyy     
        $tglreport=date("d-m-Y");
        $sales = DB::table('sale_products')
                 ->join('sales','sale_products.sale_id','=','sales.id')
                 ->join('products','sale_products.product_id','=','products.id')
                 ->join('product_categories','product_categories.id','=','products.prodcat_id')
                 ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                 'prodcat_desc','products.id','products.product_name',
                 'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                 'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                 ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$tgl)
                 ->where('sales.store_id',$storeid)
                 ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                 ->orderby('sales.sale_no','asc')
                 ->get();

        return view("reports/office/dailysales", compact('store','sales','tglreport'));
    }
    public function dailySalesStoreDate($storeid,$tgl){
        $store=Stores::where('id',$storeid)->first();  
        $tglreport=substr($tgl,0,2).'-'.substr($tgl,2,2).'-'.substr($tgl,4,4);
        if($tgl=='NOW'){
          $tgl=date("dmY"); //ddmmyyyy
          $tglreport=date("d-m-Y");
        }
        $sales = DB::table('sale_products')
                 ->join('sales','sale_products.sale_id','=','sales.id')
                 ->join('products','sale_products.product_id','=','products.id')
                 ->join('product_categories','product_categories.id','=','products.prodcat_id')
                 ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                 'prodcat_desc','products.id','products.product_name',
                 'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                 'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                 ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$tgl)
                 ->where('sales.store_id',$storeid)
                 ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                 ->orderby('sales.sale_no','asc')
                 ->get();

        return view("reports/office/dailysales", compact('store','sales','tglreport'));
    }
    //-----laporan sales periode
    public function periodeSalesIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first();  
            $tanggal=date("Y-m-d");
            $tglawal=$tanggal.' 00:00:00';
            $tglakhir=$tanggal.' 23:59:59';
            $sales = DB::table('sale_products')
                     ->join('sales','sale_products.sale_id','=','sales.id')
                     ->join('products','sale_products.product_id','=','products.id')
                     ->join('product_categories','product_categories.id','=','products.prodcat_id')
                     ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                     'prodcat_desc','products.id','products.product_name',
                     'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                     'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                     ->whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                     ->where('sales.store_id',$storeid)
                     ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                     ->orderby('sales.sale_no','asc')
                     ->get();
            $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
                .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
            return view("reports/office/periodesales", compact('store','sales','tglreport','tglawal','tglakhir'));
        }
        return view('reports/office/periodesales_index');
    }
    public function periodeSalesMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();  
        $tglawal=$request->get("tgl_awal").' 00:00:00';
        $tglakhir=$request->get("tgl_akhir").' 23:59:59';
        $sales = DB::table('sale_products')
                 ->join('sales','sale_products.sale_id','=','sales.id')
                 ->join('products','sale_products.product_id','=','products.id')
                 ->join('product_categories','product_categories.id','=','products.prodcat_id')
                 ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                 'prodcat_desc','products.id','products.product_name',
                 'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                 'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                 ->whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                 ->where('sales.store_id',$storeid)
                 ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                 ->orderby('sales.sale_no','asc')
                 ->get();
        $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
            .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
        return view("reports/office/periodesales", compact('store','sales','tglreport','tglawal','tglakhir'));
    }

    public function periodeSalesStoreDate($storeid,$tglawal,$tglakhir){
        $store=Stores::where('id',$storeid)->first();  
        $tglawal=$tglawal.' 00:00:00';
        $tglakhir=$tglakhir.' 23:59:59';
        $sales = DB::table('sale_products')
                 ->join('sales','sale_products.sale_id','=','sales.id')
                 ->join('products','sale_products.product_id','=','products.id')
                 ->join('product_categories','product_categories.id','=','products.prodcat_id')
                 ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                 'prodcat_desc','products.id','products.product_name',
                 'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                 'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                 ->whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                 ->where('sales.store_id',$storeid)
                 ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                 ->orderby('sales.sale_no','asc')
                 ->get();
        $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
            .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
        return view("reports/office/periodesales", compact('store','sales','tglreport','tglawal','tglakhir'));
    }
    //-----laporan sales konsinyasi
    public function consignmentSalesIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            return view("reports/office/consignment_supplier",compact('storeid'));
        }
        return view('reports/office/consignment_index');
    }
    public function consignmentSalesSupplier($storeid){
            // $productstore=DB::table('product_stores')->select('product_stores.product_id')
            //     ->where([['product_stores.product_consignment_state','1'],['product_stores.store_id',$storeid]])
            //     ->pluck('product_stores.product_id');
        return view('reports/office/consignment_supplier',compact('storeid'));    
    }
    public function consignmentSalesMain(Request $request){
        $storeid=$request->get('store_id');
        $tglawal=$request->get("tgl_awal").' 00:00:00';
        $tglakhir=$request->get("tgl_akhir").' 23:59:59';
        $supplierid=$request->get('supplier_id');
        $store=Stores::where('id',$storeid)->first();  
        $supplier=Suppliers::where('id',$supplierid)->first();
        $productstore=DB::table('product_suppliers')->select('product_suppliers.product_id')
            ->where('product_suppliers.supplier_id',$supplierid)
            ->pluck('product_suppliers.product_id');
        $sales = DB::table('sale_products')
                ->join('sales','sale_products.sale_id','=','sales.id')
                ->join('products','sale_products.product_id','=','products.id')
                ->join('product_categories','product_categories.id','=','products.prodcat_id')
                ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                'prodcat_desc','products.id','products.product_name',
                'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                ->whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                ->where('sales.store_id',$storeid)
                ->whereIn('products.id',$productstore)
                ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                ->orderby('sales.sale_no','asc')
                ->get();
        $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
            .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
        return view("reports/office/consignment", compact('store','supplier','sales','tglreport','tglawal','tglakhir'));
    }

    public function consignmentSalesStoreDate($storeid,$tglawal,$tglakhir){
        $store=Stores::where('id',$storeid)->first();  
        $tglawal=$tglawal.' 00:00:00';
        $tglakhir=$tglakhir.' 23:59:59';
        $sales = DB::table('sale_products')
                ->join('sales','sale_products.sale_id','=','sales.id')
                ->join('products','sale_products.product_id','=','products.id')
                ->join('product_categories','product_categories.id','=','products.prodcat_id')
                ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                'prodcat_desc','products.id','products.product_name',
                'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                ->whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                ->where('sales.store_id',$storeid)
                ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                ->orderby('sales.sale_no','asc')
                ->get();
        $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
            .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
        return view("reports/office/consignment", compact('store','sales','tglreport','tglawal','tglakhir'));
    }
    //-----laporan sales harian anggota
    public function periodeSalesCustomerIndex(){
        $storeControl=new StoresController();
        $countStore=$storeControl->storeByCompanyCount();
        if($countStore["count"]=='1'){
            $storeid=$countStore["storeid"];
            $store=Stores::where('id',$storeid)->first();  
            $tanggal=date("Y-m-d");
            $tglawal=$tanggal.' 00:00:00';
            $tglakhir=$tanggal.' 23:59:59';
            $sales=Sales::whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                ->where('sales.store_id',$storeid)
                ->where('sales.sale_state','!=','0')
                ->orderby('sales.sale_no','asc')
                ->get();
            $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
                .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
            return view("reports/office/sales_customer",compact('store','sales','tglreport','tglawal','tglakhir'));
        }
        return view('reports/office/sales_customer_index',compact('stores'));

    }

    public function periodeSalesCustomerMain(Request $request){
        $storeid=$request->get('store_id');
        $store=Stores::where('id',$storeid)->first();  
        $tglawal=$request->get("tgl_awal").' 00:00:00';
        $tglakhir=$request->get("tgl_akhir").' 23:59:59';
        $sales=Sales::whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                 ->where('sales.store_id',$storeid)
                 ->where('sales.sale_state','!=','0')
                 ->orderby('sales.sale_no','asc')
                 ->get();
        $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
            .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
        return view("reports/office/sales_customer",compact('store','sales','tglreport','tglawal','tglakhir'));
    }
  
    public function periodeSalesCustomerStoreDate($storeid,$tglawal,$tglakhir){
        $store=Stores::where('id',$storeid)->first();  
        $tglawal=$tglawal.' 00:00:00';
        $tglakhir=$tglakhir.' 23:59:59';
        $sales=Sales::whereBetween('sales.sale_date',[$tglawal,$tglakhir])
                 ->where('sales.store_id',$storeid)
                 ->where('sales.sale_state','!=','0')
                 ->orderby('sales.sale_no','asc')
                 ->get();
        $tglreport=substr($tglawal,8,2).'-'.substr($tglawal,5,2).'-'.substr($tglawal,0,4)
            .' s.d '.substr($tglakhir,8,2).'-'.substr($tglakhir,5,2).'-'.substr($tglakhir,0,4);
        return view("reports/office/sales_customer", compact('store','sales','tglreport','tglawal','tglakhir'));
    }
    #--------------------------------
    #-----------LAPORAN TOKO---------
    #--------------------------------
    //-----laporan stok
    public function storeProductStockMain(Request $request){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first();  
        $prodControl=new ProductsController;   
        $products=$prodControl->productByStore($storeid);
        return view("reports/shared/products_stock", compact('products','store'));
    }
    //-----laporan sales harian
    public function storeDailySalesMain(Request $request){
        $storeid=$this->storeid();
        $store=Stores::where('id',$storeid)->first();  
        $tgl=date("dmY"); //ddmmyyyy     
        $tglreport=date("d-m-Y");
        $sales = DB::table('sale_products')
                    ->join('sales','sale_products.sale_id','=','sales.id')
                    ->join('products','sale_products.product_id','=','products.id')
                    ->join('product_categories','product_categories.id','=','products.prodcat_id')
                    ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                    'prodcat_desc','products.id','products.product_name',
                    'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                    'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                    ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$tgl)
                    ->where('sales.store_id',$storeid)
                    ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                    ->orderby('sales.sale_no','asc')
                    ->get();

        return view("reports/store/dailysales", compact('store','sales','tglreport'));
    }
    public function storeDailySalesStoreDate($storeid,$tgl){
        $store=Stores::where('id',$storeid)->first();  
        $tglreport=substr($tgl,0,2).'-'.substr($tgl,2,2).'-'.substr($tgl,4,4);
        if($tgl=='NOW'){
            $tgl=date("dmY"); //ddmmyyyy
            $tglreport=date("d-m-Y");
        }
        $sales = DB::table('sale_products')
                    ->join('sales','sale_products.sale_id','=','sales.id')
                    ->join('products','sale_products.product_id','=','products.id')
                    ->join('product_categories','product_categories.id','=','products.prodcat_id')
                    ->select('sales.sale_no','sales.sale_date','sales.sale_tax','sales.sale_service_charge',
                    'prodcat_desc','products.id','products.product_name',
                    'products.product_plu','sale_products.sale_product_qty','sale_products.sale_product_disc',
                    'sale_products.sale_product_buy_price','sale_products.sale_product_price','sale_products.sale_product_total')
                    ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$tgl)
                    ->where('sales.store_id',$storeid)
                    ->where([['sales.sale_state','!=','0'],['products.product_state','!=','0']])
                    ->orderby('sales.sale_no','asc')
                    ->get();

        return view("reports/store/dailysales", compact('store','sales','tglreport'));
    }
}
