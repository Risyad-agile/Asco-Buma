<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\StockCards;
use App\Services\ProductServices;
use Carbon\Carbon;
use Session;
use DB;

class StockCardServices
{

    public static function updateProductStock($store_id,$product,$stat)
    {
        for ($i=0; $i < count($product); $i++) {
            $product_id=$product[$i]['product_id'];
            $stock_qty=$product[$i]['stock_qty'];   
          
            // $product_stock=ProductServices::getProductStoreByProductID($store_id,$product_id)->product_stock;
            $product_stock=0;
            $productselected=ProductServices::getProductStoreByProductID($store_id,$product_id);
            if(isset($productselected)){
              $product_stock=$productselected->product_stock;
            }

            $newstock=0;
            if($stat=="ADD"){
              $newstock=$product_stock+$stock_qty; //add new stock
            }else{
              $newstock=$product_stock-$stock_qty; //substract current stock
            }

               // update stok di tabel produk store
            $products=Products::where('product_id','=',$product_id)->first();
            $products->stores()->updateExistingPivot($store_id,['product_stock'=>$newstock]);
        }
    }

    public static function updateStockCard($username,$product,$stat)
    {
      for ($i=0; $i < count($product); $i++) {
          $prodid=$product[$i]['product_id'];
          $qty=$product[$i]['stock_qty'];
          $transno=$product[$i]['trans_no']; 

          $stockcard=new StockCards;
          $stockcard->product_id=$prodid;
          $stockcard->trans_no=$transno;
          $stockcard->username=$username;
          $stockcard->stock_qty=$qty;
          switch ($stat) {
            case 'RECEIVE':
              $stockcard->stock_info="Penerimaan Supplier(PO) dengan No Terima :".$transno;
              $stockcard->stat_input="1"; //stok masuk
              break;
            case 'RECEIVECANCEL':
              $stockcard->stock_info="Pembatalan Penerimaan Supplier(PO) dengan No Terima :".$transno;
              $stockcard->stat_input="2"; //stok keluar
              break;  
            case 'RETUR':
              $stockcard->stock_info="Retur Supplier(PO) dengan No Retur :".$transno;
              $stockcard->stat_input="2"; //stok keluar
              break;
            case 'RETURCANCEL':
              $stockcard->stock_info="Pembatalan Retur Supplier(PO) dengan No Retur :".$transno;
              $stockcard->stat_input="1"; //stok masuk
              break;
            case 'MUTIN':
              $stockcard->stock_info="Penerimaan antar Toko dengan No Mutasi :".$transno;
              $stockcard->stat_input="1"; //stok masuk
              break;
            case 'MUTINCANCEL':
              $stockcard->stock_info="Pembatalan Mutasi Terima dengan No Mutasi :".$transno;
              $stockcard->stat_input="2"; //stok keluar 
              break;
            case 'MUTOUT':
              $stockcard->stock_info="Mutasi Keluar dengan No Mutasi :".$transno;
              $stockcard->stat_input="2"; //stok keluar
              break;
            case 'MUTOUTCANCEL':
              $stockcard->stock_info="Pembatalan Mutasi Keluar dengan No Mutasi :".$transno;
              $stockcard->stat_input="1"; //stok masuk
              break;  
            case 'CONS':
              $stockcard->stock_info="Mutasi Keluar Konsinyasi dengan No Mutasi :".$transno;
              $stockcard->stat_input="2"; //stok keluar
              break;
            case 'CONSRETURN':
              $stockcard->stock_info="Retur Konsinyasi(Terjual) dengan No Mutasi :".$transno;
              $stockcard->stat_input="1"; //stok masuk
              break; 
            case 'CONSMUTIN':
              $stockcard->stock_info="Mutasi Terima Konsinyasi(SALES) dengan No Mutasi :".$transno;
              $stockcard->stat_input="1"; //stok masuk
              break;  
            case 'SALES':
              $stockcard->stock_info="Transaksi Penjualan dengan Nomor Penjualan :".$transno;
              $stockcard->stat_input="2"; //stok keluar 
              break;
            case 'SALESCANCEL':
              $stockcard->stock_info="Pembatalan Transaksi Penjualan dengan Nomor Penjualan :".$transno;
              $stockcard->stat_input="1"; //stok masuk
              break;
            case 'OFFMEMADD':
              $stockcard->stock_info="Penyesuaian Penambahan Berita Acara No :".$transno;
              $stockcard->stat_input="2"; //stok masuk
              break;
            case 'OFFMEMSUB':
              $stockcard->stock_info="Penyesuaian Pengurangan Berita Acara No :".$transno;
              $stockcard->stat_input="1"; //stok keluar
              break;  
          }
          $stockcard->save();
      }


    }

}
