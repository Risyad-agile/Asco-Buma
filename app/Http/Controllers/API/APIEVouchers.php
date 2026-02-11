<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\EVouchers;
use App\Models\Members;


class APIEVouchers extends Controller
{
    public function getEVoucherByCompany($compid){
        $evouchers=EVouchers::with('members','companies')->where([['comp_id',$compid],['evoucher_state','1']])->get();
        return response()->json($evouchers);
    }
    public function getEVoucherByMember($memberno){
        $member=function($query) use ($memberno){
            $query->where('member_no',$memberno);
        };
        $evouchers=EVouchers::with(['members'=>$member,'companies'])->where('evoucher_state','2')->whereHas('members',$member)->get();
        return response()->json($evouchers);
    }
    public function redeemEVoucher(Request $request){
        // dd($request);
        $memberno=$request->get("member_no");
  
        $memberpoin=Members::where('member_no',$memberno)->first()->member_points;
        $selectedpoin=0;
        for ($i=0; $i < count($request->input('evouchers')) ; $i++) {
            $evoucher_no=$request->input('evouchers')[$i]['evoucher_no'];
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
        $memberpoin=$memberpoin-$selectedpoin;
        Members::where('member_no',$memberno)->update(['member_points'=>$memberpoin]);

        $members=Members::with('evouchers')->where('member_no',$memberno)->first();
        return response()->json($members);
    }

    public function updatePendingEVoucher(Request $request, $memberno){
        for ($i=0; $i < count($request->input('evouchers')) ; $i++) {
            $evoucherno=$request->input('evouchers')[$i]['evoucher_no'];
            Evouchers::where('evoucher_no','=',$evoucherno)
            ->update([
              'evoucher_state'=>'4', //pending
            ]);
        }
        $members=Members::with('evouchers')->where('member_no',$memberno)->first();
        return response()->json($members);
    }
}
