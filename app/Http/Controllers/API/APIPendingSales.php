<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stores;
use App\Models\PendingSales;  
use App\Models\pendingsale_products;
use App\Models\Spots;
use App\Models\Products;
use App\Models\ProductStores;
use App\Models\ProductGroups;
use App\Http\Controllers\PendingSalesController;
use App\Http\Controllers\SpotsController;
use App\Services\MemberServices;
use DB;


class APIPendingSales extends Controller
{
    public function savePendingSales(Request $request){
        $memberserv=new MemberServices;

        $storeid=$request->input('store_id');
        $pendingnote=$request->input('pending_note');
        $pendingno=$request->input('pending_no');
        if($pendingno==""){ //pending baru
            $pendSalesControl=new PendingSalesController;
            $pendingno=$pendSalesControl->autoPendingSalesNo($storeid);
        }

        // 1. hapus data pending sebelumnya
        $pendingsales=PendingSales::where([['pending_no',$pendingno],['store_id',$storeid]])->delete();

        // 2. masukan data pending yang baru
        $pendingsales=new PendingSales;
        $pendingsales->pending_no=$pendingno;
        $pendingsales->store_id=$storeid;
        $pendingsales->member_no=$request->member_no;
        $pendingsales->pending_note=$pendingnote;
        $pendingsales->pending_state='1';
        $pendingsales->pending_date=date("Y-m-d H:i:s");
        $pendingsales->save();        

        //3. simpan produk
        for ($i=0; $i < count($request->input('pendingsale_products')) ; $i++) {
            $product_id=$request->input('pendingsale_products')[$i]['product_id'];
            $prodqty=$request->input('pendingsale_products')[$i]['pending_sale_product_qty'];
            $prodprice=$request->input('pendingsale_products')[$i]['pending_sale_product_price'];
            $prodbuyprice=$request->input('pendingsale_products')[$i]['pending_sale_product_buy_price'];
            $proddisc=$request->input('pendingsale_products')[$i]['pending_sale_product_disc'];        
            $prodnote=$request->input('pendingsale_products')[$i]['pending_sale_product_note'];  
            $prodstate=$request->input('pendingsale_products')[$i]['pending_sale_product_state'];  
            $prodtotal=$prodqty*$prodprice;
            $products=Products::where('id',$product_id)->first();
            $pendingsales->products()->attach([
                        'product_id'=>$product_id],
                        [
                            'pending_id'=>$pendingsales->id,
                            'pending_sale_product_qty'=>$prodqty,
                            'pending_sale_product_price'=>$prodprice,
                            'pending_sale_product_buy_price'=>$prodbuyprice,
                            'pending_sale_product_disc'=>$proddisc,
                            'pending_sale_product_total'=>$prodtotal,
                            'pending_sale_product_note' =>$prodnote,
                            'pending_sale_product_state' =>$prodstate,
                        ]);
        }
         // 4. simpan member GUEST
        $memberserv->createMemberGuest($storeid);
        $pendingsales=PendingSales::with('store.company','products')->where('id',$pendingsales->id)->first();
        return response()->json($pendingsales);        
    }

    public function getPendingSalesListByStore($storeid){
        $store=function ($query) use ($storeid){
            $query->where('stores.id',$storeid);
        };

        $products=function ($query) use ($store){
            $query->with(['stores'=>$store])->whereHas('stores',$store);
        };
        $pendingsales=PendingSales::with(['products'=>$products,'store'=>$store])
            ->where('store_id',$storeid)->whereHas('products',$products)
            ->where('pending_state','1')->orderby(DB::raw('created_at'),'asc')->get();

        //inject stok
        foreach ($pendingsales as $key => $pendingsale) {
            # code...
            $products=$pendingsale->products;
            foreach ($products as $key => $product) { 
                $product->product_buy_price=$product->stores[0]['product_stores']['product_buy_price'];
                $product->product_price=$product->stores[0]['product_stores']['product_price'];
                $product->product_stock=$product->stores[0]['product_stores']['product_stock'];
            }
        }


        return response()->json($pendingsales);
    }

    public function getPendingSalesById($pendingid){
        $storeid=PendingSales::where('id',$pendingid)->first()->store_id;
        $store=function ($query) use ($storeid){
            $query->where('stores.id',$storeid);
        };

        $products=function ($query) use ($store){ 
            $query->with(['stores'=>$store])->whereHas('stores',$store);
        };

        $pendingsale=PendingSales::with(['products'=>$products,'store'=>$store])
                ->where([['id',$pendingid],['pending_state','!=','0']])
                ->whereHas('products',$products)->first();
        //inject stok
        $products=$pendingsale->products;
        foreach ($products as $key => $product) { 
            $product->product_buy_price=$product->stores[0]['product_stores']['product_buy_price'];
            $product->product_price=$product->stores[0]['product_stores']['product_price'];
            $product->product_stock=$product->stores[0]['product_stores']['product_stock'];
        }
        
        return response()->json($pendingsale);
    }


