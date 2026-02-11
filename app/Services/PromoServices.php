<?php

namespace App\Services;
use App\Models\Promotions; 
use App\Models\Stores; 
use App\Models\Products; 
 

class PromoServices
{
    public static function updatePromoState($compid)
    {
        //UPDATE 04-07-19
        //memungkinkan ada beberapa promo dengan produk yang sama di tanggal yang sama
        //promo tersebut harus di non aktifkan
        //namun memungkinkan juga ada promo dengan beberapa produk yang sama, namun
        //tanggal berbeda, untuk kasus ini yang di non aktifkan hanya di tanggal yang sama

        $tgl=date("Y-m-d"); 
        $promos=Promotions::with('products')->where('comp_id','=',$compid)
                ->whereDate('promo_date_end',"<",$tgl)->get();

        foreach ($promos as $key => $promo) {
            $promo_no=$promo->promo_no;
            $promo_type=$promo->promo_type;

            if($promo_type!='9'){  //promo type 9 adalah paket, tidak perlu non aktifkan
                Promotions::where('promo_no','=',$promo_no)->update(['promo_state'=>'0']); //non aktif promo

                $stores=Stores::where('comp_id','=',$compid)->get();
                foreach ($stores as $key => $store) {
                    $store_id=$store->store_id;
                    // $promo->stores()->updateExistingPivot($store_id,['promo_store_state'=>'0']); //non aktif promo di toko
                    //hapus berdasarkan promo pada tabel promo store
                    $promo->stores()->detach(); 
                }
            }
            
            //kembalikan status produk ke not promo, kecuali produk yang masih berada di promo yang
            //masih aktif
            foreach ($promo->products as $product) {
                $productid=$product->product_id;
                if(PromoServices::isProductPromo($productid)==false){
                    Products::where('product_id','=',$productid)
                    ->update([
                        'product_promostate'=>"0",
                    ]);
                }
            }
        }

        return "SUCCESS";
    }

    public static function isProductPromo($productid){
        // untuk memeriksa apakah produk yang di non aktifkan masih terdapat di promo lain
        // yang masih aktif
        $promo=Promotions::with('products')->where('promo_state','=','1')
        ->whereHas('products',function($query) use ($productid)
            { 
                $query->where('products.product_id','=',$productid);
            })->first();

        if($promo==null){
            return false;
        }

        return true;
    }

    public static function getProductPromo($compid, $productid){
        $promos=Promotions::with('products')
            ->where('promo_state','=','1')
            ->whereIn('promo_type', ['1', '2']) 
            ->where('comp_id','=',$compid)
            ->whereHas('products',function($query) use ($productid)
                { 
                    $query->where('products.product_id','=',$productid);
                })->get(); 
                // ->pluck('name');
        return $promos;
    }
}
