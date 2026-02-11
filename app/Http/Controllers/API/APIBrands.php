<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; 
use App\Models\Companys;
use App\Models\Brands;

class APIBrands extends Controller
{
    public function getBrandByCompId($comp_id)
    {
        $brands=Brands::where('comp_id','=',$comp_id)->get();
        return response()->json($brands);
    }
}