    public function getProductPendingSalesUnprintByStoreId($storeid){
        $products=PendingSales::with('spots')->where('store_id',$storeid)
            ->first()->products()->wherePivot('pending_sale_product_printed','0')->get();

        // return response()->json($pendingsales);

        // $pendingsales=function ($query) use ($storeid){
        //     $query->with('spots')->where('store_id',$storeid);
        // };

        // $products=Products::with(['pendingsales'=>$pendingsales,'productcats'])->whereHas('pendingsales',$pendingsales)
        // ->orderBy('products.product_id')->get();
        // $products=Products::with(['pendingsales'=>$pendingsales,'productcats'])->whereHas('pendingsales',$pendingsales)
        // ->orderBy('products.product_id')->first()->pendingsales()->wherePivot('pending_sale_product_printed','0')->get();
        // $products=Products::with(['productcats'])->whereHas('pendingsales',$pendingsales)
        //           ->orderBy('products.product_id')->get();

        //untuk di pivot where agar dapat object single harus diawali single record makaya pake first()
        // $pendingsales=PendingSales::where('store_id',$storeid);
        // $products=$pendingsales->first()->products()->wherePivot('pending_sale_product_printed','0')->get();
        return response()->json($products);
    }
    public function getProductPendingSalesByStoreWarehouse($storeid,$warehouseid){
        $pendingsales=function ($query) use ($storeid){
            $query->with('spots')->where('store_id',$storeid);
        };
        $prodcats=function ($query) use ($warehouseid){
            $query->with('warehouses')->where('warehouse_id',$warehouseid);
        };
        $products=Products::with(['pendingsales'=>$pendingsales,'productcats'=>$prodcats])
                  ->whereHas('pendingsales',$pendingsales)->whereHas('productcats',$prodcats)
                  ->orderBy('products.product_id')->get();

        return response()->json($products);
    }

    public function updatePendingSalesId(Request $request, $pendingno){ // khusus PUT di postman harus pake xwwwform
        $newpendingno=$request->input('pending_no');
        Spots::where('spot_id','=',$pendingno)->update(['spot_state'=>"1"]); // yang lama jadi available
        Spots::where('spot_id','=',$newpendingno)->update(['spot_state'=>"3"]); //yang baru jadi in use
        PendingSales::where('pending_no','=',$pendingno)
        ->update(['pending_no'=>$newpendingno,
                  'spot_id'=>$newpendingno]);

        return response()->json("SUCCESS");
    }
    public function updatePendingSalesState(Request $request, $pendingno){
        $currentpendingstate=PendingSales::where('pending_no',$pendingno)->first()->pending_state;
        if($currentpendingstate=='3'){
            $this->deletePendingSales($pendingno);
        }else{
            $pendingstate=$request->pending_state;
            PendingSales::where('pending_no',$pendingno)->update(['pending_state'=>$pendingstate]);
        }

        return response()->json("SUCCESS");
    }
    public function updatePendingSalesProductState(Request $request, $pendingno){
        $pendingsales=PendingSales::where('pending_no','=',$pendingno)->first();

        for ($i=0; $i < count($request->input('products')) ; $i++) {
            $productid=$request->input('products')[$i]['product_id']; 
            $pendprodstate=$request->input('products')[$i]['pendingsale_products']['pending_sale_product_state'];
            $pendingsales->products()->updateExistingPivot($productid,['pending_sale_product_state'=>$pendprodstate]);
        }

        $pendingsales=PendingSales::with('products')->where('pending_no','=',$pendingno)->first();
        return response()->json($pendingsales);
    }
    public function updateProductPendingSalesPrintedState(Request $request){
        $storeid="";
        for ($i=0; $i < count($request->input('products')) ; $i++) {
            $productid=$request->input('products')[$i]['product_id']; 
            $pendingsales=$request->input('products')[$i]['pendingsales']; 
            $products=Products::where('product_id',$productid)->first();
            for ($j=0; $j < count($pendingsales) ; $j++) {
                $storeid=$pendingsales[$j]['store_id']; 
                $pendingno=$pendingsales[$j]['pending_no']; 
                $pendprodprintstate=$pendingsales[$j]['pendingsale_products']['pending_sale_product_printed'];
                $products->pendingsales()->updateExistingPivot($pendingno,
                        ['pending_sale_product_printed'=>$pendprodprintstate,
                         'pending_sale_product_qty_last' => 0]);
            }
        }
        $pendingsales=function ($query) use ($storeid){
            $query->with('spots')->where('store_id',$storeid);
        };
        $products=Products::with(['pendingsales'=>$pendingsales,'productcats'])->whereHas('pendingsales',$pendingsales)
        ->orderBy('products.product_id')->get();
        return response()->json($products);
    }

