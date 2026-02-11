<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AccountStyleCompanies;
use App\Models\Tasks;
use App\Models\Dashboard;
use App\Models\ProcessDataLogs; 
use Carbon\Carbon; 
use App\Models\Location;
use App\Models\Organization;
use App\Models\Companies;
use DB;

class DashboardCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function username(){
        return Auth::user()->username;
    }
    public function compid(){
        return Auth::user()->comp_id;
    }
    public function orgname(){
        return Auth::user()->organization->org_name;
    }
    private function accountstyletotal(){
        return AccountStyleCompanies::where('comp_id',$this->compid())->count();
    }
    private function locationtotal(){
        return Location::where('org_name',$this->compid())->count();
        // return Locations::where('comp_id',$this->compid())->count();
    }
    private function tasktotal(){
        $now = Carbon::now();
        return Tasks::where('comp_id', $this->compid())
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->count();

        // return Tasks::where('comp_id',$this->compid())->count();
    }
    public function dashboardIndex(){
        // //sales bulanan
        $blnthn = date("m").date("y");
        $bulan = array('01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', 
                       '05'=>'Mei', '06'=>'Juni', '07'=>'Juli','08'=>'Agustus', 
                       '09'=>'September', '10'=>'Oktober', '11'=>'Nopember', 
                       '12'=>'Desember');
        $bulan=$bulan[substr($blnthn,0,2)];
        $tahun=substr($blnthn,2,4);
        $bulantahun=$bulan." ".$tahun;

        $dashboard=new Dashboard;
        $dashboard->dash_account_styles=$this->accountstyletotal();
        $dashboard->dash_locations=$this->locationtotal();
        $dashboard->dash_task_total=$this->tasktotal();
        $dashboard->dash_process_error=$this->tasktotal();

       
        return view('home/company/manager/landing',compact("dashboard","bulantahun"));
    }
}
