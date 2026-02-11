<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Promotions;
use App\Models\Stores;
use App\Models\PromotionProducts;
use App\Services\ProductServices;

class APIPromotions extends Controller
{
    public function getPromoById($promono){
        $promos=Promotions::with('products')->where("promo_no",$promono)->get();
        return response()->json($promos);
    }
    public function getPromoByStore($storeid){
        $stores=function ($query) use ($storeid){
            $query->where('stores.store_id','=',$storeid);
        };
        $promos=Promotions::with(['stores'=>$stores,'products'])->where('promo_state','1')
                ->whereHas('stores',$stores)->get();


        // $stores=Stores::with('promotions.products')->where('store_id',$storeid)->get();

        //lakukan injeksi harga dan stok toko
        // foreach ($promos as $promo) {
        //     foreach ($promo->products as $prod) {
        //         $productid=$prod->product_id;
        //         $product=ProductServices::getProductStoreByProductID($storeid,$productid);
        //         $prod->product_price=$product->product_price;
        //         $prod->product_stock=$product->product_stock;
        //     }
        // }
        return response()->json($promos);
    }
    public function getPromoStoreProduct($storeid,$productid){
        $store=function ($query) use ($storeid){
            $query->where('stores.store_id',$storeid);
        };
        $products=function ($query) use ($store,$productid){
            $query->whereHas('stores',$store)->where('products.product_id',$productid);
        };
        $promonos=Promotions::whereHas('products',$products)->pluck('promo_no');
        $promos=Promotions::with('products')->whereIn('promo_no',$promonos)->where('promo_state','1')->get();
        return response()->json($promos);
    }

    public function getProductsPromo($storeid){
        $products=ProductServices::getProductStoresByStoreId($storeid); //produk yang aktif di toko tsb
        // $promos=Promotions::where('comp_id','=',$this->compid())->get();
        return response()->json($products);
    }
    

    public function getpromosbyprodid($productid){
        //cari promo yang masih aktif dengan id produk yang dikirimkan
        $promos=Promotions::with('products')->where('promo_state','=','1')
        // ->where('comp_id','=',$this->compid())
        ->whereHas('products',function($query) use ($productid)
            { 
                $query->where('products.product_id','=',$productid);
            })->get();

        if($promos==null){
            $promos=new Promotions;
            $promos->promo_no="NOTPROMO";
        }

        return response()->json($promos);
    }
    public function getpromosstorebyprodid($storeid,$productid){

        //ambil data store terpilih
        // $store=function ($query) use($storeid){
        //     $query->where('store_id',$storeid);
        // };

        // $products=function($query) use ($store){
        //     $query->with(['stores'=>$store]);
        // };

        // $promos=Promotions::with(['products'=>$products,'stores'=>$store])->where('promo_state','=','1')->whereHas('products',$products)->get();

        //cari promo yang masih aktif dengan id produk yang dikirimkan
        $promos=Promotions::with('products')->where('promo_state','=','1')
        ->whereHas('stores',function($query) use ($storeid){
            $query->where('stores.store_id','=',$storeid);
        })
        ->whereHas('products',function($query) use ($productid){ 
            $query->where('products.product_id','=',$productid);
        })->get();

        //cari lokasi dengan promo & lokasi tersebut, jika tidak ditemukan
        //berarti promo tidak aktif di lokasi tersebut
        //jika ditemukan lakukan injeksi harga dan stok toko
        if($promos==null){
            $promos=new Promotions;
            $promos->promo_no="NOTPROMO";
        }else{
            foreach ($promos as $promo) {
                foreach ($promo->products as $prod) {
                    $productid=$prod->product_id;
                    $product=ProductServices::getProductStoreByProductID($storeid,$productid);
                    $prod->product_price=$product->product_price;
                    $prod->product_stock=$product->product_stock;
                }
                
            }
        }

        return response()->json($promos);
    }

    //reset masa aktif promo, antisipasi jika user tidak pernah login de dashboard
    //jadi setiap store login dia langsung lakukan reset jika sudah melewati masa
    //waktu aktif promo
    public function updatePromoState($compid){
        $products=ProductServices::updatePromoState($compid); 
        return response()->json("SUCCESS");
    }
}