    public function updateProductPendingSalesState(Request $request,$pendingno){
        $storeid="";
        for ($i=0; $i < count($request->input('products')) ; $i++) {
            $productid=$request->input('products')[$i]['product_id']; 
            $pendingsales=$request->input('products')[$i]['pendingsales']; 
            $products=Products::where('product_id',$productid)->first();
            for ($j=0; $j < count($pendingsales) ; $j++) {
                $storeid=$pendingsales[$j]['store_id']; 
                $pendingno=$pendingsales[$j]['pending_no']; 
                $pendprodstate=$pendingsales[$j]['pendingsale_products']['pending_sale_product_state'];
                $products->pendingsales()->updateExistingPivot($pendingno,['pending_sale_product_state'=>$pendprodstate]);
            }
        }
        $pendingsales=function ($query) use ($storeid){
            $query->with('spots')->where('store_id',$storeid);
        };
        $products=Products::with(['pendingsales'=>$pendingsales,'productcats'])->whereHas('pendingsales',$pendingsales)
        ->orderBy('products.product_id')->get();
        return response()->json($products);
    }

    public function combinePendingSalesSpots(Request $request, $pendingno){
        for ($i=0; $i < count($request->input('spotlist')) ; $i++) {
            $spotid=$request->input('spotlist')[$i]['spot_id'];
            $pendingsales=PendingSales::where('pending_no',$pendingno)->first();
            $pendingsales->pendingsalespots()->attach($pendingno,['spot_id'=>$spotid]);
            Spots::where('spot_id','=',$spotid)->update(['spot_state'=>"4"]);
        }
        //update status meja utama
        Spots::where('spot_id','=',$pendingno)->update(['spot_state'=>"4"]);  
        return response()->json("SUCCESS");
    }

    public function getCombineSpots($pendingno){
        // $spotlist=PendingSales::with('pendingsalespots')->where([['store_id',$storeid],['pending_no',$pendingno]])->get(array('spot_id'));
        // return $spotlist;

        $arrspot[]="";
        $spotlist=DB::table('pending_sale_spots')->join('pending_sales','pending_sale_spots.pending_no','=','pending_sales.pending_no')
                ->where('pending_sale_spots.pending_no',$pendingno)
                ->get(array('pending_sale_spots.spot_id'));
        foreach ($spotlist as $spot) {
            $arrspot[] = $spot->spot_id;
        }
        $spots=Spots::whereIn('spot_id',$arrspot)->get();
        return response()->json($spots);
    }

    public function splitPendingSalesSpots(Request $request, $pendingno){
        $spotlist=DB::table('pending_sale_spots')->join('pending_sales','pending_sale_spots.pending_no','=','pending_sales.pending_no')
                ->where('pending_sale_spots.pending_no',$pendingno)
                ->get(array('pending_sale_spots.spot_id'));
        
        if(count($spotlist)!=count($request->input('spotlist'))){
            for ($i=0; $i < count($request->input('spotlist')) ; $i++) {
                $spotid=$request->input('spotlist')[$i]['spot_id'];
                //update status meja 
                Spots::where('spot_id','=',$spotid)->update(['spot_state'=>"1"]);
                //hapus yang ada di list
                DB::table('pending_sale_spots')->where([['pending_no',$pendingno],['spot_id',$spotid]])->delete();                
            }
            // Spots::where('spot_id','=',$pendingno)->update(['spot_state'=>"3"]);
            return response()->json("SUCCESS DELETE PARTIAL");
        }else{
            //jika ukuran list dari tabel sama dengan dari yang dikirim, hapus semua list
            //update status meja 
            for ($i=0; $i < count($request->input('spotlist')) ; $i++) {
                $spotid=$request->input('spotlist')[$i]['spot_id'];
                Spots::where('spot_id','=',$spotid)->update(['spot_state'=>"1"]);
            }
            //hapus semua yang ada di list
            DB::table('pending_sale_spots')->where('pending_no',$pendingno)->delete();
            //update meja menjadi tanpa gabungan
            Spots::where('spot_id','=',$pendingno)->update(['spot_state'=>"3"]);
            return response()->json("SUCCESS DELETE ALL COMBINE");
        }

    }

