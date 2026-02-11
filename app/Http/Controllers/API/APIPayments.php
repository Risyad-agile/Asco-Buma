<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payments;
use App\Models\Stores;
use DB;

class APIPayments extends Controller
{
    public function paymentStore($storeid){
        $payControl=new PaymentsController; 
        $payments=$payControl->paymentActiveListByStore($storeid);
        return response()->json($payments);
    }
    public function paymentbycomp($storeid){
        $comp_id=Stores::where('store_id','=',$storeid)->first()->comp_id;
        $payments=Payments::where('pay_state','!=','0')->where('comp_id','=',$comp_id)->get();
        return response()->json($payments);
    }
    public function paymentbystore($storeid){

        // $store=function ($query) use ($storeid){
        //     $query->where([['stores.store_id',$storeid],['active_state','1']]);
        // };
        // $payments=Payments::with(['stores'=>$store])->whereHas('stores',$store)->orderBy('payments.pay_id')->get();

        $payments=DB::table('payment_stores')
            ->join('payments','payment_stores.pay_id','=','payments.pay_id')
            ->where([['store_id',$storeid],['active_state','1']])->orderBy('payments.pay_id')->get();

       
        return $payments;
    }

    #-----Agile Manager
    public function amPaymentActiveByStore($storeid){
        // $store=function ($query) use ($storeid){
        //     $query->where('stores.store_id',$storeid);
        // };
        // $payments=Payments::with(['stores'=>$store])->whereHas('stores',$store)->get();
        $store=Stores::with('payments')->where('store_id',$storeid)->first();
        return response()->json($store);
    }
    public function amPaymentActivationUpdate(Request $request){
        //hapus semua pembayaran
        //aktifkan yang dikirim
        $storeid=$request->get('store_id');
        $payments=$request->get('payments');
        $store=Stores::where('store_id',$storeid)->first();
        $store->payments()->detach(); //hapus berdasarkan payment

        for ($i=0; $i < count($request->input('payments')) ; $i++) {
            $paymentid=$request->input('payments')[$i]['pay_id'];
            $store->payments()->attach(['store_id'=>$storeid],[
                'pay_id'=>$paymentid,
                'active_state'=>'1',
            ]);
        }
        $store=Stores::with('payments')->where('store_id',$storeid)->first();
        return response()->json($store);
    }
}
