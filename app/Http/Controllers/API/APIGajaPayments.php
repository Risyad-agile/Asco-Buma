<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\GajaPayments;
use App\Models\GajaResponLog;
use App\Models\Sales;
use DB;
use Config;
class APIGajaPayments extends Controller
{
    #pengaturan server dilakukan di config
    public function getGajaActiveServer(){
        $activeServer=Config::get('agile.payment.gaja.production');
        return response()->json($activeServer);
    }
    public function getGajaPayMentByDeviceId($deviceid){
        $model=GajaPayments::with('store')->where('gaja_device_id',$deviceid)->first();
        if(!$model){
            $model=new GajaPayments;
            $model->store_id=""; 
            $model->gaja_device_id=""; 
            $model->gaja_merchant_id=""; 
            $model->gaja_terminal_id=""; 
            $model->gaja_secret_code=""; 
            $model->gaja_state='NOT FOUND';
            $model->store=null;
        }
        return response()->json($model);
    }

    #menyimpan pembayaran yang di lakukan device, untuk selanjutnya digunakan dalam pengecekan
    #melalui pengiriman notifikasi
    public function saveResponLog(Request $request){
        $signa=$request->get("signature"); 
        $model=GajaResponLog::where('signature',$signa)->first();
        if($model){
            return response()->json($model);
        }

        $model=new GajaResponLog;
        $model->store_id=$request->get("store")["store_id"]; 
        $model->gaja_device_id=$request->get("gaja_device_id");   
        $model->gaja_secret_code=$request->get("gaja_secret_code");   
        $model->amount=$request->get("amount");   
        $model->tran_type=$request->get("tran_type"); 
        $model->amount_tran=$request->get("amount_tran");  
        $model->trace_number=$request->get("trace_number"); 
        $model->datetime_tran=$request->get("datetime_tran");   
        $model->refnum=$request->get("refnum");   
        $model->merchant_id=$request->get("merchant_id"); 
        $model->terminal_id=$request->get("terminal_id");   
        $model->qr_owner=$request->get("qr_owner"); 
        $model->rc=$request->get("rc");  
        $model->msg=$request->get("msg"); 
        $model->qrdata=$request->get("private_data")["qrdata"];   
        $model->rrn=$request->get("rrn"); 
        $model->issuer_name=$request->get("issuer_name");  
        $model->forwarding_id=$request->get("forwarding_id"); 
        $model->signature=$request->get("signature"); 
        $model->response_state="1";
        $model->save();
        return response()->json($model);
    }

    #menerima notofikasi dari GAJA Payment, pembayaran sudah sesuai atau belum
    #jika sudah di bayar, ada respon code sukses
    #request dan respon dalam bentuk json
    #selanjutnya update pada tabel transaksi Gaja
    public function receiveNotification(Request $request){
        // dd($request->json());
        $refnum=$request->json("refnum"); 
        $model=GajaResponLog::where('refnum',$refnum)->first();
        if($model){
            //update status 
            GajaResponLog::where('refnum',$refnum)->update([
                    'tran_type'=>'Notify',
                    'rrn'=>$request->json("rrn"),
                    'issuer_name'=>$request->json("issuer_name"), 
                    'response_state'=>"2" // receive notif
            ]);
            $respon=['tran_type' => $request->json("tran_type"), 
                'amount_tran' => $request->json("amount_tran"), 
                'trace_number' => $request->json("trace_number"), 
                'datetime_tran' => $request->json("datetime_tran"), 
                'refnum' => $request->json("refnum"), 
                'qr_owner' => $request->json("qr_owner"), 
                'cust_id' => $request->json("cust_id"), 
                'resp_code' => [
                'rc' => '0000',
                'msg' => 'SUCCESS',
            ]];
        }else{
            $respon=['tran_type' => $request->json("tran_type"), 
                'amount_tran' => $request->json("amount_tran"), 
                'trace_number' => $request->json("trace_number"), 
                'datetime_tran' => $request->json("datetime_tran"), 
                'refnum' => $request->json("refnum"), 
                'qr_owner' => $request->json("qr_owner"), 
                'cust_id' => $request->json("cust_id"), 
                'resp_code' => [
                'rc' => '0099',
                'msg' => 'FAILED',
            ]];
        }
        return response()->json( $respon);
    }