    public function deletePendingSales($pendingno,$storeid){
        $pendingid=PendingSales::where([['pending_no',$pendingno],['store_id',$storeid]])->first()->id;
        PendingSales::where('id',$pendingid)->delete();
        return response()->json("SUCCESS");
    }

    //proses untuk melakukan update sold out, di titip object yang dikirim ke server pada
    //objek pending sales
    public function updateProductRestoSoldOut(Request $request){
        $storeid=$request->input('store_id');
        for ($i=0; $i < count($request->input('products')) ; $i++) {
            $productid=$request->input('products')[$i]['product_id']; 
            $prodstate=$request->input('products')[$i]['product_state'];
            $products=Products::where('product_id',$productid)->first();
            $products->stores()->updateExistingPivot($storeid,['product_state'=>$prodstate]);
        }
        return response()->json("SUCCESS");
    }

    #-------------------WEBPOS
    public function webposSavePendingSales(Request $request){
        $storeid=$request->input('store_id');
        $memberno=$request->input('member_no');
        $pendingnote=$request->input('pending_note');

        $spotcontrol=new SpotsController;
        $pendSalesControl=new PendingSalesController;
        $pendingno=$pendSalesControl->autoPendingSalesNo($storeid);

        // 1. hapus data pending sebelumnya
        $pendingsales=PendingSales::where('member_no',$memberno)->first();
        if($pendingsales){
            $pendingno=$pendingsales->pending_no;
        }
        $pendingsales=PendingSales::where('member_no',$memberno)->delete();
        $spotid=$spotcontrol->spotGenerateRoomOrder($storeid);
        

        // 2. masukan data pending yang baru
        $pendingsales=new PendingSales;
        $pendingsales->pending_no=$pendingno;
        $pendingsales->store_id=$storeid;
        $pendingsales->spot_id=$spotid; 
        $pendingsales->member_no=$memberno;
        $pendingsales->pending_note=$pendingnote;
        $pendingsales->pending_state='9'; //khusus web pos,  di update jadi 1 pada saat akan di bayar
        $pendingsales->pending_date=date("Y-m-d H:i:s");
        $pendingsales->save();

        //3. simpan produk
        for ($i=0; $i < count($request->input('products')) ; $i++) {
            $product_id=$request->input('products')[$i]['product_id'];
            $prodqty=$request->input('products')[$i]['pendingsale_products']['pending_sale_product_qty'];
            $prodprice=$request->input('products')[$i]['pendingsale_products']['pending_sale_product_price'];
            $proddisc=$request->input('products')[$i]['pendingsale_products']['pending_sale_product_disc'];        
            $prodnote=$request->input('products')[$i]['pendingsale_products']['pending_sale_product_note'];  
            $prodstate=$request->input('products')[$i]['pendingsale_products']['pending_sale_product_state'];  
            $prodtotal=$prodqty*$prodprice;

            $products=Products::where('product_id','=',$product_id)->first();
            $pendingsales->products()->attach([
            'product_id'=>$product_id],
            [
                'pending_no'=>$pendingno,
                'pending_sale_product_qty'=>$prodqty,
                'pending_sale_product_price'=>$prodprice,
                'pending_sale_buy_price'=>$products->product_buy_price,
                'pending_sale_product_disc'=>$proddisc,
                'pending_sale_product_total'=>$prodtotal,
                'pending_sale_product_note' =>$prodnote,
                'pending_sale_product_state' =>$prodstate,
            ]);
        }
        return response()->json("SUCCESS");        
    }

    public function webposPendingSalesProductDelete($pendingno,$productid){
        $prodcount=pendingsale_products::where('pending_no',$pendingno)->count();
        $pendingsales=PendingSales::where('pending_no',$pendingno)->first();
        // $pendingsales=PendingSales::where('member_no',$memberno)->first();
        $product=Products::where('product_id',$productid)->first();
        $pendingsales->products()->detach($product); //hapus berdasarkan produk

        //hapus pending sales jika record sudah habis
        if($prodcount==1){
            PendingSales::where('pending_no',$pendingno)->delete();
        }
        return response()->json("SUCCESS");  
    }

