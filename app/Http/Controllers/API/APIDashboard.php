<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\Dashboard;
use App\Models\Companys;
use App\Models\Sales;
use App\Models\SalePayments;
use App\Models\Products;
use App\Models\ProductCategorys;
use App\Models\PendingSales;
use App\Models\Stores;
use App\Models\Members;
use App\Models\Spots;
use Session;
use DB;
use Carbon\Carbon;

class APIDashboard extends Controller
{
    private function today(){
        return date("d").date("m").date("Y");
    }
    private function firstdaylastyear(){
        return date("Y-m-d", strtotime("first day of january last year"));
    }
    private function samedaylastyear(){
        return date("Y-m-d", strtotime("last year"));
    }
    private function lastmonth(){
        return  date("my", strtotime("-1 month")); 
    }
    private function activemonth(){
        return  date("my");
    }
    private function lastyear(){
        return  date("y", strtotime("-1 year"));
    }
    private function activeyear(){
        return  date("y");
    }
    private function activeye4r(){
        return  date("Y");
    }
    private function bulannames(){
        return array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
        '05'=>'Mei', '06'=>'Juni', '07'=>'Juli','08'=>'Agustus', 
        '09'=>'September', '10'=>'Oktober', '11'=>'Nopember', 
        '12'=>'Desember');
    }
    private function lastmonthlysales($compid){
        $monthlysales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->lastmonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        return $monthlysales->saletot;
    }
    private function lastmonthlyprofit($compid){
        $lastmonthlyprofit=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM(((sale_product_price-sale_buy_price)*sale_products.sale_product_qty)-sale_product_disc) as salemargin'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->lastmonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        return $lastmonthlyprofit->salemargin;
    }
    private function monthlysales($compid){
        $monthlysales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->activemonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        return $monthlysales->saletot;
    }
    private function monthlyprofit($compid){
        $monthlyprofit=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM(((sale_product_price-sale_buy_price)*sale_products.sale_product_qty)-sale_product_disc) as salemargin'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->activemonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        return $monthlyprofit->salemargin;
    }
    private function monthlysalescount($stores){
        $monthsalesqty=Sales::whereMonth('sale_date',Carbon::now()->month)   
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        return $monthsalesqty;
    }
    private function lastmonthlysalescount($stores){
        $monthsalesqty=Sales::whereMonth('sale_date',Carbon::now()->month-1)   
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        return $monthsalesqty;
    }
    private function lastyearlysales($compid){
        $lastyearlysales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'))
            // ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%Y-%m-%d")'),'>=',$this->firstdaylastyear())
            // ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%Y-%m-%d")'),'<',$this->samedaylastyear())
            ->whereBetween('sales.sale_date',[$this->firstdaylastyear(), $this->samedaylastyear()])
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        if($lastyearlysales==null){
            return 0;
        }
        return $lastyearlysales->saletot;
    }
    private function lastyearlyprofit($compid){
        $lastyearlyprofit=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM(((sale_product_price-sale_buy_price)*sale_products.sale_product_qty)-sale_product_disc) as salemargin'))
            ->whereBetween('sales.sale_date',[$this->firstdaylastyear(), $this->samedaylastyear()])
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        if($lastyearlyprofit==null){
            return 0;
        }
        return $lastyearlyprofit->salemargin;
    }
    private function yearlysales($compid){
        $yearlysales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%y")'),'=',$this->activeyear())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        return $yearlysales->saletot;
    }
    private function yearlyprofit($compid){
        $yearlyprofit=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('SUM(((sale_product_price-sale_buy_price)*sale_products.sale_product_qty)-sale_product_disc) as salemargin'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%y")'),'=',$this->activeyear())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->first();
        return $yearlyprofit->salemargin;
    }
    private function yearlysalescount($compid){
        $stores=function ($query) use ($compid){
            $query->where('comp_id',$compid);
        };
        $yearsalesqty=Sales::whereYear('sale_date',Carbon::now()->year)   
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        return $yearsalesqty;
    }
    private function lastyearlysalescount($compid){
        $stores=function ($query) use ($compid){
            $query->where('comp_id',$compid);
        };
        $yearsalesqty=Sales::whereBetween('sales.sale_date',[$this->firstdaylastyear(), $this->samedaylastyear()])  
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        return $yearsalesqty;
    }
    public function amDashboardManager($compid)
    {
        $bulan=$this->bulannames()[substr($this->activemonth(),0,2)];
        $bulantahun=$bulan." ".$this->activeye4r();
        $now = Carbon::now();
        $activeyear=$this->activeye4r();
        $compname=Companys::where('comp_id',$compid)->first()->comp_brand;
        //-------------harian
        $stores=function ($query) use ($compid){
            $query->where('comp_id',$compid);
        };
        $products=function ($query) use ($stores){
            $query->whereHas('stores',$stores);
        };
        $todaysalesval=Sales::whereDate('sale_date',Carbon::today())
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->sum('sale_total');
        $todaydiscval=Sales::whereDate('sale_date',Carbon::today())
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->sum('sale_disc');
        $todaysalesval=$todaysalesval-$todaydiscval;
        $todaysalesqty=Sales::whereDate('sale_date',Carbon::today())
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        $todaysales=Sales::with(['products'=>$products])->whereDate('sale_date',Carbon::today())
                ->whereHas('products',$products)->where('sale_state','!=',0)->get();
        if($todaysales){
            $todayprofit=0;
            foreach ($todaysales as $key => $sale) {
                foreach ($sale->products as $prod) {
                    $prodbuyprice=$prod['saleproducts']['sale_buy_price'];
                    $prodprice=$prod['saleproducts']['sale_product_price'];
                    $proddisc=$prod['saleproducts']['sale_product_disc'];
                    $prodsaleqty=$prod['saleproducts']['sale_product_qty'];
                    $todayprofit=$todayprofit+(($prodprice-$prodbuyprice)*$prodsaleqty)-$proddisc;
                }
            }
        }
        //-----------chart
        $yearlysales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('DATE_FORMAT(sales.sale_date,"%m") as salemonth'),
            DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'),
            DB::raw('SUM(((sale_product_price-sale_buy_price)*sale_products.sale_product_qty)-sale_product_disc) as salemargin'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%y")'),'=',$this->activeyear())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->groupby('salemonth')
            ->get();


        $dashboard=new Dashboard;
        if(!$todaysales){
            $dashboard->dash_profit=0;
            $dashboard->dash_cost=0;
            $dashboard->dash_revenue=0;
            $dashboard->dash_profit_month=0;
            $dashboard->dash_cost_month=0;
            $dashboard->dash_revenue_month=0;
            $dashboard->dash_profit_last=0;
            $dashboard->dash_cost_last=0;
            $dashboard->dash_revenue_last=0;
            $dashboard->dash_profit_last=0;
            $dashboard->dash_cost_last=0; 
        }else{
            $dashboard->dash_company_name=$compname;
            $dashboard->dash_active_year=$activeyear;
            $dashboard->dash_revenue=$todaysalesval;
            $dashboard->dash_profit=$todayprofit;
            $dashboard->dash_cost=$todaysalesval-$todayprofit;
            $dashboard->dash_cust=$todaysalesqty;
            // $dashboard->dash_revenue_month=$this->yearlysales($compid); 
            // $dashboard->dash_profit_month=$this->yearlyprofit($compid);
            // $dashboard->dash_cost_month=$this->yearlysales($compid)-$this->yearlyprofit($compid);
            // $dashboard->dash_cust_month=$this->yearlysalescount($compid);
            // $dashboard->dash_revenue_last=$this->lastyearlysales($compid);
            // $dashboard->dash_profit_last=$this->lastyearlyprofit($compid);
            // $dashboard->dash_cost_last=$this->lastyearlysales($compid)-$this->lastyearlyprofit($compid);
            // $dashboard->dash_cust_last=$this->lastyearlysalescount($compid);
            $dashboard->dash_sales_value=$todaysalesval;
            $dashboard->dash_sales_qty=$todaysalesqty;
            $dashboard->dash_sales_margin=$todayprofit;
            if($todaysalesval!=0){
                $dashboard->dash_sales_value_margin=($todayprofit/$todaysalesval)*100;
            }
            $dashboard->dash_sales_chart_yearly=$yearlysales; 
        }
       
        return response()->json($dashboard); 
    }
    public function dashboardCard(){
        $dashboard=new Dashboard;
        $stores=function ($query){
            $query->where('comp_id',$compid);
        };
        $products=function ($query) use ($stores){
            $query->whereHas('stores',$stores);
        };
        $todaysalesval=Sales::whereDate('sale_date',Carbon::today())
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->sum('sale_total');
        $todaysalesqty=Sales::whereDate('sale_date',Carbon::today())
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        $sales=Sales::with(['products'=>$products])->whereDate('sale_date',Carbon::today())
                ->whereHas('products',$products)->where('sale_state','!=',0)->get();
        $netsales=0;

        foreach ($sales as $key => $sale) {
            $prodbuyprice=$sale->products['0']['product_buy_price'];
            $prodprice=$sale->products['0']['saleproducts']['sale_product_price'];
            $proddisc=$sale->products['0']['saleproducts']['product_disc'];
            $prodsaleqty=$sale->products['0']['saleproducts']['sale_product_qty'];
            $netsales=$netsales+((($prodprice-$prodbuyprice))*$prodsaleqty)-$proddisc;
        }

        $dashboard->dash_sales_value=$todaysalesval;
        $dashboard->dash_sales_qty=$todaysalesqty;
        $dashboard->dash_sales_margin=$todaysalesval-$netsales;
        return(compact('dashboard'));
    }

    public function products(){
        $bulan=$this->bulannames()[substr($this->activemonth(),0,2)];
        $bulantahun=$bulan." ".$this->activeye4r();

        $prodvaltopten=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->join('products','sale_products.product_id','=','products.product_id')
            ->select('products.product_shortdesc as prodname',
                DB::raw('SUM(sale_product_qty) as qtytot'),
                DB::raw('SUM(sale_product_price) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->activemonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->groupby('products.product_id','products.product_shortdesc')
            ->orderby('qtytot','desc')
            ->take(10)
            ->get();

        $prodpricetopten=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->join('products','sale_products.product_id','=','products.product_id')
            ->select('products.product_shortdesc as prodname',
                DB::raw('SUM(sale_product_qty) as qtytot'),
                DB::raw('SUM(sale_product_price) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->activemonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->groupby('products.product_id','products.product_shortdesc')
            ->orderby('saletot','desc')
            ->take(10)
            ->get();

        $prodcategory=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->join('products','sale_products.product_id','=','products.product_id')
            ->join('product_categorys','product_categorys.prodcat_id','=','products.prodcat_id')
            ->select('product_categorys.prodcat_desc as prodcat',
                DB::raw('SUM(sale_product_qty) as qtytot'),
                DB::raw('SUM(sale_product_price) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->activemonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->groupby('product_categorys.prodcat_id','product_categorys.prodcat_desc')
            ->orderby('saletot','desc')
            ->take(5)
            ->get();
        
        return view('home/dashboard/dashboardproducts',compact("prodvaltopten","prodpricetopten","prodcategory","bulantahun"));
    }    
    public function stores(){
        $bulan=$this->bulannames()[substr($this->activemonth(),0,2)];
        $bulantahun=$bulan." ".$this->activeye4r();
        
        $storesales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->join('stores','sales.store_id','=','stores.store_id')
            ->select('stores.store_name as storename',
                DB::raw('SUM(sale_product_qty) as qtytot'),
                DB::raw('SUM(sale_product_price) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->activemonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->groupby('sales.store_id','stores.store_name')
            ->orderby('saletot','desc')
            ->take(5)
            ->get();    

        $storetimesales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->join('stores','sales.store_id','=','stores.store_id')
            ->select('stores.store_name as storename', 
                DB::raw('DATE_FORMAT(sales.sale_date,"%H") as saletime'),
                DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$this->today())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->groupby('stores.store_name','saletime')
            ->get();

        return view('home/dashboard/dashboardstores',compact("storesales","storetimesales","bulantahun"));
    }
    public function dashboardStoreIndex(){
        return view('home/dashboard/storeindex');
    }

    public function dashboardStoreSales(){
        $bulan=$this->bulannames()[substr($this->activemonth(),0,2)];
        $bulantahun=$bulan." ".$this->activeye4r();

        $dailytimesales=DB::table('sale_products')
                ->join('sales','sale_products.sale_no','=','sales.sale_no')
                ->select(DB::raw('DATE_FORMAT(sales.sale_date,"%H") as saletime'),
                DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'))
                ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%d%m%Y")'),'=',$this->today())
                ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
                ->where('sales.sale_state','!=','0')
                ->groupby('saletime')
                ->get();

        $monthlysales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('DATE_FORMAT(sales.sale_date,"%d") as saleday'),
            DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'),
            DB::raw('SUM(((sale_product_price-sale_buy_price)*sale_products.sale_product_qty)-sale_product_disc) as salemargin'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$this->activemonth())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where('sales.sale_state','!=','0')
            ->groupby('saleday')
            ->get();

        //-------------bulanan
        $stores=function ($query){
            $query->where('comp_id',$compid);
        };
        $products=function ($query) use ($stores){
            $query->whereHas('stores',$stores);
        };

 
        $dashboard=new Dashboard;        
        $dashboard->dash_revenue_month=$this->monthlysales($stores); 
        $dashboard->dash_profit_month=$this->monthlyprofit($stores);
        $dashboard->dash_cost_month=$this->monthlysales($stores)-$this->monthlyprofit($stores);
        $dashboard->dash_cust_month=$this->monthlysalescount($stores);
        $dashboard->dash_revenue_last=$this->lastmonthlysales($stores);
        $dashboard->dash_profit_last=$this->lastmonthlyprofit($stores);
        $dashboard->dash_cost_last=$this->lastmonthlysales($stores)-$this->lastmonthlyprofit($stores);
        $dashboard->dash_cust_last=$this->lastmonthlysalescount($stores);


        return view('home/dashboard/dashboardsales',compact("dailytimesales","monthlysales","bulantahun","dashboard"));
    }
    public function dashboardStoreMain(Request $request){
        $selectedstores=explode(",",$request->get('store_id'));
        $store=Stores::whereIn('store_id',$selectedstores)->first(); 
        $storeid=$store->store_id;


        //sales live
        $pendingsales=DB::table('pending_sale_products')->join('pending_sales','pending_sale_products.pending_no','=','pending_sales.pending_no')
            ->select(DB::raw('SUM((pending_sale_product_price*pending_sale_product_qty)-pending_sale_product_disc) as pendingtot'))
            ->where('pending_sales.store_id',$storeid)
            ->whereDate('pending_date',Carbon::today())->first();
        $arrpendingsales=(array)$pendingsales;
        $pendingqty=PendingSales::whereDate('pending_date',Carbon::today())->where('store_id',$storeid)->count('pending_no');
        $spotqty=Spots::where('store_id',$storeid)->count();
        $spotavail=Spots::where([['store_id',$storeid],['spot_state','1']])->count();
        $todaysalesval=Sales::whereDate('sale_date',Carbon::today())
            ->where([['store_id',$storeid],['sale_state','!=',0]])->sum('sale_total');


        // $arrpendingsales=(array)$pendingsales;
        // var_dump($arrpendingsales["pendingtot"]);
        // dd($pendingsales);

        //sales bulanan
        $blnthn = date("m").date("y");
        $bulan = array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
                       '05'=>'Mei', '06'=>'Juni', '07'=>'Juli','08'=>'Agustus', 
                       '09'=>'September', '10'=>'Oktober', '11'=>'Nopember', 
                       '12'=>'Desember');
        $bulan=$bulan[substr($blnthn,0,2)];
        $tahun=substr($blnthn,2,4);
        $bulantahun=$bulan." ".$tahun;


        $today=date("d").date("m").date("Y");
        $tahun=date("Y");
        $bulantahun=$bulan." ".$tahun;

        $prodvaltopten=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->join('products','sale_products.product_id','=','products.product_id')
            ->select('products.product_shortdesc as prodname',
                DB::raw('SUM(sale_product_qty) as qtytot'),
                DB::raw('SUM(sale_product_price) as saletot'))
            ->whereDate('sale_date',Carbon::today())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->groupby('products.product_id','products.product_shortdesc')
            ->orderby('qtytot','desc')
            ->take(10)
            ->get();


        $prodpricetopten=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->join('products','sale_products.product_id','=','products.product_id')
            ->select('products.product_shortdesc as prodname',
                DB::raw('SUM(sale_product_qty) as qtytot'),
                DB::raw('SUM(sale_product_price) as saletot'))
            ->whereDate('sale_date',Carbon::today())
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->groupby('products.product_id','products.product_shortdesc')
            ->orderby('saletot','desc')
            ->take(10)
            ->get();


        $monthlycartsales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('DATE_FORMAT(sales.sale_date,"%d") as saleday'),
            DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as saletot'),
            DB::raw('SUM(((sale_product_price-sale_buy_price)*sale_products.sale_product_qty)-sale_product_disc) as salemargin'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$compid)
            ->where([['sales.sale_state','!=','0'],['sales.store_id',$storeid]])
            ->groupby('saleday')
            ->get();

        $dashboard=new Dashboard;
        $stores=function ($query) use ($storeid){
            $query->where('stores.store_id',$storeid);
        };
        $products=function ($query) use ($stores){
            $query->whereHas('stores',$stores);
        };
        $monthrev=Sales::where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->sum('sale_total');    
        $monthsalesqty=Sales::where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        $sales=Sales::with(['products'=>$products])->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
                ->whereHas('products',$products)->where('sale_state','!=',0)->get();
        
        if(!$sales){
            $dashboard->dash_revenue_month=0;
            $dashboard->dash_profit_month=0;
            $dashboard->dash_cost_month=0;
            $dashboard->dash_sales_value_margin=0;
            $dashboard->dash_pending_sales=0;
            $dashboard->dash_pending_qty=0;
        }else{
            $monthprofit=0;
            foreach ($sales as $key => $sale) {
                foreach ($sale->products as $prod) {
                    $prodbuyprice=$prod['saleproducts']['sale_buy_price'];
                    $prodprice=$prod['saleproducts']['sale_product_price'];
                    $proddisc=$prod['saleproducts']['sale_product_disc'];
                    $prodsaleqty=$prod['saleproducts']['sale_product_qty'];
                    $monthprofit=$monthprofit+(($prodprice-$prodbuyprice)*$prodsaleqty)-$proddisc;
                }
                
            }
            // dd($prodbuyprice);
            $dashboard->dash_revenue_month=$monthrev;
            $dashboard->dash_profit_month=$monthprofit;
            $dashboard->dash_cost_month=$monthrev-$monthprofit;
            $dashboard->dash_cust_month=$monthsalesqty;
            $dashboard->dash_pending_sales=$arrpendingsales["pendingtot"];
            $dashboard->dash_pending_qty=$pendingqty;
            $dashboard->dash_spot_avail=$spotavail;
            $dashboard->dash_spot_total=$spotqty;
            $dashboard->dash_sales_value=$todaysalesval+$arrpendingsales["pendingtot"];
            $dashboard->dash_chart_prodvaltopten=$prodvaltopten;
            $dashboard->dash_chart_prodpricetopten=$prodpricetopten;
            
            if($monthrev!=0){
                $dashboard->dash_sales_value_margin=($monthprofit/$monthrev)*100;
            }
            $dashboard->dash_sales_cart_monthly=$monthlycartsales;
            
        }

        return view('home/dashboard/storemain',compact('store',"dashboard","bulantahun"));
    }
    
}
