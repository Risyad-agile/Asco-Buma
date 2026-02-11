<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cities; 
use Validator;

class CitiesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compid(){
        return Auth::user()->stores->companies->id;
    }

    public function index()
    {
        return view('masters/cities');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $cities=Cities::where('city_state','1')->get();
        return $cities;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['status' => 'error', 'message' => 'Request is not permitted', 'code' => 404]);
        }
        $validator = Validator::make($request->all(), [
            'city_name' => 'required|string',  
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }

        try {
            $city=new Cities;
            $city->city_name=$request->get('city_name');
            $city->city_state='1';

            $exec=$city->save();
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
        $res = Cities::where('id', $id)->update($request->except(['id']));

        if (!$res) {
            return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
        }

        return response()->json(['status' => 'success', 'message' => 'Data successfully edited', 'code' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
