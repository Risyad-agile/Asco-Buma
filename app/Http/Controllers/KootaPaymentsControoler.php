<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\KootaPayments;
use App\Models\Stores;
use Validator;


class KootaPaymentsControoler extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compid(){
        return Auth::user()->stores->companys->comp_id;
    }
    public function index()
    {
        $stores=Stores::where('store_ho','0')->get();
        return view('masters/kootapayments',compact('stores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $model=KootaPayments::where('koota_state','1')->get();
        return $model;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|string',
            'koota_device_id' => 'required|string',
            'koota_terminal_id' => 'required|string',
            'koota_secret_key' => 'required|string', 
        ],[
            'store_id.required' => 'Toko Harus di isi',
            'koota_device_id.required' => 'Device Harus di isi',
            'koota_terminal_id.required' => 'Terminal ID Harus di isi',
            'koota_secret_key.required' => 'Secret Key Harus di isi', 
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }
       
        try {
            $model=new KootaPayments;
            $model->store_id=$request->get('store_id'); 
            $model->koota_device_id=$request->get('koota_device_id'); 
            $model->koota_terminal_id=$request->get('koota_terminal_id'); 
            $model->koota_secret_key=$request->get('koota_secret_key');  
            $model->koota_state='1';
            $model->save();
            if (!$model) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            return response()->json(['status' => 'success', 'message' => 'Data successfully Saved', 'code' => 200]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);  
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $res = KootaPayments::where('id', $id)->update($request->except(['id']));

        if (!$res) {
            return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
        }

        return response()->json(['status' => 'success', 'message' => 'Data successfully Updated', 'code' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        KootaPayments::where('id',$id)->update(['koota_state'=>'0']); 
        return response()->json(['status' => 'success', 'message' => 'Data successfully Deleted', 'code' => 200]);
    }
}
