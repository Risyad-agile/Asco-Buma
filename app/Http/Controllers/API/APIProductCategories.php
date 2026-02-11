<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Companys;
use App\Models\Stores;
use App\Models\Products;
use App\Models\ProductCategories;
use App\Http\Controllers\StoresController;
class APIProductCategories extends Controller
{
    public function getProductCategoriesByCompId($comp_id){
        $prodcat=ProductCategorys::where([['comp_id',$comp_id],['prodcat_state','1']])->get();
        return response()->json($prodcat);
    }

    #-------------------WEBPOS
    public function webposProductCategoriesByCompId($comp_id){
        $prodcat=ProductCategorys::where([['comp_id',$comp_id],['prodcat_state','1']])->get();
        return response()->json($prodcat);
    }
    public function webposProductCategoriesByStoreId($store_id){
        $storecontrol=new StoresController;
        $storetype=Stores::where('store_id',$store_id)->first()->store_type;
        
        $store=function ($query) use ($store_id){
            $query->where('stores.store_id',$store_id);
        };
        if($storecontrol->checkStoreType($storetype)=="RETAIL"){
            $prodcatids=Products::whereHas('stores',$store)->groupBy('prodcat_id')->get('prodcat_id');
        }
        if($storecontrol->checkStoreType($storetype)=="RESTO"){
            $prodcatids=Products::whereHas('stores',$store)
            ->whereIn('products.product_state',array('2','3'))
            ->groupBy('prodcat_id')->get('prodcat_id');
        }
        
        $prodcat=ProductCategorys::whereIn('prodcat_id',$prodcatids)->get();
        return response()->json($prodcat);
    }

   

    public function webposProductCategoriesRestoByStoreId($store_id){
        $store=function ($query) use ($store_id){
            $query->where('stores.store_id',$store_id);
        };

        $prodcatids=Products::whereHas('stores',$store)
            ->whereIn('products.product_state',array('2','3'))
            ->groupBy('prodcat_id')->get('prodcat_id');

        $prodcat=ProductCategorys::whereIn('prodcat_id',$prodcatids)->get();
        return response()->json($prodcat);
    }

}
