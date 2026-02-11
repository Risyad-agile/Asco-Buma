<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Products;
use Session;
use DB;

class ProductServices
{
    //--konsep baru pengambilan produk
    public static function getProductByStoreId($storeid,$stockstate){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
            ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
            ->join('brands','brands.brand_id','=','products.brand_id')
            ->select('products.product_id','products.product_plu','products.product_desc',
            'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
            'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
            'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
            'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
            'products.product_promostate','products.product_file_loc','brands.brand_name')
            ->where([['product_stores.store_id','=',$storeid],['products.product_stockstate','=',$stockstate],
            ['products.product_state','!=','0']])->get();
        return $products;
    }
    public static function getProductOnTheStore($store_id){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')
                ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])->get();
        return $products;
    }
    public static function getProductStoreResto($storeid){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
            ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
            ->join('brands','brands.brand_id','=','products.brand_id')
            ->select('products.product_id','products.product_plu','products.product_desc',
            'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
            'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
            'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
            'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
            'products.product_promostate','products.product_file_loc','brands.brand_name')
            ->where([['product_stores.store_id','=',$storeid],['products.product_state','!=','0']])
            ->whereIn('products.product_state',array('2','3'))->get();
        return $products;
    }
    public static function getProductPayment($storeid){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
            ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
            ->join('brands','brands.brand_id','=','products.brand_id')
            ->select('products.product_id','products.product_plu','products.product_desc',
            'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
            'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
            'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
            'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
            'products.product_promostate','products.product_file_loc','brands.brand_name')
            ->where([['product_stores.store_id','=',$storeid],['products.product_state','!=','0']])
            ->whereIn('products.product_state',array('4'))->get();
        return $products;
    }
//--konsep awal pengambilan produk
    public static function getProductAllByCompany($comp_id){
        $products=DB::table('products') 
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','products.product_file_loc', 
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','brands.brand_name')
                ->orderBy('products.prodcat_id')
                ->where([['products.comp_id','=',$comp_id],['products.product_state','!=','0']])->get();
        return $products;
    }
    //data produk dari tabel product stores disajikan berdasarkan kode store yang dikirim
    public static function getProductStoresByStoreId($store_id){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')->whereIn('products.product_state',array('1','2'))
                ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])->get();
        return $products;
    }
    //stok lebih dari nol 
    public static function getProductReadyStoreByStoreId($store_id){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')
                ->where('product_stores.product_stock','>',0)
                ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])->get();
        return $products;
    }
    //produk dengan dengan state tidak sama dengan 3 
    public static function getProductUnHideByStoreId($store_id){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')
                ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','3'],
                ['products.product_state','!=','0']])->get();
        return $products;
    }

    public static function getProductReadytStoreByCriteria($store_id,$kriteria){
        if ($kriteria=="a") {
            $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                    ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                    ->join('brands','brands.brand_id','=','products.brand_id')
                    ->select('products.product_id','products.product_plu','products.product_desc',
                    'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                    'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                    'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                    'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                    'products.product_promostate','products.product_file_loc','brands.brand_name')
                    ->where(function($query){
                            $query->where('product_stores.product_stock','>',0)
                            ->orWhere('products.product_stockstate','=','0');
                    })
                    ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])->take(100)->get();
        }else{
            $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')
                ->where(function($query){
                        $query->where('product_stores.product_stock','>',0)
                        ->orWhere('products.product_stockstate','=','0');
                })
                ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])
                ->where(function ($query) use($kriteria){
                        $query->where('products.product_id','like','%'.$kriteria.'%') 
                                ->orWhere('products.product_desc','like','%'.$kriteria.'%')
                                ->orWhere('products.product_barcode','like','%'.$kriteria.'%');
                        }
                )->get();
        }
     
    //   if ($kriteria=="ALL") {
    //     $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
    //             ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
    //             ->join('brands','brands.brand_id','=','products.brand_id')
    //             ->select('products.product_id','products.product_plu','products.product_desc',
    //             'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
    //             'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
    //             'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
    //             'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
    //             'products.product_promostate','products.product_file_loc','brands.brand_name')
    //             ->where(function($query){
    //                     $query->where('product_stores.product_stock','>',0)
    //                     ->orWhere('products.product_stockstate','=','0');
    //             })
    //             ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])->get();
    //   }
      return $products;
    }

    //mendapatkan data produk per toko berdasarkan kode produk
    public static function getProductStoreByProductID($store_id,$product_id){
      $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')
                ->where([['product_stores.store_id',$store_id],['products.product_id',$product_id]])
                // ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])
                // ->where('products.product_id','=',$product_id)
                ->first();
      return $products;
  }

    //pencarian produk berdasarkan perusahaan dan kriteria
    public static function getProductStoreByCriteria($store_id,$kriteria){
        $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')
                ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])
                ->where(function ($query) use($kriteria){
                        $query->where('products.product_id','like','%'.$kriteria.'%') 
                              ->orWhere('products.product_desc','like','%'.$kriteria.'%')
                              ->orWhere('products.product_barcode','like','%'.$kriteria.'%');
                        }
                
                )->get();
        if ($kriteria=="ALL") {
          $products=DB::table('product_stores')->join('products','product_stores.product_id','=','products.product_id')
                ->join('product_categories','product_categories.prodcat_id','=','products.prodcat_id')
                ->join('brands','brands.brand_id','=','products.brand_id')
                ->select('products.product_id','products.product_plu','products.product_desc',
                'products.product_shortdesc','products.comp_id','products.brand_id','products.prodcat_id',
                'products.product_barcode','products.product_buy_price','product_stores.product_buffer_stock',
                'product_stores.product_stock','product_stores.store_id','product_stores.product_price',
                'products.product_stockstate','products.product_state','product_categories.prodcat_desc',
                'products.product_promostate','products.product_file_loc','brands.brand_name')
                ->where([['product_stores.store_id','=',$store_id],['products.product_state','!=','0']])->get();
        }
        return $products;
      }
}