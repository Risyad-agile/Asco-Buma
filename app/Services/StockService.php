<?php

namespace App\Services;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\Stores;
use App\Models\Receives;
use App\Models\Returns;
use App\Models\MutationIns;
use App\Models\MutationOuts;
use App\Models\Sales;
use App\Models\OfficialMemos;
use DB;

class StockService 
{
    # MASIH DIGUNAKAN DI KIDSWA FARMA
    #---------------------------------------------------------------------------------------
    #perhitungan stok masuk di Head Office untuk seluruh store di dalam Perusahaan yang sama
    #---------------------------------------------------------------------------------------
    public function stockCountIn($compid,$tglAwal,$tglAkhir){
        $products=Products::where([['product_stockstate','1'],['product_state','!=','0'],
                ['comp_id',$compid]])->get();

        // inisialisasi value
        foreach ($products as $key => $product) {
            $product->product_stock_purchase=0;
            $product->product_stock_retur=0;
            $product->product_stock_mutation_in=0;
            $product->product_stock_mutation_out=0;
            $product->product_stock_sales=0;
            $product->product_stock_offmemo_plus=0;
            $product->product_stock_offmemo_minus=0;
        };

        $storeids=Stores::where('comp_id',$compid)->pluck('store_id');
        $receiveproduct=$this->countPurchase($storeids,$tglAwal,$tglAkhir);
        $mutinproduct=$this->countMutationIn($storeids,$tglAwal,$tglAkhir);
        $offmemoproduct=$this->countOffMemo($storeids,$tglAwal,$tglAkhir,'I'); //berita acara masuk

        foreach ($products as $key => $product) {
            foreach ($receiveproduct as $key => $rcvprod) {
                if($product->product_id==$rcvprod->product_id){
                    $product->product_stock_purchase=$rcvprod->product_qty; 
                }
            }
        }

   
        foreach ($products as $key => $product) {
            foreach ($mutinproduct as $key => $mutinprod) {
                if($product->product_id==$mutinprod->product_id){
                    $product->product_stock_mutation_in=$mutinprod->product_qty; 
                }
            }
        }

        foreach ($products as $key => $product) {
            foreach ($offmemoproduct as $key => $offmemoprod) {
                if($product->product_id==$offmemoprod->product_id){
                    $product->product_stock_offmemo_plus=$offmemoprod->product_qty; 
                }
            }
        }
        return $products;
    }
    #perhitungan stok keluar
    public function stockCountOut($compid,$tglAwal,$tglAkhir){
        $products=Products::where([['product_stockstate','1'],['product_state','!=','0'],
        ['comp_id',$compid]])->get();

        // inisialisasi value
        foreach ($products as $key => $product) {
            $product->product_stock_purchase=0;
            $product->product_stock_retur=0;
            $product->product_stock_mutation_in=0;
            $product->product_stock_mutation_out=0;
            $product->product_stock_sales=0;
            $product->product_stock_offmemo_plus=0;
            $product->product_stock_offmemo_minus=0;
        };

        $storeids=Stores::where('comp_id',$compid)->pluck('store_id');
        $returnproduct=$this->countReturn($storeids,$tglAwal,$tglAkhir);
        $mutoutproduct=$this->countMutationOut($storeids,$tglAwal,$tglAkhir);
        $saleproduct=$this->countSales($storeids,$tglAwal,$tglAkhir);
        $offmemoproduct=$this->countOffMemo($storeids,$tglAwal,$tglAkhir,'O'); //berita acara keluar
 
        foreach ($products as $key => $product) {
            foreach ($returnproduct as $key => $rtnprod) {
                if($product->product_id==$rtnprod->product_id){
                    $product->product_stock_retur=$rtnprod->product_qty; 
                }
            }
        }
        foreach ($products as $key => $product) {
            foreach ($mutoutproduct as $key => $mutoutprod) {
                if($product->product_id==$mutoutprod->product_id){
                    $product->product_stock_mutation_out=($mutoutprod->product_qty); 
                }
            }
        }
        foreach ($products as $key => $product) {
            foreach ($saleproduct as $key => $saleprod) {
                if($product->product_id==$saleprod->product_id){
                    $product->product_stock_sales=($saleprod->product_qty); 
                }
            }
        }
        foreach ($products as $key => $product) {
            foreach ($offmemoproduct as $key => $offmemoprod) {
                if($product->product_id==$offmemoprod->product_id){
                    $product->product_stock_offmemo_minus=($offmemoprod->product_qty); 
                }
            }
        }

        return $products;
    }