    // 1. ada update status ketika berhasil
    // 2. update status ketika gagal, konsepnya update signature
    // 3. update resp code dan resp msg
    // 4. update setelah di batalkan
    // 5. void/ pembatalan signature bisa berbeda, konsepnya update signature

    #perbaharui data setelah mendapatkan status berhasil ketika transaksi sukses di bayar
    #jika terjadi gagal, kemudian diperiksa ulang, maka di perbaharui termasuk datetime dan signature diganti dengan yang baru
    public function updateSuccessState(Request $request){
        $refnum=$request->get("refnum"); 
        $model=GajaResponLog::where('refnum',$refnum)->first();
        if($model->response_state=='2'){
            $rc=$request->get("resp_code")["rc"];
            $msg=$request->get("resp_code")["msg"];
            $status="3"; 
            $merchant_name=isset($request->get("private_data")["merchantName"])?$request->get("private_data")["merchantName"]:null;
            $merchant_city=isset($request->get("private_data")["merchantCity"])?$request->get("private_data")["merchantCity"]:null;
            $merchant_country=isset($request->get("private_data")["merchantCountry"])?$request->get("private_data")["merchantCountry"]:null;
            $merchant_criteria=isset($request->get("private_data")["merchantCriteria"])?$request->get("private_data")["merchantCriteria"]:null;
            $merchant_pan=isset($request->get("private_data")["merchantPan"])?$request->get("private_data")["merchantPan"]:null;
            $customer_pan=isset($request->get("private_data")["customerPan"])?$request->get("private_data")["customerPan"]:null;
            $approval_code=isset($request->get("private_data")["approvalCode"])?$request->get("private_data")["approvalCode"]:null;
            GajaResponLog::where('refnum',$refnum)->update([
                'tran_type'=>'status',
                'forwarding_id'=>$request->get("forwarding_id"),
                'rc'=>$rc,
                'msg'=>$msg,
                'merchant_name'=>$merchant_name,
                'merchant_city'=>$merchant_city,
                'merchant_country'=>$merchant_country,
                'merchant_criteria'=>$merchant_criteria,
                'merchant_pan'=>$merchant_pan,
                'customer_pan'=>$customer_pan,
                'approval_code'=>$approval_code,
                'datetime_tran' =>$request->get('datetime_tran'), 
                'signature' =>$request->get('signature'), 
                'response_state'=>$status // receive status 
            ]);
        }
        return response()->json($model);
    }
    public function updateUnSuccessState(Request $request){
        $refnum=$request->get("refnum"); 
        $model=GajaResponLog::where('refnum',$refnum)->first();
        //update status 
        $status="4"; //gagal bayar
        $rc=$request->get("resp_code")["rc"];
        $msg=$request->get("resp_code")["msg"];
        GajaResponLog::where('refnum',$refnum)->update([
            'tran_type'=>'status',
            'rc'=>$rc,
            'msg'=>$msg,
            'datetime_tran' =>$request->get('datetime_tran'), 
            'signature' =>$request->get('signature'), 
            'response_state'=>$status 
        ]);
        return response()->json($model);
    }
    public function updateVoidState(Request $request){
        $refnum=$request->get("refnum"); 
        $model=GajaResponLog::where('refnum',$refnum)->first();
        if($model->response_state=='3'){
            $rc=$request->get("resp_code")["rc"];
            $msg=$request->get("resp_code")["msg"];
            $status="5"; 
            $merchant_name=isset($request->get("private_data")["merchantName"])?$request->get("private_data")["merchantName"]:null;
            $merchant_city=isset($request->get("private_data")["merchantCity"])?$request->get("private_data")["merchantCity"]:null;
            $merchant_country=isset($request->get("private_data")["merchantCountry"])?$request->get("private_data")["merchantCountry"]:null; 
            $approval_code=isset($request->get("private_data")["approvalCode"])?$request->get("private_data")["approvalCode"]:null;
            GajaResponLog::where('refnum',$refnum)->update([
                'tran_type'=>'status',
                'forwarding_id'=>$request->get("forwarding_id"),
                'rc'=>$rc,
                'msg'=>$msg,
                'merchant_name'=>$merchant_name,
                'merchant_city'=>$merchant_city,
                'merchant_country'=>$merchant_country,
                'approval_code'=>$approval_code,
                'datetime_tran' =>$request->get('datetime_tran'), 
                'signature' =>$request->get('signature'), 
                'response_state'=>$status // receive status 
            ]);
        }
        //void sales disini
        return response()->json($model);
    }


