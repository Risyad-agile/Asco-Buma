<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\User;
use App\Models\Stores;
use App\Models\Sales;
use App\Models\Dashboard;
use App\Services\PromoServices;
use Carbon\Carbon;
use Session;
use DB; 

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function compid(){
        return Auth::user()->company->id;
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();

        // (Opsional) Cek status approve kalau diperlukan
        // if ($user->status != 1) {
        //     Auth::logout();
        //     return redirect('/login')->withErrors(['Akun Anda belum disetujui.']);
        // }

        // Ambil nama role pertama user
        $role = $user->getRoleNames()->first();
        // dd($role);
        // Peta role ke redirect/halaman masing-masing
        $redirectMap = [
            'superadmin'      => fn() => redirect()->action([HomeController::class,'saDashboardIndex']),
            'agile-manager'   => fn() => redirect()->action([DashboardController::class, 'index']),
            'agile-helpdesk'  => fn() => view('home/company/home'),
            'agile-finance'   => fn() => redirect()->action([DashboardCompanyController::class, 'dashboardIndex']),
            'agile-admin'     => fn() => view('home/company/home'),
            'manager'         => fn() => redirect()->action([DashboardCompanyController::class, 'dashboardIndex']),
            'supervisor'      => fn() => view('home/company/supervisor/landing'),
            'user'            => fn() => view('home/company/user/landing'),
            'admin'           => fn() => view('home'),
            'csr-admin'       => fn() => redirect()->route('create.task.crud.newCSR'), // Custom CSR route
        ];

        // Eksekusi redirect sesuai role
        if (isset($redirectMap[$role])) {
            return $redirectMap[$role]();
        }

        // Default fallback: logout user & tampilkan pesan
        Auth::logout();
        return redirect('/login')->withErrors(['Akses tidak diizinkan untuk role ini.']);
    }

    
    // public function index()
    // {
    //     #untuk login ke dashbaord status approve harus 1
    //     $id=Auth::user()->id;
    //     $user = User::where('id',$id)->first();
    //     //dd($user);
    //     $role=$user->getRoleNames();        
    //     dd($role);
    //     if($role[0]=='superadmin'){
    //         // return view('home');
    //         return redirect()->action([HomeController::class,'saDashboardIndex']);
    //     }
    //     if($role[0]=='agile-manager'){
    //         return redirect()->action([DashboardController::class,'index']);
    //     }
    //     if($role[0]=='agile-helpdesk'){
    //         return view('home/company/home');
    //     }
    //     if($role[0]=='agile-finance'){
    //         return redirect()->action([DashboardCompanyController::class,'dashboardIndex']);
    //     }
    //     if($role[0]=='agile-admin'){
    //         return view('home/company/home');
    //     }
    //     if($role[0]=="manager"){
    //         // return view('home');
    //        return redirect()->action([DashboardCompanyController::class,'dashboardIndex']);
    //     }
    //     if($role[0]=="supervisor"){
    //         return view('home/company/supervisor/landing');
    //     }
    //     if($role[0]=="user"){
    //         return view('home/company/user/landing');
    //     }
    //     if($role[0]=="admin"){
    //         // return view('home/company/home');
    //         return view('home');
    //     }
    //     return view('home');

        
    //     /*
    //     if($role->isEmpty()){
    //         return view('home');
    //         echo "User tidak memiliki role ";
    //         if(!empty($role)){
    //         return view('home');
    //     }
    //     */
    // }

    public function saDashboardIndex(){
        $now = Carbon::now();

        //hitung total order
        $bln=$now->month;
        $cryear=$now->year;

        $from = date("Y-m-d", strtotime("first day of january this year"));
        $to = $now;


        // $sales=DB::table('sales')
        // ->join('stores','stores.id','sales.store_id')
        // ->join('companies','companies.id','stores.comp_id')
        // ->select('sale_no','companies.comp_brand','stores.store_name','sale_total','sale_date')
        // ->where([['stores.store_type','!=','0'],['sales.sale_state','1']])
        // ->whereBetween('sales.sale_date', [$from, $to]) 
        // ->get();

        // return view('home/superadmin/sa_dashboardindex',compact('sales','cryear'));
        return view('home/superadmin/sa_dashboardindex');
    }
    public function saDashboardMerchantSales(){
        $blnthn = date("m").date("y");
        $bulan = array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
                       '05'=>'Mei', '06'=>'Juni', '07'=>'Juli','08'=>'Agustus', 
                       '09'=>'September', '10'=>'Oktober', '11'=>'Nopember', 
                       '12'=>'Desember');
        $bulan=$bulan[substr($blnthn,0,2)];
        $tahun=substr($blnthn,2,4);
        $bulantahun=$bulan." ".$tahun;
        $now = Carbon::now();
        $monthlysales=DB::table('sale_products')
            ->join('sales','sale_products.sale_no','=','sales.sale_no')
            ->select(DB::raw('DATE_FORMAT(sales.sale_date,"%d") as saleday'),
            DB::raw('SUM((sale_product_price-sale_product_disc)*sale_product_qty) as saletot'),
            DB::raw('SUM(((sale_product_price-sale_buy_price)-sale_product_disc)*sale_products.sale_product_qty) as salemargin'))
            ->where(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn)
            ->where(DB::raw('LEFT(sales.store_id,4)'),'=',$this->compid())
            ->where('sales.sale_state','!=','0')
            ->groupby('saleday')
            ->get();

        //-------------harian
        $stores=function ($query){
            $query->where('store_type','!=','0');
        };
        $products=function ($query) use ($stores){
            $query->whereHas('stores',$stores);
        };
        $todaysalesval=Sales::whereDate('sale_date',Carbon::today())
                ->whereHas('stores',$stores)->where('sale_state','!=',0)->sum('sale_total');
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
                    $todayprofit=$todayprofit+(($prodprice-$prodbuyprice-$proddisc)*$prodsaleqty);
                }
            }
        }

        //-------------bulanan
        $monthrev=Sales::whereMonth('sale_date',$now->month) //->whereYear('sale_date',$now->year)   
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->sum('sale_total');
        $monthsalesqty=Sales::whereMonth('sale_date',$now->month) //->whereYear('sale_date',$now->year)   
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        $monthsales=Sales::with(['products'=>$products])->whereMonth('sale_date',$now->month) //->whereYear('sale_date',$now->year)   
            ->whereHas('products',$products)->where('sale_state','!=',0)->get();
        if($monthlysales){
            $monthprofit=0;
            foreach ($monthsales as $key => $sale) {
                foreach ($sale->products as $prod) {
                    $prodbuyprice=$prod['saleproducts']['sale_buy_price'];
                    $prodprice=$prod['saleproducts']['sale_product_price'];
                    $proddisc=$prod['saleproducts']['sale_product_disc'];
                    $prodsaleqty=$prod['saleproducts']['sale_product_qty'];
                    $monthprofit=$monthprofit+(($prodprice-$prodbuyprice-$proddisc)*$prodsaleqty);
                }
            }
        }

        //-----------bulan lalu
        //ambil periode tanggal yang sama bulan lalu
        $from = date("Y-m-d", strtotime("first day of previous month"));
        $to = date("Y-m-d", strtotime("last month"));
        $lastmonthrev=Sales::whereBetween('sale_date', [$from, $to]) 
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->sum('sale_total');
        $lastmonthsalesqty=Sales::whereBetween('sale_date', [$from, $to])  
            ->whereHas('stores',$stores)->where('sale_state','!=',0)->count('sale_no');
        $lastmonthsales=Sales::with(['products'=>$products])->whereBetween('sale_date', [$from, $to])  
            ->whereHas('products',$products)->where('sale_state','!=',0)->get();
        if($lastmonthsales){
            $lastmonthprofit=0;
            foreach ($lastmonthsales as $key => $sale) {
                foreach ($sale->products as $prod) {
                    $prodbuyprice=$prod['saleproducts']['sale_buy_price'];
                    $prodprice=$prod['saleproducts']['sale_product_price'];
                    $proddisc=$prod['saleproducts']['sale_product_disc'];
                    $prodsaleqty=$prod['saleproducts']['sale_product_qty'];
                    $lastmonthprofit=$lastmonthprofit+(($prodprice-$prodbuyprice-$proddisc)*$prodsaleqty);
                }
            }
        }

        //jumlah merchant aktif dan tidak aktif
        $from = date("Y-m-d", strtotime("-7 day"));
        $to = $now;
        $dash_sales_all=DB::table('sales')
        ->join('stores','stores.id','sales.store_id')
        ->join('companies','companies.id','stores.comp_id')
        ->select('companies.comp_brand','stores.store_name',DB::raw('SUM(sales.sale_total) as sale_total'),
            DB::raw('DATE_FORMAT(sales.sale_date,"%d-%m-%y") as sale_date'),DB::raw('1 as sales_count'))
        ->where([['stores.store_type','!=','0'],['sales.sale_state','1']])
        ->whereBetween('sales.sale_date', [$from, $to]) 
        ->groupby('companies.comp_brand','stores.store_name',DB::raw('DATE_FORMAT(sales.sale_date,"%d-%m-%y")'))
        ->get();

        $sales_all=DB::table('sales')
        ->join('stores','stores.id','sales.store_id')
        ->join('companies','companies.id','stores.comp_id')
        ->select('companies.comp_brand','stores.store_name',DB::raw('1 as sale_count'))
        ->where([['stores.store_type','!=','0'],['sales.sale_state','1']])
        ->whereBetween('sales.sale_date', [$from, $to]) 
        ->groupby('companies.comp_brand','stores.store_name',DB::raw('DATE_FORMAT(sales.sale_date,"%d-%m-%y")'))
        ->get();

        //jumlah toko
        $totalstore=Stores::where([['store_type','!=','0'],['store_state','1']])->count();

        

        //ambil jumlah toko yang aktif dalam satu minggu
        $storeactivelist=array();
        $storename="";
        $storeactive=0;
        foreach ($sales_all as $key => $sale) {
            if($storename!=$sale->store_name){
                $storename=$sale->store_name;
                $storeactivelist[]=$sale->store_name;   
                $storeactive++;           
            }
        }

        //toko tidak aktif dalam seminggu
        $salescount=array();
        for ($i=0; $i <count($storeactivelist) ; $i++) { 
            $jml=0;
            $compbrand="";
            $storename="";
            foreach ($sales_all as $key => $sale) {
                if($storeactivelist[$i]==$sale->store_name){
                    $jml++;
                    $compbrand=$sale->comp_brand;
                    $storename=$sale->store_name;
                }
            }
            $salescount[]=$jml;  
        }

        $storeactive=0;
        for ($i=0; $i <count($salescount) ; $i++) { 
            if($salescount[$i]>=3){
                $storeactive++;
            }
        }

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
            $dashboard->dash_revenue=$todaysalesval;
            $dashboard->dash_profit=$todayprofit;
            $dashboard->dash_cost=$todaysalesval-$todayprofit;
            $dashboard->dash_cust=$todaysalesqty;
            $dashboard->dash_revenue_month=$monthrev; 
            $dashboard->dash_profit_month=$monthprofit;
            $dashboard->dash_cost_month=$monthrev-$monthprofit;
            $dashboard->dash_cust_month=$monthsalesqty;
            $dashboard->dash_revenue_last=$lastmonthrev;
            $dashboard->dash_profit_last=$lastmonthprofit;
            $dashboard->dash_cost_last=$lastmonthrev-$lastmonthprofit;
            $dashboard->dash_cust_last=$lastmonthsalesqty;
            $dashboard->dash_sales_value=$todaysalesval;
            $dashboard->dash_sales_qty=$todaysalesqty;
            $dashboard->dash_sales_margin=$todayprofit;
            if($todaysalesval!=0){
                $dashboard->dash_sales_value_margin=($todayprofit/$todaysalesval)*100;
            }
            $dashboard->dash_sales_cart_monthly=$monthlysales;
            $dashboard->dash_sales_all=$dash_sales_all;
            $dashboard->dash_total_store=$totalstore;
            $dashboard->dash_not_active_store=$totalstore-$storeactive;
        }

        return view('home/superadmin/sa_merchantsales',compact("dashboard","bulantahun"));
    }
}
