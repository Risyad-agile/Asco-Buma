<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Models\Stores;
use App\Models\Sales;
use App\Models\Dashboard; 
use Carbon\Carbon;
use Session;
use DB;
use Entrust;

class DashboardHoldingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function userid(){
        return Auth::user()->id;
    }
    public function holdingDashboardIndex(){
        $userid=$this->userid();

        $user=function ($query) use ($userid){
            $query->where('id',$userid);
        };
        $stores=Stores::whereHas('users',$user)->get();
        $storelist=array();
        foreach ($stores as $store ) {
            $storeid=$store->store_id;
            $storelist[]= $storeid;
        }

        // dd($storelist);

        $now = Carbon::now();

        //hitung total order
        $bln=$now->month;
        $cryear=$now->year;

        $from = date("Y-m-d", strtotime("first day of january this year"));
        $to = $now;


        $sales=DB::table('sales')
        ->join('stores','stores.store_id','sales.store_id')
        ->join('companys','companys.comp_id','stores.comp_id')
        ->select('sale_no','companys.comp_brand','stores.store_name','sale_total','sale_date')
        // ->where([['stores.store_default','0'],['sales.sale_state','1']])
        ->whereIn('stores.store_id',$storelist)
        ->whereBetween('sales.sale_date', [$from, $to]) 
        ->get();

        // dd($sales);

        return view('home/holding/hd_dashboardindex',compact('sales','cryear'));
    }
}
