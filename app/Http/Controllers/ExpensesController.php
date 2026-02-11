<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Expenses;
use Validator;


class ExpensesController extends Controller
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
    public function username(){
        return Auth::user()->username;
    }
    public function activeuserstoreid(){
        return Auth::user()->stores->store_id;
    }
    public function index()
    {
        return view('masters/expenses');        
    }
   

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $expenses=Expenses::where('expense_state','1')->get();
        return $expenses;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['status' => 'error', 'message' => 'Request is not permitted', 'code' => 404]);
        }

        $validator = Validator::make($request->all(), [
            'expense_desc' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }

        try {
            $model = new Expenses();
            $model->comp_id = "AGIL";
            $model->expense_desc = $request->expense_desc;
            $model->expense_state = "1";
            $exec = $model->save();

            if (!$exec) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }

            return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);

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
        $res = Expenses::where('id', $id)->update($request->except(['id']));

        if (!$res) {
            return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
        }

        return response()->json(['status' => 'success', 'message' => 'Data successfully edited', 'code' => 200]);
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
}
