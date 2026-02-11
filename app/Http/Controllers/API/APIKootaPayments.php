<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\KootaPayments;
use App\Models\KootaResponLogs;
use App\Models\Sales;
use DB;
use Config;

class APIKootaPayments extends Controller
{
    #pengaturan server dilakukan di config
    public function getKootaActiveServer(){
        $activeServer=Config::get('agile.payment.koota.production');
        return response()->json($activeServer);
    }
    public function getKootaPayMentByDeviceId($deviceid){
        $model=KootaPayments::with('store')->where('koota_device_id',$deviceid)->first();
        if(!$model){
            $model=new KootaPayments;
            $model->store_id="";  
            $model->koota_device_id=""; 
            $model->koota_terminal_id=""; 
            $model->koota_secret_key="";  
            $model->koota_state='NOT FOUND';
            $model->store=null;
        }
        return response()->json($model);
    }


    #menyimpan log pembayaran yang di lakukan device
    public function saveResponLog(Request $request){
        $status='0';
        if($request->get("status")=='true'){
            $status='1';
        }
        $model=new KootaResponLogs;
        $model->store_id=$request->get("store")["store_id"]; 
        $model->koota_device_id=$request->get("device_id");   
        $model->status=$status;   
        $model->status_code=$request->get("status_code"); 
        $model->message=$request->get("message");   
        $model->reference_no=$request->get("reference_no");  
        $model->transmission_date_time=$request->get("transmission_date_time"); 
        $model->anggota_simpanan_no_rekening=$request->get("data")["anggota_simpanan_no_rekening"];   
        $model->amount=$request->get("data")["amount"]; 
        $model->signature=$request->get("signature");   
        $model->reff_trx_id=$request->get("data")["reff_trx_id"]; 
        $model->anggota_name=$request->get("data")["anggota_name"];  
        $model->merchant_name=$request->get("data")["merchant_name"]; 
        $model->qr_data=$request->get("data")["qr_data"];  
        $model->address=$request->get("data")["address"]; 
        $model->terminal_id=$request->get("data")["terminal_id"];   
        // $model->status_bayar=$request->get("data")["status"]; 
        // $model->reqistrasion_date=$request->get("data")["reqistrasion_date"]; 
        $model->save();
        return response()->json($model);
    }

    public function updateResponLog(Request $request,$id){
        $statusbayar=isset($request->input('data')['status_bayar'])?$request->input('data')['status_bayar']:0;
        KootaResponLogs::where('reff_trx_id', $id)->update([
            'message'=>$request->get("message"),
            'status_bayar'=>$statusbayar,
        ]);

        $kootarespon=KootaResponLogs::with('store')->where('reff_trx_id',$id)->first();
        return response()->json($kootarespon);
    }

    public function getKootaResponLogTodayListByStore($storeid){
        $tanggal=Date('Y-m-d');
        $models=KootaResponLogs::with('store')->where([[DB::raw('SUBSTRING(transmission_date_time,1,10)'),$tanggal],['store_id',$storeid]])->get();
        $kootarespon=array();
        foreach ($models as $key => $model) { 
                $respon=[
                    'status' => $model->status, 
                    'status_code' => $model->status_code, 
                    'message' => $model->message, 
                    'reference_no' => $model->reference_no, 
                    'device_id' => $model->device_id, 
                    'transmission_date_time' => $model->transmission_date_time, 
                    'store_id' =>$model->store_id, 
                    'data' => [
                        'reff_trx_id' => $model->reff_trx_id, 
                        'anggota_simpanan_no_rekening' => $model->msg, 
                        'anggota_name' => $model->anggota_name, 
                        'terminal_id' => $model->terminal_id, 
                        'merchant_name' => $model->merchant_name, 
                        'qr_data' => $model->qr_data, 
                        'address' => $model->address, 
                        'amount' => $model->amount, 
                        'reqistrasion_date' =>  $model->reqistrasion_date, 
                        'status_bayar' => $model->status_bayar,
                    ],
                ];
            $kootarespon[]=$respon;
        }
        return response()->json($kootarespon);
    }
    public function getKootaResponLogTodayListSuccessByStore($storeid){
        $tanggal=Date('Y-m-d');
        $models=KootaResponLogs::with('store')->where([[DB::raw('SUBSTRING(transmission_date_time,1,10)'),$tanggal],
            ['store_id',$storeid],['status_bayar','1']])->get();
        $kootarespon=array();
        foreach ($models as $key => $model) { 
            $sales=new Sales;
            if($model->sales!=null){
                $sales->sale_no=$model->sales->sale_no;
                $sales->username=$model->sales->username;
                $sales->store_id=$model->sales->store_id;
            }else{
                $sales=null;
            }
            $respon=[
                'status' => $model->status, 
                'status_code' => $model->status_code, 
                'message' => $model->message, 
                'reference_no' => $model->reference_no, 
                'device_id' => $model->device_id, 
                'sale_no' => $model->sale_no, 
                'transmission_date_time' => $model->transmission_date_time, 
                'store_id' =>$model->store_id, 
                'sales' => $sales,
                'data' => [
                    'reff_trx_id' => $model->reff_trx_id, 
                    'anggota_simpanan_no_rekening' => $model->msg, 
                    'anggota_name' => $model->anggota_name, 
                    'terminal_id' => $model->terminal_id, 
                    'merchant_name' => $model->merchant_name, 
                    'qr_data' => $model->qr_data, 
                    'address' => $model->address, 
                    'amount' => $model->amount, 
                    'reqistrasion_date' =>  $model->reqistrasion_date, 
                    'status_bayar' => $model->status_bayar,
                ],
            ];
            $kootarespon[]=$respon;
        }
        return response()->json($kootarespon);
    }

    #update nomor sales untuk keperluan maping jika ada pembatalan sales
    public function updateSaleNumber($reff_trx_id,$saleno){
        KootaResponLogs::where('reff_trx_id',$reff_trx_id)->update(['sale_no'=>$saleno]);
        return;
    }
}

