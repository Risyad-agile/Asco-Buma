<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GajaPayments;
use App\Models\Stores;
use Validator;

class GajaPaymentsController extends Controller
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

    public function compid(){
        return Auth::user()->stores->companys->comp_id;
    }
    public function index()
    {
        $stores=Stores::where('store_ho','0')->get();
        return view('masters/gajapayments',compact('stores'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $model=GajaPayments::where('gaja_state','1')->get();
        return $model;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_id' => 'required|string',
            'gaja_secret_code' => 'required|string',
            'gaja_device_id' => 'required|string',
            'gaja_merchant_id' => 'required|string',
            'gaja_terminal_id' => 'required|string',
        ],[
            'store_id.required' => 'Toko Harus di isi',
            'gaja_secret_code.required' => 'Secret Code Harus di isi',
            'gaja_device_id.required' => 'Device ID Harus di isi',
            'gaja_merchant_id.required' => 'Merchant ID Harus di isi',
            'gaja_terminal_id.required' => 'Terminal ID Harus di isi',
        ]);


        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }
       
        try {
            $model=new GajaPayments;
            $model->store_id=$request->get('store_id'); 
            $model->gaja_device_id=$request->get('gaja_device_id'); 
            $model->gaja_merchant_id=$request->get('gaja_merchant_id'); 
            $model->gaja_terminal_id=$request->get('gaja_terminal_id'); 
            $model->gaja_secret_code=$request->get('gaja_secret_code'); 
            $model->gaja_state='1';
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
        $res = GajaPayments::where('id', $id)->update($request->except(['id']));

        if (!$res) {
            return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
        }

        return response()->json(['status' => 'success', 'message' => 'Data successfully Updated', 'code' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        GajaPayments::where('id',$id)->update(['gaja_state'=>'0']); 
        return response()->json(['status' => 'success', 'message' => 'Data successfully Deleted', 'code' => 200]);
    }
}
