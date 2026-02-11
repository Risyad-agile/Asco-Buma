<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EVouchers;
use App\Models\Members;

class EVoucherController extends Controller
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
        return Auth::user()->stores->store_id;
    }
    public function activeuserstoreid(){
        return Auth::user()->stores->store_id;
    }
    public function compid(){
        return Auth::user()->stores->companys->comp_id;
    }
    public function index()
    {
        $evouchers=EVouchers::where('comp_id',$this->compid())->get();
        return view('masters/evoucherindex',compact('evouchers'));
    }
    public function main(Request $request){
        $evoucherstat=$request->get('evoucherstat');
 
        if($evoucherstat=="ADDNEW"){  
            return view('masters/evouchergenerate');
        }else{
            $members=Members::where([['comp_id',$this->compid()],['member_state','1']])->get();
            $evouchers=EVouchers::where([['comp_id',$this->compid()],['evoucher_state','1']])->get();
            return view('masters/evoucherredeem',compact('members','evouchers'));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $evoucher_value_idx=$request->get('form')['evoucher_value_idx']; 
        $evoucher_qty=$request->get('form')['evoucher_qty'];    
        $evoucher_point_value=$request->get('form')['evoucher_point_value']; 

        $evoucher_value=0;
        switch ($evoucher_value_idx) {
            case 1:
                $evoucher_value=50000;
                break;
            case 2:
                $evoucher_value=100000;
                break;
            case 3:
                $evoucher_value=200000;
                break;
            case 4:
                $evoucher_value=500000;
            case 5:
                $evoucher_value=1000000;
                break;
        }

        for ($i=0; $i < $evoucher_qty; $i++) { 
            $evoucher=new EVouchers;
            $evoucher->evoucher_no=$this->autoEVoucherNo();
            $evoucher->comp_id=$this->compid();
            $evoucher->evoucher_exp_date=$request->input('form')['evoucher_exp_date'];
            $evoucher->evoucher_redeem_date=date("Y-m-d H:i:s");  
            $evoucher->evoucher_value_idx=$evoucher_value_idx;  
            $evoucher->evoucher_value=$evoucher_value;  
            $evoucher->evoucher_point_value=$evoucher_point_value;  
            $evoucher->evoucher_state="1";   
            $evoucher->save();
        }
        
        return "SUCCESS";
      
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    public function redeemIndex(){
        $members=Members::where([['comp_id',$this->compid()],['member_state','1']])->get();
        $evouchers=EVouchers::where([['comp_id',$this->compid()],['evoucher_state','1']])->get();
        return view('masters/evoucherredeem',compact('members','evouchers'));
    }
    public function redeemEVoucher(Request $request){
        // dd($request);
        $memberno=$request->get("memberno");
        $memberpoin=Members::where('member_no',$memberno)->first()->member_points;
        $selectedpoin=0;
        for ($i=0; $i < count($request->input('selectedvouchers')) ; $i++) {
            $evoucher_no=$request->input('selectedvouchers')[$i];
            $evoucher=EVouchers::where('evoucher_no',$evoucher_no)->first();

            //memastikan voucher yang di pilih belum digunakan, handling multi user login
            if($evoucher->evoucher_state='1'){
                $selectedpoin=$selectedpoin+$evoucher->evoucher_point_value;

                Evouchers::where('evoucher_no','=',$evoucher_no)
                ->update([
                  'member_no'=>$memberno,
                  'evoucher_state'=>"2", //claimed
                  'evoucher_claim_date'=>date("Y-m-d H:i:s"),
                ]);

            }
        }

        //kurangin poin member
        $memberpoin=$memberpoin-$selectedpoin;
        Members::where('member_no',$memberno)->update(['member_points'=>$memberpoin]);

        return "SUCCESS";

    }

    public function autoEVoucherNo()
    {
      // aturan nomor Mutasi In : StoreId-MTINmmyy1234
      $bulan=date("m");
      $tahun=date("y");
      $compid=$this->compid();
      $strNewId = $compid.$tahun.$bulan."0001";

      // kalau belum ada record sebelumnya berarti ini yang pertama
      if(EVouchers::where('comp_id','=',$compid)->get()->count()==0){
          return $strNewId;
      }
      $strLastId= EVouchers::where('comp_id','=',$compid)->orderby('evoucher_no', 'desc')->first()->evoucher_no;
      $intNewId= substr($strLastId,-4)+1;

      $strNewId=strlen($intNewId);
      switch (strlen($intNewId)) {
          case 1:
              $strNewId=$compid.$tahun.$bulan.'000'.$intNewId;
              break;
          case 2:
              $strNewId=$compid.$tahun.$bulan.'00'.$intNewId;
              break;
          case 3:
              $strNewId=$compid.$tahun.$bulan.'0'.$intNewId;
              break;
          case 4:
              $strNewId=$compid.$tahun.$bulan.$intNewId;
              break;
      }
      return $strNewId;
    }
}
