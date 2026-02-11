<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Stores;
use App\Models\Companys;
use Session;
use DB;

class StoreServices
{
    //data store id dan nama berdasarkan perusahaan 
    public static function getStoreIDNameByCompanyID($comp_id){
        $stores=Stores::where('comp_id','=',$comp_id)
                // ->where('store_default','=','0')
                ->get(array('store_id','store_name'));
        return $stores;
    }
    //data store selain store sendiri & tanpa HO, tetap dalam satu perusahaan
    public static function getOtherStoreByCompanyID($store_id){
        $headoffice=substr($store_id,-3); //kode 000 terakhir adalah HO 
        $stores=new Stores;
        if($headoffice!=0){
            $comp_id=Stores::where('store_id','=',$store_id)->first()->comp_id;
            $stores=Stores::where('comp_id','=',$comp_id)
                    ->where('store_id','!=',$store_id)
                    ->where('store_default','=','0')
                    ->get(array('store_id','store_name'));
        }
        return $stores;
    }

    //data store selain store sendiri & dengan HO, tetap dalam satu perusahaan
    public static function getOtherStoreHOByCompanyID($store_id){
        $stores=new Stores;
        $comp_id=Stores::where('store_id','=',$store_id)->first()->comp_id;
        $stores=Stores::where('comp_id','=',$comp_id)
                    ->where('store_id','!=',$store_id)
                    ->get(array('store_id','store_name'));
        return $stores;
    }

}