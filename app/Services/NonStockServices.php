<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Products;
use App\Models\NonStocks;
use App\Models\StockCards;
use App\Services\ProductServices;
use Carbon\Carbon;
use Session;
use DB;

class NonStockServices
{
    public function __construct()
    {
        
    }
    public static function updateProductNonStock($store_id,$product_id,$prodqty)
    {
        // $product_stock=ProductServices::getProductStoreByProductID($store_id,$product_id)->product_stock;

        $product_stock=0;
        $productselected=ProductServices::getProductStoreByProductID($store_id,$product_id);
        if(isset($productselected)){
            $product_stock=$productselected->product_stock;
        }
        $newqty=$product_stock+$prodqty;
 
        // update stok di tabel produk store
        $products=Products::where('product_id','=',$product_id)->first();
        $products->stores()->updateExistingPivot($store_id,['product_stock'=>$newqty]);
    }

    public static function saveNonStock($username,$store_id,$nonstock){
        $nonstocks=new NonStocks;
        $nsservice=new NonStockServices;
       
        $autono=$nsservice->autoNonStockNo($store_id);

        $nonstocks->nonstock_no=$autono;

        $nonstocks->sale_no=$nonstock[0]['sale_no'];
        $nonstocks->store_id=$store_id;
        $nonstocks->product_id=$nonstock[0]['product_id'];
        $nonstocks->nonstock_reff=$nonstock[0]['ns_reff'];
        $nonstocks->nonstock_date=$nonstock[0]['ns_date'];
        $nonstocks->nonstock_price=$nonstock[0]['ns_price'];
        $nonstocks->nonstock_fee=$nonstock[0]['ns_fee'];
        $nonstocks->nonstock_qty=$nonstock[0]['ns_qty'];
        $nonstocks->nonstock_cancel='0';
        $nonstocks->save();

        $nsservice->updateStockCard($username,$autono,$store_id,$nonstock);
    }


    public static function updateStockCard($username,$autono,$store_id,$nonstock)
    {
        $stockcard=new StockCards;
        $stockcard->product_id=$nonstock[0]['product_id'];
        $stockcard->trans_no=$autono;
        $stockcard->username=$username;
        $stockcard->trans_date=$nonstock[0]['ns_date'];
        $stockcard->stock_qty=$nonstock[0]['ns_qty'];
        $stockcard->stock_info="Penambahan Non Stok :".$autono;
        $stockcard->stat_input="1";
        $stockcard->save();
    }

    public function autoNonStockNo($store_id)
    {
        #note : proses ini mengakibatkan overload karena menghingtung seluruh non stok
        #di toko yang dipilih 15-04-2022
        #perlu di pertimbangkan lagi apakah masih di perlukan
        
        // aturan nomor Non Stok: storeid-NOSTyymm1234
        $bulan=date("m");
        $tahun=date("y");
        $strNewId = $store_id.'-NOST'.$tahun.$bulan."0001";

       
        // kalau belum ada record sebelumnya berarti ini yang pertama
        if(NonStocks::where('store_id','=',$store_id)->latest()->take(5)->get()->count()==0){
            return $strNewId;
        }

    
        
        $strLastId=NonStocks::where('store_id','=',$store_id)->latest('nonstock_no')->first()->nonstock_no;
        $intNewId= substr($strLastId,-4)+1;

        $strNewId=strlen($intNewId);
        switch (strlen($intNewId)) {
            case 1:
                $strNewId=$store_id.'-NOST'.$tahun.$bulan.'000'.$intNewId;
                break;
            case 2:
                $strNewId=$store_id.'-NOST'.$tahun.$bulan.'00'.$intNewId;
                break;
            case 3:
                $strNewId=$store_id.'-NOST'.$tahun.$bulan.'0'.$intNewId;
                break;
            case 4:
                $strNewId=$store_id.'-NOST'.$tahun.$bulan.$intNewId;
                break;
  
        }


        return $strNewId;
    }       

}
