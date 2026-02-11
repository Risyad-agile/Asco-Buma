<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stores;
use App\Models\Sales;
use DB;
class APIReports extends Controller
{
    public function getPLGross($compid, $blnthn){
       
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
        $plgross=DB::table('sale_products')
                 ->join('sales','sale_products.sale_no','=','sales.sale_no') 
                 ->join('products','sale_products.product_id','=','products.product_id')
                 ->select(DB::raw('DATE_FORMAT(sales.sale_date,"%m%y") as tglsales'),
                 DB::raw('SUM(product_buy_price*sale_product_qty) as tot_product_buy_price'),
                 DB::raw('SUM(sale_product_price*sale_product_qty) as tot_product_sales_price'),
                 DB::raw('SUM(sale_product_disc) as tot_product_disc'),
                 DB::raw('SUM((sale_product_price*sale_product_qty)-sale_product_disc) as tot_product_sales_disc'),
                 DB::raw('SUM(((sale_product_price-product_buy_price)*sale_product_qty)-sale_product_disc) as total_pl'))
                 ->where([[DB::raw('DATE_FORMAT(sales.sale_date,"%m%y")'),'=',$blnthn],
                  [DB::raw('LEFT(sales.store_id,4)'),'=',$compid],['sales.sale_state','!=','0']])
                 ->groupby('tglsales')
                 ->first();
        return response()->json($plgross); 
    }

    public function getChartSalesWeekly($compid){
      $startdate=date("dmY"); 
      $stores=function ($query) use ($compid){
        $query->where('comp_id',$compid);
      };
      // Note: 1=Sunday, 2=Monday, 3=Tuesday, 4=Wednesday, 5=Thursday, 6=Friday, 7=Saturday.
      // $sales=Sales::whereHas('stores',$stores)
      //        ->where('sale_date','>=',DB::raw('DATE_SUB(date("2020-01-08"),INTERVAL 7 DAY)'))
      //        ->groupby(DB::raw('DAYOFWEEK(sale_date)'))
      //        ->get('sale_total');
      // $sales=Sales::whereHas('stores',$stores)
      //        ->where('sale_date','>=',DB::raw('DATE_SUB(date("2020-01-08"),INTERVAL 7 DAY)'))
      //        ->orderby(DB::raw('DAYOFWEEK(sale_date)'),'asc')
      //        ->get([DB::raw('DAYOFWEEK(sale_date) as weekday'),'sale_total'])
      //        ->groupby('weekday');
      // $sales=Sales::groupBy(DB::raw('DAYOFWEEK(sale_date)'))->selectRaw('DAYOFWEEK(sale_date) as weekday, sum(sale_total) as saletotal')
      //        ->where('sale_date','>=',DB::raw('DATE_SUB(CURDATE(),INTERVAL 7 DAY)'))
      //        ->whereHas('stores',$stores)->orderby(DB::raw('DAYOFWEEK(sale_date)'),'asc')
      //        ->get();

      // Note: 0 = Monday, 1 = Tuesday, 2 = Wednesday, 3 = Thursday, 4 = Friday, 5 = Saturday, 6 = Sunday.
      $sales=Sales::groupBy(DB::raw('WEEKDAY(sale_date)'))->selectRaw('WEEKDAY(sale_date) as weekday, sum(sale_total) as saletotal')
             ->where('sale_date','>=',DB::raw('DATE_SUB(CURDATE(),INTERVAL 7 DAY)'))
             ->whereHas('stores',$stores)->orderby(DB::raw('WEEKDAY(sale_date)'),'asc')
             ->get();
      

      return response()->json($sales); 
    }

    #-----------------WEBPOS----------------

}