    public function webposPendingSalesProductQuantityUpdate($pendingno,$productid,$productqty){
        $pendingsales=PendingSales::where('pending_no',$pendingno)->first();
        $storeid=$pendingsales->store_id;

        $productprice=ProductStores::where([['product_id',$productid],['store_id',$storeid]])->first()->product_price;
        $prodtotal=$productqty*$productprice;
        $pendingsales->products()->updateExistingPivot($productid,[
            'pending_sale_product_qty'=>$productqty,
            'pending_sale_product_price'=>$productprice,
            'pending_sale_product_total'=>$prodtotal,
        ]);
        return response()->json("SUCCESS");  
    }


    public function webposPendingSalesByMemberNo($memberno){
        $pendingsales=PendingSales::where('member_no',$memberno)->first();
        if(!$pendingsales){
            return null;
        }
        $storeid=$pendingsales->store_id;
        $pendingno=$pendingsales->pending_no;

        $store=function ($query) use ($storeid){
            $query->with('companys')->where('stores.store_id',$storeid);
        };
        $products=function ($query) use ($store){
            $query->with(['stores'=>$store,'brands','productcats'])->whereHas('stores',$store);
        };

        $pendingsales=PendingSales::with(['products'=>$products,'members'])
                    // ->where([['pending_no',$pendingno],['pending_state','9']])
                    ->where('pending_no',$pendingno)
                    ->first();

        return response()->json($pendingsales);
    }
    public function webposPendingSalesListByCompany($compid){
        $storeids=Stores::where([['comp_id',$compid],['store_default','0']])->pluck('store_id'); //bukan HO
        $stores=function ($query) use ($storeids){
            $query->with('companys')->whereIn('stores.store_id',$storeids);
        };
        $products=function ($query) use ($stores){
            $query->with(['stores'=>$stores,'brands','productcats'])->whereHas('stores',$stores);
        };

        $pendingsales=PendingSales::with(['products'=>$products,'members','spots','promotions.products'])
                ->where('pending_state','1')
                ->orderby(DB::raw('created_at'),'asc')->get();
        return response()->json($pendingsales);
    }
    public function webposPendingSalesListByStore($storeid){
        // $storeids=Stores::where([['comp_id',$compid],['store_default','0']])->pluck('store_id'); //bukan HO
        $store=function ($query) use ($storeid){
            $query->with('companys')->where('stores.store_id',$storeid);
        };
        $products=function ($query) use ($store){
            $query->with(['stores'=>$store,'brands','productcats'])->whereHas('stores',$store);
        };

        $pendingsales=PendingSales::where('store_id',$storeid)->get();
        foreach ($pendingsales as $key => $pendingsale) {
            if($pendingsale->member_no=="GUEST"){ 
                $pendingno=$pendingsale->pending_no;
                //Update member GUEST-PL01002 handle order selain live order (tanpa member)
                PendingSales::where('pending_no',$pendingno)->update(['member_no'=>'GUEST-'.$storeid]); 
            }
        }

        $pendingsales=PendingSales::with(['products'=>$products,'members','spots','promotions.products'])
                ->where([['pending_state','1'],['store_id',$storeid]])
                ->orderby(DB::raw('created_at'),'asc')->get();
        return response()->json($pendingsales);
    }
    public function webposPendingSalesCountByMemberNo($memberno){
        $count=0;
        $pendingsales=PendingSales::where([['member_no',$memberno],['pending_state','9']])->first();
        if($pendingsales==null){
            return response()->json($count);
        }
        $pendingno=$pendingsales->pending_no;
        $psp=pendingsale_products::where('pending_no',$pendingno);
        if($psp!=null){
            $count=pendingsale_products::where('pending_no',$pendingno)->count();
        }
        return response()->json($count);
    }

    public function webposPendingSalesUpdateStatusPayment($memberno,$note){
        PendingSales::where('member_no',$memberno)->update(['pending_state'=>'1','pending_note'=>$note]);
        return response()->json("SUCCESS");     
    }

    //fungsi sementara untuk menjaga agar pesanan tidak tertimpa
    //seharusnya nanti, pesanan bisa di update
    public function webposPendingSalesOnProgress($memberno){
        $pendingsales=PendingSales::where('member_no',$memberno)->first();
        if(!$pendingsales){
            return null;
        }
        $storeid=$pendingsales->store_id;
        $pendingno=$pendingsales->pending_no;

        $store=function ($query) use ($storeid){
            $query->with('companys')->where('stores.store_id',$storeid);
        };
        $products=function ($query) use ($store){
            $query->with(['stores'=>$store,'brands','productcats'])->whereHas('stores',$store);
        };

        $pendingsales=PendingSales::with(['products'=>$products,'members'])
                    // ->where([['pending_no',$pendingno],['pending_state','9']])
                    ->where('pending_no',$pendingno)
                    ->first();
        return response()->json($pendingsales);
    }

}