    #---------------------------------------------------------------------------------------
    #perhitungan stok masuk di Store untuk store yang aktif
    #---------------------------------------------------------------------------------------
    public function stockCountStoreIn($storeid,$prodcatid,$tglAwal,$tglAkhir){
        $store=function ($query) use ($storeid){
            $query->where('stores.id',$storeid);
        };
        $products=Products::where([['product_stock_state','1'],['product_state','!=','0'],['prodcat_id',$prodcatid]])
            ->whereHas('stores',$store)->get();

        // inisialisasi value
        foreach ($products as $key => $product) {
            $product->product_stock_purchase=0;
            $product->product_stock_retur=0;
            $product->product_stock_mutation_in=0;
            $product->product_stock_mutation_out=0;
            $product->product_stock_sales=0;
            $product->product_stock_offmemo_plus=0;
            $product->product_stock_offmemo_minus=0;
        };

        $storeid=array($storeid);
        $receiveproduct=$this->countPurchase($storeid,$tglAwal,$tglAkhir);
        $mutinproduct=$this->countMutationIn($storeid,$tglAwal,$tglAkhir);
        $offmemoproduct=$this->countOffMemo($storeid,$tglAwal,$tglAkhir,'I'); //berita acara masuk
      
        foreach ($products as $key => $product) {
            foreach ($receiveproduct as $key => $rcvprod) {
                if($product->id==$rcvprod->product_id){
                    $product->product_stock_purchase=$rcvprod->product_qty; 
                }
            }
        }

        foreach ($products as $key => $product) {
            foreach ($mutinproduct as $key => $mutinprod) {
                if($product->id==$mutinprod->product_id){
                    $product->product_stock_mutation_in=$mutinprod->product_qty; 
                }
            }
        }

        foreach ($products as $key => $product) {
            foreach ($offmemoproduct as $key => $offmemoprod) {
                if($product->id==$offmemoprod->product_id){
                    $product->product_stock_offmemo_plus=$offmemoprod->product_qty; 
                }
            }
        }
        return $products;
    }
    #perhitungan stok keluar
    public function stockCountStoreOut($storeid,$prodcatid,$tglAwal,$tglAkhir){
        $store=function ($query) use ($storeid){
            $query->where('stores.id',$storeid);
        };
        $products=Products::where([['product_stock_state','1'],['product_state','!=','0'],['prodcat_id',$prodcatid]])
            ->whereHas('stores',$store)->get();

        // inisialisasi value
        foreach ($products as $key => $product) {
            $product->product_stock_purchase=0;
            $product->product_stock_retur=0;
            $product->product_stock_mutation_in=0;
            $product->product_stock_mutation_out=0;
            $product->product_stock_sales=0;
            $product->product_stock_offmemo_plus=0;
            $product->product_stock_offmemo_minus=0;
        };
        
        $storeid=array($storeid);
        $returnproduct=$this->countReturn($storeid,$tglAwal,$tglAkhir);
        $mutoutproduct=$this->countMutationOut($storeid,$tglAwal,$tglAkhir);
        $saleproduct=$this->countSales($storeid,$tglAwal,$tglAkhir);
        $offmemoproduct=$this->countOffMemo($storeid,$tglAwal,$tglAkhir,'O'); //berita acara keluar

        foreach ($products as $key => $product) {
            foreach ($returnproduct as $key => $rtnprod) {
                if($product->id==$rtnprod->product_id){
                    $product->product_stock_retur=$rtnprod->product_qty; 
                }
            }
        }
        foreach ($products as $key => $product) {
            foreach ($mutoutproduct as $key => $mutoutprod) {
                if($product->id==$mutoutprod->product_id){
                    $product->product_stock_mutation_out=($mutoutprod->product_qty); 
                }
            }
        }
        foreach ($products as $key => $product) {
            foreach ($saleproduct as $key => $saleprod) {
                if($product->id==$saleprod->product_id){
                    $product->product_stock_sales=($saleprod->product_qty); 
                }
            }
        }
        foreach ($products as $key => $product) {
            foreach ($offmemoproduct as $key => $offmemoprod) {
                if($product->id==$offmemoprod->product_id){
                    $product->product_stock_offmemo_minus=($offmemoprod->product_qty); 
                }
            }
        }

        return $products;
    }

    #---------------------------------------------------------------------------------------
    #perhitungan stok per proses
    #---------------------------------------------------------------------------------------
    private function countPurchase($storeids,$tglAwal,$tglAkhir){
        // dd($storeids);
        // dd(count($storeids));
        if(count($storeids)==1){ //jika hanya satu store
            $receiveids=Receives::where([['receive_state','1'],['store_id',$storeids]])
                ->whereBetween('receive_date', [$tglAwal, $tglAkhir])
                ->pluck('id');    
        }else{
            $receiveids=Receives::whereIn('store_id',$storeids)
                ->where('receive_state','1')
                ->whereBetween('receive_date', [$tglAwal, $tglAkhir])
                ->pluck('id');  
        }

        $receiveproduct=DB::table('receive_products')
            ->select('product_id', DB::raw('SUM(receive_product_qty) AS product_qty'))
            ->whereIn('receive_id',$receiveids)
            ->groupBy('product_id')->get();
        return $receiveproduct ;
    }

