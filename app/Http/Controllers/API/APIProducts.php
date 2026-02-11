<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ProductGroups;
use App\Models\Brands;
use App\Models\Promotions;
use App\Models\MenuResto;
use App\Models\Spots;
use App\Models\Stores;
use App\Services\ProductServices;
use App\Services\StockService;
use Session;
use DB;
use App\Http\Controllers\StoresController;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class APIProducts extends Controller
{

    public function productStore($storeid){
        $productControl=new ProductsController;
        $products=$productControl->productByStore($storeid);
        return response()->json($products);
    }
    public function productStoreSearchByCriteria($storeid,$kriteria){
        // $productControl=new ProductsController;
        // $products=$productControl->productStoreByCriteria($storeid,$kriteria);

        $stores=function ($query) use ($storeid){
            $query->where('stores.id',$storeid);
        };
        $products=Products::with(['stores'=>$stores])->whereHas('stores',$stores)
            ->where('product_name','like','%'.$kriteria.'%')
            ->orWhere('product_barcode','like','%'.$kriteria.'%')
            ->get();       
        $arrProduct=array();
        foreach ($products as $key => $product) {
            $stok=$product->stores[0]['product_stores']['product_stock'];
            $product->product_buy_price=$product->stores[0]['product_stores']['product_buy_price'];
            $product->product_price=$product->stores[0]['product_stores']['product_price'];
            $product->product_stock=$stok;
            $product->product_buffer_stock=$product->stores[0]['product_stores']['product_buffer_stock'];
            $product->product_stock_state=$product->stores[0]['product_stores']['product_stock_state'];
            $product->product_consignment_state=$product->stores[0]['product_stores']['product_consignment_state'];
            $product->product_promo_state='0';

            #ambil yang ada stok,  di buat array baru untuk agar pengambilan data dari server lebih ringan
            if($stok!=0){
                $arrProduct[]=array('id' => $product->id,
                    'brand_id' => $product->brand_id,
                    'prodcat_id' => $product->prodcat_id,
                    'product_code' => $product->product_code,
                    'product_plu' => $product->product_plu,
                    'product_name' => $product->product_name,
                    'product_barcode' => $product->product_barcode,
                    'product_pic_loc' => $product->product_pic_loc,
                    'product_buy_price' => $product->product_buy_price,
                    'product_price' => $product->product_price,
                    'product_stock' => $product->product_stock,
                    'product_buffer_stock' => $product->product_buffer_stock,
                    'product_state' => $product->product_state,
                    'product_stock_state' => $product->product_stock_state,
                    'product_consignment_state' => $product->product_consignment_state,
                    'product_promo_state' => $product->product_promo_state,
                ); 
            }
        }
        $products=json_decode(json_encode($arrProduct), FALSE);  //array to object
        return response()->json($products);
    }
    
    public function productStoreStockOpnameByCategory($storeid,$prodcatid){
        $productControl=new ProductsController;
        $products=$productControl->productStoreByCategory($storeid,$prodcatid);
        $tglAwal = date('2018-01-01')." 00:00:00";  
        $tglAkhir = date('Y-m-d')." 23:59:59"; ; //hari ini

        $stockservice=new StockService;
        $productStockIn=$stockservice->stockCountStoreIn($storeid,$prodcatid,$tglAwal,$tglAkhir);   
        foreach ($products as $key => $product) {
            foreach ($productStockIn as $key => $stockIn) {
                if($product->id==$stockIn->id){
                $product->product_stock_in=$stockIn->product_stock_purchase+
                    $stockIn->product_stock_mutation_in+
                    $stockIn->product_stock_offmemo_plus;
                }
            }  
        }    
    
        $productStockOut=$stockservice->stockCountStoreOut($storeid,$prodcatid,$tglAwal,$tglAkhir);
        foreach ($products as $key => $product) {
            foreach ($productStockOut as $key => $startOut) {
              if($product->id==$startOut->id){
                $product->product_stock_out=$startOut->product_stock_retur+
                    $startOut->product_stock_mutation_out+
                    $startOut->product_stock_sales+
                    $startOut->product_stock_offmemo_minus;
              }
          }  
        }

        //konsolidasi stok in dan out
        foreach ($products as $key => $product) {
            $product->product_stock=$product->product_stock_in-$product->product_stock_out;
            $product->product_stock_physic=0;
            if($product->product_stock>0){
                $product->product_stock_physic=$product->product_stock;
            }
        }


        return response()->json($products);
    }















    //--------AGILE-----
    public function getProductByCompany($compid){
        $products=ProductServices::getProductAllByCompany($compid);
        return response()->json($products);
    }
    public function getProductStoreByCategoryId($storeid,$prodcatid){
        $stores=function ($query) use ($storeid){
            $query->where('stores.store_id',$storeid);
        };
        $products=Products::with(['productcats','stores'=>$stores])
            ->whereHas('stores',$stores)->where('prodcat_id',$prodcatid)->get();
        return response()->json($products);
    }

    //produk yang aktif di toko
    public function productStoreByStoreId($storeid){ 
        $stores=function ($query) use ($storeid){
            $query->where('stores.id',$storeid);
        };
        $products=Products::with(['stores'=>$stores,'productcategory','brand'])->whereHas('stores',$stores)->get();
        foreach ($products as $key => $product) {
            $product->product_buy_price=$product->stores[0]['product_stores']['product_buy_price'];
            $product->product_price=$product->stores[0]['product_stores']['product_price'];
            $product->product_stock=$product->stores[0]['product_stores']['product_stock'];
            $product->product_buffer_stock=$product->stores[0]['product_stores']['product_buffer_stock'];
            $product->product_promo_state='0';
        }
        return response()->json($products);
    }
    public function searchproductbykriteria($storeid,$kriteria){
        $products=ProductServices::getProductStoreByCriteria($storeid,$kriteria);
        return response()->json($products);
    }

    //produk aktif di toko dan ready (stok > 0)  current
    public function getproductreadystoresbystid($storeid){ 
        $products=ProductServices::getProductReadyStoreByStoreId($storeid);
        return response()->json($products);
    }
    public function searchproductreadybykriteria($storeid,$kriteria){
        $products=ProductServices::getProductReadytStoreByCriteria($storeid,$kriteria);
        return response()->json($products);
    }
    //produk aktif di toko dan ready (stok > 0)  new
    public function getProductWithStock($storeid){ 
        $products=ProductServices::getProductByStoreId($storeid,"1"); 
        return response()->json($products);
    }    

    //produk yang digabung dengan produk promo(old)
    public function getproductunhideall($storeid){
        $products=ProductServices::getProductUnHideByStoreId($storeid);

        //cari ke promo, kemudian cari di produk aslinya, kemudian cari stok terendah
        foreach ($products as $key => $prods) {
            $promono=$prods->product_barcode;    
            if($prods->product_state=='2'){ //produk paket
                $prods->product_stock=$this->getproductsmaleststockbypromoid($promono,$storeid);
            }
        }
        return response()->json($products);
    }


    //produk khusus close loop emoney
    public function getProductPayment($storeid){
        $products=ProductServices::getProductPayment($storeid);
        return response()->json($products);
    }

    public function getallproduct(){
        $fractal = new Manager();

        $product=Products::all();
        $prodfractal= fractal()->collection($product)
                    ->transformWith(new ProductsTransform)
                    ->toArray();
        return $prodfractal;
    }

    public function search($kriteria){
        //kirim status promo untuk dikirimkan ke API, jadi nanti dari client tidak bolak balik
        //2. produk promo, jika produk promo harus dikirimkan juga konsep promonya

        //konsep ini nanti bisa digunakan di versi web
        $prods=Products::where('product_id','like',"%{$kriteria}%")
                 ->orWhere('product_desc','like','%'.$kriteria.'%')
                 ->get(['products.*','product_activestate as product_promo_stat']); //insert dummy column

        for($i=0;$i<count($prods);$i++){
            $prodid=$prods[$i]['product_id'];
            $prods[$i]['product_promo_stat']= $this->getpromostate($prodid);
        }        
        return response()->json($prods);
    }

    public function barcode($barcodeid){
        $prods=Products::where('product_barcode','like',"%{$barcodeid}%")
               ->get(['products.*','product_activestate as product_promo_stat']);
        for($i=0;$i<count($prods);$i++){
                $prodid=$prods[$i]['product_id'];
                $prods[$i]['product_promo_stat']= $this->getpromostate($prodid);
        } 
        return response()->json($prods);
    }

    public function getpromostate($prodid){
        $promos=Promotions::where('promo_active','=','1')
        ->whereHas('products',function($query) use ($prodid)
        { 
            $query->where('products.product_id','=',$prodid);
        })->first();
        if($promos!=null){ 
            return "1"; //promo
        }
        return "0"; //tidak promo
    }
    
    public function productActivationByStoreProdCatId($storeid,$prodcatid){
        $prodcat=function ($query) use($prodcatid){
            $query->where('prodcat_id',$prodcatid);
        };
        $products=Products::with(["productcats"=>$prodcat])->where('product_state','!=','0')
                  ->wherehas('productcats',$prodcat)->get();

        foreach ($products as $product) {
            $productid=$product->product_id;
            $prod=function ($query) use ($productid){
              $query->where('products.product_id',$productid);
            };
            $storeprod=Stores::with(['products'=>$prod])->where('stores.store_id',$storeid)->whereHas('products',$prod)->first(); //produknya hanya satu

            //jika tidak ditemukan di relasi, berarti statusnya false/0
            if(!$storeprod){
              $product->product_state=0;
              $product->product_stock=0;
            }else{
              $product->product_state=1;
              $product->product_stock=$storeprod->products[0]['productstores']['product_stock'];
            }
        }
        return $products;
    }

   

   

    #-------------------WEBPOS
    public function webposProductStoreByCategoryId($storeid,$prodcatid){
        $storetype=Stores::where('store_id',$storeid)->first()->store_type;
        $storecontrol=new StoresController;

        $stores=function ($query) use ($storeid){
            $query->with('companys')->where('stores.store_id',$storeid);
        };

        //produk kategori yang aktif
        $productcats=function ($query) use ($prodcatid) {
            $query->where([['prodcat_id',$prodcatid],['prodcat_state','1']]);
        };

        if($storecontrol->checkStoreType($storetype)=="RETAIL"){
            $products=Products::with(['productcats'=>$productcats,'stores'=>$stores])
                ->whereHas('stores',$stores)
                ->whereHas('productcats',$productcats)
                ->where([['prodcat_id',$prodcatid],['product_state','!=','0']])->get();
        }
        if($storecontrol->checkStoreType($storetype)=="RESTO"){
            $products=Products::with(['stores'=>$stores,'brands','productcats'=>$productcats])
                ->whereHas('stores',$stores)->whereHas('productcats',$productcats)
                ->whereIn('products.product_state',array('2','3'))->get();
            foreach ($products as $key => $product) {
                $product->product_price=$product->stores[0]['productstores']['product_price'];
                $product->store_id=$product->stores[0]['store_id'];
                $product->brand_name=$product->brands['brand_name'];
                $product->prodcat_desc=$product->productcats['prodcat_desc'];
                $product->product_stock=$product->stores[0]['productstores']['product_stock'];
                $product->product_promostate='0';
    
                $mentono=$product->product_plu;    
                if($product->product_state=='3'){ //produk dari pembuatan menu resto
                    $product->product_stock=$this->restoProductSmallStock($mentono,$storeid);
                    $product->product_stockstate='1'; //dibuat statusnya jadi stock
                    $product->stores[0]['productstores']['product_stock']=$product->product_stock;
                }
            }
        } 

        return response()->json($products);
    }


    #--------AGILE MANAGER
    public function amProductByCompany($compid){
        $products=Products::with(['productcats','brands'])->where('comp_id',$compid)->get();
        return response()->json($products);
    }
    
    public function productActivationUpdate(Request $request,$storeid){
        for ($i=0; $i < count($request->input('products')) ; $i++) {
            $productid=$request->input('products')[$i]['product_id'];
            $productstate=$request->input('products')[$i]['product_state'];
            $product=Products::where('product_id',$productid)->first();
            $productprice=$product->product_price;
            $prodcatid=$product->prodcat_id; 

            if($productstate=="1"){
                //jika sudah ada tidak perlu di masukan
                $storeprod=function($query) use ($storeid){
                  $query->where('stores.store_id',$storeid);
                };
                $prod=Products::with(['stores'=>$storeprod])->whereHas('stores',$storeprod)->where('product_id',$productid)->first();
                if(!$prod){
                  $product->stores()->attach([
                    'store_id'=>$storeid],
                    [
                        'product_id'=>$productid,
                        'product_stock'=>0,
                        'product_buffer_stock'=>0,
                        'product_price'=>$productprice, 
                    ]);
                }
              }else{
                $store=Stores::where('store_id','=',$storeid)->first();
                $store->products()->detach($product); //hapus berdasarkan produk
              }           
        }     
        $stores=function($query) use ($storeid){
            $query->where('stores.store_id',$storeid);
        };  
        $products=Products::with(['productcats','stores'=>$stores])
            ->whereHas('stores',$stores)->where('prodcat_id',$prodcatid)->get();
        return response()->json($products);
    }
    public function productStoreUpdate(Request $request,$storeid){
        // $products=$request->get('products');
        $prodcatid="";
        for ($i=0; $i < count($request->input('products')) ; $i++) {
            $productid=$request->input('products')[$i]['product_id'];
            $productprice=$request->input('products')[$i]['product_price'];

            $product=Products::where('product_id',$productid)->first();
            $prodcatid=$product->prodcat_id; 
            $product->stores()->updateExistingPivot($storeid,['product_price'=>$productprice]);
        }

        $stores=function ($query) use ($storeid){
            $query->where('stores.store_id',$storeid);
        };
        $products=Products::with(['productcats','stores'=>$stores])
            ->whereHas('stores',$stores)->where('prodcat_id',$prodcatid)->get();
        return response()->json($products);
    }
    public function saveimage(Request $request){
        $product_id=$request->input('product_id');  

        // dd($request);

        $this->validate($request, [
          'image_file' => 'required | image | mimes:jpeg,png,jpg,gif | max:256'
        ]);
    
        // dd(url()->current();
        // dd($request->root());

      
        // menyimpan data file yang diupload ke variabel $file
        $images = $request->file('image_file');
        $new_name=$product_id.'.'.$images->getClientOriginalExtension();

        // dd($new_name);

        // $tujuan_upload = public_path("images");
        $tujuan_upload = base_path("images");
        $images->move($tujuan_upload,$new_name); 

        $fileloc=substr($request->root(),0,strlen($request->root())-6)."images/".$new_name;

        Products::where('product_id','=',$product_id)->update(['product_file_loc'=>$fileloc]);

        return "success";
        //  back()->with('success','Upload Berhasil')->with('path',$new_name);
    }
}