    public function getGajaPaymentTodayListByStore($storeid){
        $today=date("Ymd");
        $models=GajaResponLog::where([['store_id',$storeid],[DB::raw('substring(datetime_tran,1,8)'),$today]])->get();
        $gajarespon=array();
        foreach ($models as $key => $model) { 
            $respon=['tran_type' => $model->tran_type, 
                'amount_tran' => $model->amount_tran, 
                'trace_number' => $model->trace_number, 
                'issuer_name' => $model->issuer_name, 
                'datetime_tran' => $model->datetime_tran, 
                'refnum' => $model->refnum, 
                'qr_owner' => $model->qr_owner, 
                'cust_id' => $model->cust_id, 
                'store_id' => $model->store_id, 
                'sale_no' => $model->sale_no, 
                'amount' => $model->amount, 
                'merchant_id' =>  $model->merchant_id, 
                'terminal_id' => $model->terminal_id,
                'gaja_device_id' => $model->gaja_device_id, 
                'gaja_secret_code' => $model->gaja_secret_code, 
                'response_state' =>$model->response_state,
                'resp_code' => [
                    'rc' => $model->rc, 
                    'msg' => $model->msg, 
                ],
                'private_data' => [
                    'merchant_name' => $model->merchant_name, 
                    'merchant_city' => $model->merchant_city, 
                    'merchant_country' => $model->merchant_country, 
                    'merchant_criteria' => $model->merchant_criteria, 
                    'merchant_pan' => $model->merchant_pan, 
                    'customer_pan' => $model->customer_pan, 
                    'approval_code' => $model->approval_code, 
                ]
            ];

            $gajarespon[]=$respon;
        }

        return response()->json($gajarespon);
    }
    public function getGajaResponLogTodayListSuccessByStore($storeid){
        $today=date("Ymd");
        $models=GajaResponLog::with('sales')->where([['store_id',$storeid],[DB::raw('substring(datetime_tran,1,8)'),$today],
            ['response_state','3']])->get();
        $gajarespon=array();
        foreach ($models as $key => $model) { 
            $sales=new Sales;
            if($model->sales!=null){
                $sales->sale_no=$model->sales->sale_no;
                $sales->username=$model->sales->username;
                $sales->store_id=$model->sales->store_id;
            }else{
                $sales=null;
            }
            $respon=['tran_type' => $model->tran_type, 
                'amount_tran' => $model->amount_tran, 
                'trace_number' => $model->trace_number, 
                'issuer_name' => $model->issuer_name, 
                'datetime_tran' => $model->datetime_tran, 
                'refnum' => $model->refnum, 
                'qr_owner' => $model->qr_owner, 
                'cust_id' => $model->cust_id, 
                'store_id' => $model->store_id, 
                'sale_no' => $model->sale_no, 
                'amount' => $model->amount, 
                'merchant_id' =>  $model->merchant_id, 
                'terminal_id' => $model->terminal_id,
                'gaja_device_id' => $model->gaja_device_id, 
                'gaja_secret_code' => $model->gaja_secret_code, 
                'response_state' =>$model->response_state,
                'sales' => $sales,
                'resp_code' => [
                    'rc' => $model->rc, 
                    'msg' => $model->msg, 
                ],
                'private_data' => [
                    'merchant_name' => $model->merchant_name, 
                    'merchant_city' => $model->merchant_city, 
                    'merchant_country' => $model->merchant_country, 
                    'merchant_criteria' => $model->merchant_criteria, 
                    'merchant_pan' => $model->merchant_pan, 
                    'customer_pan' => $model->customer_pan, 
                    'approval_code' => $model->approval_code, 
                ]
            ];
            $gajarespon[]=$respon;
        }

        return response()->json($gajarespon);
    }

    #update nomor sales untuk keperluan maping jika ada pembatalan sales
    public function updateSaleNumber($refnum,$saleno){
        GajaResponLog::where('refnum',$refnum)->update(['sale_no'=>$saleno]);
        return;
    }
}