    public function countReturn($storeids,$tglAwal,$tglAkhir){ 
        if(count($storeids)==1){ //jika hanya satu store
            $returids=Returns::where([['retur_state','1'],['store_id',$storeids]])
                ->whereBetween('retur_date', [$tglAwal, $tglAkhir])
                ->pluck('id');      
        }else{
            $returids=Returns::whereIn('store_id',$storeids)
                ->where('retur_state','1')
                ->whereBetween('retur_date', [$tglAwal, $tglAkhir])
                ->pluck('id');   
        }
        $returproduct=DB::table('return_products')
            ->select('product_id', DB::raw('SUM(retur_product_qty) AS product_qty'))
            ->whereIn('retur_id',$returids)
            ->groupBy('product_id')->get();
        return $returproduct;
    }
    public function countMutationIn($storeids,$tglAwal,$tglAkhir){ 
        if(count($storeids)==1){ //jika hanya satu store
            $mutinids=MutationIns::where([['mutin_state','1'],['store_id',$storeids]])
                ->where('mutin_state','1')
                ->whereBetween('mutin_date', [$tglAwal, $tglAkhir])
                ->pluck('id');   
        }else{
            $mutinids=MutationIns::whereIn('store_id',$storeids)
                ->where('mutin_state','1')
                ->whereBetween('mutin_date', [$tglAwal, $tglAkhir])
                ->pluck('id');  
        }    
        $mutinproduct=DB::table('mutationin_products')
            ->select('product_id', DB::raw('SUM(mutin_product_qty) AS product_qty'))
            ->whereIn('mutin_id',$mutinids)
            ->groupBy('product_id')->get();
        return $mutinproduct;
    }
    public function countMutationOut($storeids,$tglAwal,$tglAkhir){ 
        if(count($storeids)==1){ //jika hanya satu store
            $mutoutids=MutationOuts::where([['mutout_state','1'],['store_id',$storeids]])
                ->whereBetween('mutout_date', [$tglAwal, $tglAkhir])
                ->pluck('id');   
        }else{
            $mutoutids=MutationOuts::whereIn('store_id',$storeids)
                ->where('mutout_state','1')
                ->whereBetween('mutout_date', [$tglAwal, $tglAkhir])
                ->pluck('id');   
        }    
        $mutoutproduct=DB::table('mutationout_products')
            ->select('product_id', DB::raw('SUM(mutout_product_qty) AS product_qty'))
            ->whereIn('mutout_id',$mutoutids)
            ->groupBy('product_id')->get();
        return $mutoutproduct;
    }
    public function countSales($storeids,$tglAwal,$tglAkhir){        
        if(count($storeids)==1){ //jika hanya satu store
            $salesids=Sales::where([['sale_state','1'],['store_id',$storeids]])
                ->where('sale_state','1')
                ->whereBetween('sale_date', [$tglAwal, $tglAkhir])
                ->pluck('id'); 
        }else{
            $salesids=Sales::whereIn('store_id',$storeids)
                ->where('sale_state','1')
                ->whereBetween('sale_date', [$tglAwal, $tglAkhir])
                ->pluck('id');  
        }     
        $saleproduct=DB::table('sale_products')
            ->select('product_id', DB::raw('SUM(sale_product_qty) AS product_qty'))
            ->whereIn('sale_id',$salesids)
            ->groupBy('product_id')->get();
        return $saleproduct;
    }
    public function countOffMemo($storeids,$tglAwal,$tglAkhir,$state){          
        if(count($storeids)==1){ //jika hanya satu store
            $offmemoids=OfficialMemos::where([['offmem_state','1'],['store_id',$storeids]])
                ->whereBetween('offmem_date', [$tglAwal, $tglAkhir])
                ->pluck('id');   
        }else{
            $offmemoids=OfficialMemos::whereIn('store_id',$storeids)
                ->where('offmem_state','1')
                ->whereBetween('offmem_date', [$tglAwal, $tglAkhir])
                ->pluck('id');  
        } 
        $offmemoprod=DB::table('officialmemo_products')
            ->select('product_id', DB::raw('SUM(offmem_product_qty) AS product_qty'))
            ->where('offmem_product_state',$state)
            ->whereIn('offmem_id',$offmemoids)
            ->groupBy('product_id')->get();
        return $offmemoprod;
    }

}
