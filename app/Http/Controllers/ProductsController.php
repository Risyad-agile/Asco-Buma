<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Products;
use App\Models\ProductUnits;
use App\Models\ProductCategories;
use App\Models\Brands;
use App\Models\User; 
use App\Models\Companys;
use App\Models\Stores;
use App\Models\OfficialMemos;
use App\Models\ProductStores;
use App\Services\ProductServices;
use App\Services\StockCardServices;
use App\Models\ProductImport;
use App\Imports\ProductImportProcess;
use GuzzleHttp\Client;
use App\Http\Controllers\ProductCategoriesController;
use App\Http\Controllers\BrandsController;
use App\Http\Controllers\OfficialMemosController;
use App\Http\Controllers\HomeController;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use DB;
use Entrust;
use Validator;


class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function compid(){
        return Auth::user()->store->company->id;
    }

    public function storeid(){
      return Auth::user()->store->id;
    }

    public function index()
    {
        $prodcat=ProductCategories::where('prodcat_state','1')->get();
        $brands=Brands::where('brand_state','1')->get();
        $units=ProductUnits::where('unit_state','1')->get();
        return view('products/products',compact('prodcat','brands','units'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $products=Products::with('productcategory','brand','productunit')->where([['product_state','!=','0'],['product_state','!=','3']])->get();
        foreach ($products as $product) {
          if($product->product_pic_loc=='1'){
            //inject default picture
            $product->product_pic_loc="http://apps.agile.co.id/devbromo/images/agile_logo.jpg";
          }
        }
        return $products;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'brand_id' => 'required|string',
          'prodcat_id' => 'required|string',
          'product_name' => 'required|string',
        ],[ 
          'brand_id.required' => 'Silakan Pilih Merek', 
          'prodcat_id.required' => 'Silakan Pilih Kategori', 
          'product_name.required' => 'Masukan Deskripsi Produk', 
        ]);
        if ($validator->fails()) {
          return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }
        try {
          $products=new Products;
          $products->brand_id=$request->get('brand_id');
          $products->prodcat_id=$request->get('prodcat_id');
          $products->unit_id=$request->get('unit_id');
          $products->product_name=$request->get('product_name');
          $products->product_barcode=$request->get('product_barcode');
          // $products->product_short_desc=substr($request->get('product_name'),0,50); //substr(string,start,length)
          $products->product_dot=$request->get('product_dot');  
          // $products->product_stock_state=$request->get('product_stock_state');
          $exec = $products->save();
          if (!$exec) {
            return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
          }

          return response()->json(['status' => 'success', 'message' => 'Data successfully Saved', 'code' => 200]);
          } catch (\Exception $e) {
              return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
          }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);  
    }
    //---fungsi sementara, nanti kedepannya ini di hapus, karena pembuatan produk melalui mekanisme pengajuan
    public function productSaveActive(Request $request)
    {
        $validator = Validator::make($request->all(), [
          'brand_id' => 'required|string',
          'prodcat_id' => 'required|string',
          'product_name' => 'required|string',
        ],[ 
          'brand_id.required' => 'Silakan Pilih Merek', 
          'prodcat_id.required' => 'Silakan Pilih Kategori', 
          'product_name.required' => 'Masukan Deskripsi Produk', 
        ]);
        if ($validator->fails()) {
          return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
        }
        try {
          $products=new Products;
          $products->brand_id=$request->get('brand_id');
          $products->prodcat_id=$request->get('prodcat_id');
          $products->unit_id=$request->get('unit_id');
          $products->product_name=$request->get('product_name');
          $products->product_barcode=$request->get('product_barcode');
          $products->product_dot=$request->get('product_dot');  
          $exec = $products->save();
          if (!$exec) {
            return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
          }
          $stores=Stores::where([['comp_id',$this->compid()],['store_type','!=','0']])->get(); //bukan ho
          foreach ($stores as $key => $store) {
            $this->productStoreActivator($store->id,$products->id);

          }
          return response()->json(['status' => 'success', 'message' => 'Data successfully Saved', 'code' => 200]);
          } catch (\Exception $e) {
              return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
          }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);  
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $prods=Products::join('productgroups','products.productgroup_id','=','productgroups.productgroup_id')
              ->join('brands','products.brand_id','=','brands.brand_id')
              ->select('products.*','products.product_id','productgroups.*','brands.*')
              ->where('product_id','=',$id)->get();

      return response($prods);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $res = Products::where('id', $id)->update($request->except(['id','prodcat_id']));
      if (!$res) {
          return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
      }
      return response()->json(['status' => 'success', 'message' => 'Data successfully Updated', 'code' => 200]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $res = Products::where('product_id', $id)->update([
        'product_state'=>'0', 
      ]);

      if (!$res) {
          return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
      }

      return response()->json(['status' => 'success', 'message' => 'Data successfully edited', 'code' => 200]);
    }

    #-----PRODUCT UNIT----- //akses superadmin
    public function productUnitIndex(){
      return view('products/productunits');
    }
    public function productUnitLoad(){
      $units=ProductUnits::where('unit_state','1')->get();
      return $units;
    }
    public function productUnitSave(Request $request){
      $validator = Validator::make($request->all(), [
        'unit_name' => 'required|string', 
      ],[ 
        'unit_name.required' => 'Silakan Masukan Satuan Produk',  
      ]);
      if ($validator->fails()) {
        return response()->json(['status' => 'error', 'message' => $validator->messages()->first(), 'code' => 404]);
      }
      try {
        $unit=new ProductUnits;
        $unit->unit_name=$request->get('unit_name'); 
        $unit->unit_state='1';
        $exec = $unit->save();
        if (!$exec) {
          return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
        }

        return response()->json(['status' => 'success', 'message' => 'Data successfully Saved', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
      return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);  
    }
    public function productUnitUpdate(Request $request, $id){
      $res = ProductUnits::where('id', $id)->update($request->except(['id']));
      if (!$res) {
          return response()->json(['status' => 'error', 'message' => 'System Error', 'code' => 404]);
      }
      return response()->json(['status' => 'success', 'message' => 'Data successfully Updated', 'code' => 200]);
    }

  #-----AKTIFASI PRODUCT----- //akses manajer
  public function productActivationIndex(){
    return view('products/activation_index');
  }
  public function productActivationMain(Request $request){
    $selectedstore=explode(",",$request->get('store_id'));

    $stores=Stores::whereIn('id',$selectedstore)->get();
    $products=Products::where('product_state','1')->get();
    $prodcat=ProductCategories::where('prodcat_state','1')->get();

    return view('products/activation',compact('products','prodcat','stores'));
  }

  public function productActivationLoad(Request $request){
    $stores=$request->stores;
    $selectedstores=array();
    $storenumber=0;
    foreach ($stores as $store) { 
      $selectedstores[]=$store['id']; //toko yang dikirim
      $storenumber++; //jumlah toko
    }

    $products=Products::with("productcategory")->where('product_state','!=','0')->get();

    foreach ($products as $product) {
      $productid=$product->id;
      $prod=function ($query) use ($productid){
        $query->where('products.id',$productid);
      };
      $storeprodnumber=Stores::whereIn('stores.id',$selectedstores)->whereHas('products',$prod)->count();

      //jika ditemukan jumlah toko sama dengan jumlah toko di relasi maka statusnya true
      if($storenumber==$storeprodnumber){
        $product->product_state=true;
      }else{
        $product->product_state=false;
      }
        
    }
    return $products;
  }

  public function productActivationUpdate(Request $request,$id){
    $prodstat=$request->input("activestate")["product_state"];
    $stores = json_decode(json_encode($request->input("stores")),false); //array to object
    //lakukan pemeriksanaan sebelum eksekusi, terutama untuk perubahan status menjadi non aktif
    //memastikan bahwa produk toko yang di hapus tidak memiliki stok
    if($prodstat=="false"){
      foreach ($stores as $store) {
        $storeid=$store->id; 
        $storeprod=function($query) use ($storeid){
          $query->where('stores.id',$storeid);
        };
        $prod=Products::with(['stores'=>$storeprod])->whereHas('stores',$storeprod)->where('id',$id)->first();
        $stok=$prod->stores[0]['product_stores']['product_stock'];
        if($stok!=0){
          $pesan="Produk ".$prod->product_name." pada ".$stok=$prod->stores[0]['store_name']." memiliki stok ".$stok.", tidak dapat di non aktifkan";
          return response()->json(['status' => 'error', 'message' => $pesan, 'code' => 404]);
        }
      }
    }

    //jika status produk true, buat baru semua, jika status produk false, hapus semua
    //ho tidak termasuk dalam daftar karena Ho tidak bisa di aktif atau non aktifkan
    try {
        foreach ($stores as $store) {
            $storeid=$store->id; 
            $product=Products::where('id',$id)->first();
            if($prodstat=="true"){
              //jika sudah ada tidak perlu di masukan
              $storeprod=function($query) use ($storeid){
                $query->where('stores.id',$storeid);
              };
              $prod=Products::with(['stores'=>$storeprod])->whereHas('stores',$storeprod)->where('id',$id)->first();
              if(!$prod){
                $product->stores()->attach([
                  'store_id'=>$storeid],
                  [
                      'product_id'=>$id,
                      'product_stock'=>0,
                      'product_buffer_stock'=>0,
                      // 'product_price'=>$product->product_price, 
                  ]);
              }
            }else{
              $store=Stores::where('id',$storeid)->first();
              $store->products()->detach($product); //hapus berdasarkan produk
            }
    
        }  
        return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);

    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
    }
  }  
  public function productActivateAll(Request $request)
  {
    $stores = json_decode(json_encode($request->input("stores")),false); //array to object
    try {
        foreach ($stores as $store) {
          $storeid=$store->id;  
          //ambil semua produk yang aktif di database berdasarkan perus
          $products=Products::where('product_state','1')->get();
          $storeprod=function($query) use ($storeid){
            $query->where('stores.id',$storeid);
          };
          foreach ($products as $key => $product) {
              $id=$product->id;
              $prod=Products::with(['stores'=>$storeprod])->whereHas('stores',$storeprod)->where('id',$id)->first();
              //jika sudah ada tidak perlu di masukan
              if(!$prod){
                $product->stores()->attach([
                  'store_id'=>$storeid],
                  [
                      'product_id'=>$id,
                      'product_stock'=>0,
                      'product_buffer_stock'=>0, 
                  ]);
              }
          }
        }  
        return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
    }
  }

  #-----BUFFER PRODUCT----- 
  public function productBufferIndex(){
    $storeControl=new StoresController();
    $countStore=$storeControl->storeByCompanyCount();
    if($countStore["count"]=='1'){
        $storeid=$countStore["storeid"];
        $store=Stores::where('id',$storeid)->first(); 
        $products=Products::where('product_state','1')->get();
        $prodcat=ProductCategories::where('prodcat_state','1')->get();
        return view('products/buffer_update',compact('products','prodcat','store'));
    }    
    return view('products/buffer_index'); 
  }

public function productBufferMain(Request $request){ 
  $selectedstore=explode(",",$request->get('storeid'));
  $stores=Stores::whereIn('id',$selectedstore)->get();
  $products=Products::where('product_state','1')->get();
  $prodcat=ProductCategories::where('prodcat_state','1')->get();
  return view('products/buffer_update',compact('products','prodcat','stores'));
}    

public function productBufferLoad(Request $request){
  $compid=$this->compid();  
  $storeid=$request->store['id']; 
  
  $store=function ($query) use ($storeid){
    $query->where('stores.id',$storeid); //toko terpilih
  };
  $products=Products::with(['stores'=>$store,'productcategory'])
      ->whereHas('stores',$store)
      ->where('product_state','!=','0')->get();
  $products=$this->injectProductList($products);
  
  return $products;
}

public function productBufferUpdate(Request $request, $id){
  $productid=$request->input("productid");
  $storeid=$request->get("store")['id'];  
  $product_buffer_stock=isset($request->get('buffer')['product_buffer_stock'])?$request->get('buffer')['product_buffer_stock']:$products->product_buffer_stock;

  try {
    $product=Products::where('id',$productid)->first();
    $product->stores()->updateExistingPivot($storeid,['product_buffer_stock'=>$product_buffer_stock]);  
    return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
  } catch (\Exception $e) {
    return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
  }
}
public function productBufferMassIndex($storeid){
  return view('products/buffer_update_mass',compact('storeid'));
}
public function productBufferMassUpdate(Request $request)
{
  $product_buffer_stock=$request->input("bufferstock");
  $storeid=$request->get("storeid");  
  try {
    $store=function ($query) use ($storeid){
      $query->where('stores.id',$storeid); 
    };
    $products=Products::whereHas('stores',$store)->where('product_state','!=','0')->get();
    foreach ($products as $key => $product) {
      $product->stores()->updateExistingPivot($storeid,['product_buffer_stock'=>$product_buffer_stock]);  
    }    
    return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
  } catch (\Exception $e) {
    return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
  }
}


#-----PENENTUAN HARGA PRODUCT----- //akses manajer
//menu utama penetuan harga
public function productPriceIndex(){
  return view('products/price_update_index');
}

  //menu utama update harga
  public function productPriceMain(Request $request){
    $selectedstores=explode(",",$request->get('storeid'));
    $stores=Stores::whereIn('id',$selectedstores)->get();
    return view('products/price_update',compact('stores'));
  }

  public function productPriceLoad(Request $request){
    //update 10-08-2020
    //1. jika di load semua atau beberapa toko maka yang ditampilkan sebagai default adalah harga di Head Office
    //2. jika di load hanya satu toko maka, yang ditampilkan adalah harga dari toko tersebut
    $compid=$this->compid();
    $stores=$request->stores;
    $selectedstores=array();
    $storenumber=0;

    foreach ($stores as $store) { 
      $selectedstores[]=$store['id']; //toko yang dikirim
      $storenumber++; //jumlah toko
    }

    #konsepnya beda dengan aplikasi agile, jadi kalau di kidswa farma headoffice bisa jadi
    #tidak ada isinya, makana ini belum solve, next di rumuskan ulang konsep yang paling cocok
    // $store=function ($query) use ($compid){
    //   $query->where('stores.comp_id',$compid)->where('store_type','0'); //head office
    // };

    // if($storenumber==1){
    //   $store=function ($query) use ($selectedstores){
    //     $query->whereIn('stores.id',$selectedstores); //toko terpilih
    //   };
    // }

    $store=function ($query) use ($selectedstores){
      $query->whereIn('stores.id',$selectedstores); //toko terpilih
    };
    
    $products=Products::with(['stores'=>$store,'productcategory'])
        ->whereHas('stores',$store)
        ->where('product_state','!=','0')->get();
    $products=$this->injectProductList($products);
    
    return $products;
  }

  public function productPriceUpdate(Request $request, $id)
  {
    $stores=$request->input("stores");  
    $storeids=array();
    foreach ($stores as $store) {
      $storeids[]=$store['id']; 
    }

    $selectedstores=function ($query) use ($storeids){
      $query->whereIn('id',$storeids);
    };
    $products=Products::with(['stores'=>$selectedstores])->whereHas('stores',$selectedstores)->where('id',$id)->first();

    $product_buy_price=isset($request->get('values')['product_buy_price'])?$request->get('values')['product_buy_price']:$products->stores[0]['product_stores']['product_buy_price'];
    $product_price=isset($request->get('values')['product_price'])?$request->get('values')['product_price']:$products->stores[0]['product_stores']['product_price'];


    //update buffer head office jika store lebih dari satu (update masal)
    if(count($stores)>1){
      //update master tabel
      $exec=Products::where('id',$id)
          ->update(array('product_buy_price'=>$product_buy_price,'product_price'=>$product_price));
      if (!$exec) {
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
      }

      $storeid=Stores::where([['comp_id',$this->compid()],['store_type','0']])->first()->id; //head office
      $product=Products::where('id',$id)->first();
      $products->stores()->updateExistingPivot($storeid,['product_buy_price'=>$product_buy_price,'product_price'=>$product_price]);
    }

    try {
      foreach ($stores as $store) {
          $storeid=$store['id']; //karena bukan objek jadi retrievenya seperti ini
          $product=Products::where('id',$id)->first();
          $products->stores()->updateExistingPivot($storeid,['product_buy_price'=>$product_buy_price,'product_price'=>$product_price]);   
      }  
      return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
    }

  }
    
  #-----COMPANY PRODUCT ---- //akses manajer masing masing apotik
  public function companyProductIndex(){
    $prodcat=ProductCategories::where('prodcat_state','1')->get();
    $brands=Brands::where('brand_state','1')->get();
    $units=ProductUnits::where('unit_state','1')->get();
    return view('products/company/products',compact('prodcat','brands','units'));
  }
  public function companyProductUpdateLoad(){
    $storeids=Stores::where('comp_id',$this->compid())->get('id');
    $stores=function ($query) use ($storeids){
      $query->whereIn('stores.id',$storeids);
    };
    $products=Products::with(['stores'=>$stores,'productcategory','brand','productunit'])->whereHas('stores',$stores)->get();
    $products=$this->injectProductList($products);
    foreach ($products as $product) {
      if($product->product_pic_loc=='1'){
        //inject default picture
        $product->product_pic_loc="http://apps.agile.co.id/devbromo/images/agile_logo.jpg";
      }
    }
    return $products;
  }
  public function companyProductUpdateMain(Request $request){
    $productid=$request->product_id;
    $status=$request->state; 
    $product=Products::where('id',$productid)->first();
    return view('products/company/products_update',compact('product'));
  }

  public function companyProductUpdateSave(Request $request){
    $stores=Stores::where('comp_id',$this->compid())->get();
    $productid=$request->product_id;
    $stockstate=$request->get('form')['product_stock_state'];
    $highprice=$request->get('form')['product_sell_high_price'];
    $lowerprice=$request->get('form')['product_sell_lower_price'];
    foreach ($stores as $key => $store) {
        ProductStores::where([['product_id',$productid],['store_id',$store->id]])->update([
          'product_stock_state'=> $stockstate,
          'product_sell_high_price'=> $highprice,
          'product_sell_lower_price'=> $lowerprice,
        ]);
    }
    return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
  }

    public function companyProductNewStart(){
    }

    //--status stok
    public function companyProductStateStockIndex(){
      return view('products/company/products_state_stock');
    }
    public function companyProductStateStockLoad(){
      $storeids=Stores::where('comp_id',$this->compid())->get('id');
      $stores=function ($query) use ($storeids){
        $query->whereIn('stores.id',$storeids);
      };
      $products=Products::with(['stores'=>$stores,'productcategory','brand','productunit'])->whereHas('stores',$stores)->get();
      $products=$this->injectProductList($products);
      foreach ($products as $product) {
        $statestock=$product->product_stock_state;
        //jika status stok = 1, stok =true
        if($statestock=="1"){
          $product->product_state=true;
        }else{
          $product->product_state=false;
        }

        if($product->product_pic_loc=='1'){
          //inject default picture
          $product->product_pic_loc="http://apps.agile.co.id/devbromo/images/agile_logo.jpg";
        }
      }
      return $products;
    }
    public function companyProductStateStockUpdate(Request $request, $id){
      $productid=$request->productid;
      $stockstate='1';
      if($request->get('activestate')['product_state']=="false"){
        $stockstate='0';
      }
      
      $stores=Stores::where('comp_id',$this->compid())->get();
      try {
        foreach ($stores as $store) {
            $storeid=$store->id; 
            $product=Products::where('id',$productid)->first();
            $product->stores()->updateExistingPivot($storeid,['product_stock_state'=>$stockstate]);   
        }  
        return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
      } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
      }
    }
    //--status konsinyasi
    public function companyProductStateConsignmentIndex(){
      return view('products/company/products_state_consignment');
    }
    public function companyProductStateConsignmentLoad(){
      $storeids=Stores::where('comp_id',$this->compid())->get('id');
      $stores=function ($query) use ($storeids){
        $query->whereIn('stores.id',$storeids);
      };
      $products=Products::with(['stores'=>$stores,'productcategory','brand','productunit'])->whereHas('stores',$stores)->get();
      $products=$this->injectProductList($products);
      foreach ($products as $product) {
        $consstate=$product->product_consignment_state;
        //jika status stok = 1, stok =true
        if($consstate=="0"){
          $product->product_state=true;
        }else{
          $product->product_state=false;
        }

        if($product->product_pic_loc=='1'){
          //inject default picture
          $product->product_pic_loc="http://apps.agile.co.id/devbromo/images/agile_logo.jpg";
        }
      }
      return $products;
    }
    public function companyProductStateConsignmentUpdate(Request $request, $id){
      $productid=$request->productid;
      $consstate='1';
      if($request->get('activestate')['product_state']=="true"){
        $consstate='0';
      }
      
      $stores=Stores::where('comp_id',$this->compid())->get();
      try {
        foreach ($stores as $store) {
            $storeid=$store->id; 
            $product=Products::where('id',$productid)->first();
            $product->stores()->updateExistingPivot($storeid,['product_consignment_state'=>$consstate]);   
        }  
        return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
      } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
      }
    }



    #-----IMPORT PRODUCT----- //akses manajer
    //------------------import produk dari xls------------------
    public function importIndex(){
      return view("products/import/importtype");
    }
    public function importSelectedType(Request $request){
      $offmemtypeid=$request->id;
      if($offmemtypeid=='3'){ 
        //import produk, penerimaan stok via berita acara
        //ada tujuan barang namun, tidak ada asal barang
        return view("products/import/importoffmemoindex");
      }
      if($offmemtypeid=='4'){ 
        //import produk, penerimaan stok via mutasi in
        //ada tujuan barang dan asal barang

      }
    }
    public function importMain(Request $request){
      $storeid=$request->storeorigin;
      $compid=Stores::where('id',$storeid)->first()->comp_id;
      return view("products/import/importoffmemoupload",compact('storeid','compid'));
    }

    public function importUpload(Request $request){
      $storeorigin=$request->storeorigin;
      $storedestination=$request->storedestination;
      $compid=Stores::where('id',$storeorigin)->first()->comp_id; //sama saja pake origin atau destination

      $filexls = $request->file('files')[0];
      $new_name= "PROD-".$storeorigin.".".$filexls->getClientOriginalExtension();
      $tujuan_upload = public_path("images"); //daripada buat folder baru simpen di images ajah
      $filexls->move($tujuan_upload,$new_name); 

      $fileloc=$tujuan_upload."/".$new_name;

      ProductImport::truncate(); //pastikan data yang ada di hapus
      Excel::import(new ProductImportProcess, $fileloc);

      ProductImport::query()->update(['comp_id'=>$compid,
          'store_origin'=>$storeorigin,
          'store_destination'=>$storedestination]);
      $prodimport=ProductImport::all();
      return $prodimport;
    }

    public function importBrandMapping(Request $request){
        $compid=$request->id;
        $brandcontrol=new BrandsController;
        $brands=Brands::where('brand_state','1')->get();
        $productbrands=ProductImport::where('comp_id',$compid)->groupBy('product_brand')->get('product_brand');
        $brandname=null;
        $importbrands=array();  
        $id=1;
        foreach ($productbrands as $key => $productbrand) {
          if($productbrand->product_brand!=$brandname){
              if($productbrand->product_brand==null){
                  $productbrand->product_brand=$brandname;
              }else{
                  $brandname=$productbrand->product_brand;
                  $predictbrandname=$brandcontrol->brandImportPredictedName($brandname); //belum di pakai
                  $importbrands[]= array('id'=>$id,'brand_name_import' => $productbrand->product_brand,
                    'brand_id'=>'NEW','brand_name'=>$productbrand->product_brand);
                  $id++;
              }
          }
        }
        $importbrands = json_encode($importbrands); //array to object
        return view("products/import/importbrand",compact('brands','importbrands','compid'));
    }

    public function importBrandMappingSave(Request $request){
      $compid=$request->compid;
      $brandcontrol=new BrandsController;
      $ibrands=json_decode($request->ibrands);
      //simpan merek pada tambel import
      foreach ($ibrands as $key => $ibrand) {
        $importbrandid=isset($ibrand->brand_id)?$ibrand->brand_id:"NEW";        
        if($importbrandid=="NEW"){
            $brandid=$brandcontrol->brandImportAdd($ibrand->brand_name);
            $ibrand->brand_id=$brandid;
        }else{
           $brand=Brands::where('id',$importbrandid)->first();
           $ibrand->brand_id=$brand->brand_id;
           $ibrand->brand_name=$brand->brand_name;
        }
        ProductImport::where('product_brand',$ibrand->brand_name_import)->update(['brand_id'=>$ibrand->brand_id]);
      }

      //persiapkan data kategori produk
      $prodcatcontrol=new ProductCategoriesController;
      $prodcats=ProductCategories::where('prodcat_state','1')->get();
      $productcats=ProductImport::where('comp_id',$compid)->groupBy('product_category')->get('product_category');
      $prodcatname=null;
      $importprodcats=array();  
      $id=1;
      foreach ($productcats as $key => $productcat) {
        if($productcat->product_category!=$prodcatname){
            if($productcat->product_category==null){
                $productcat->product_category=$prodcatname;
            }else{
                $prodcatname=$productcat->product_category;
                $predictprodcatdesc=$prodcatcontrol->prodcatImportPredictedDesc($prodcatname); //belum dipakai
                $importprodcats[]= array('id'=>$id,'prodcat_desc_import' => $productcat->product_category,
                  'prodcat_id'=>'NEW','prodcat_desc'=>$productcat->product_category);
                $id++;
            }
        }
      }
      $importprodcats = json_encode($importprodcats); //array to object
      return view("products/import/importproductcategory",compact('compid','prodcats','importprodcats'));
    }

    public function importProductCategoryMappingSave(Request $request){
      $compid=$request->compid;
      $prodcatcontrol=new ProductCategoriesController;
      $iprodcats=json_decode($request->iprodcats);
      
      foreach ($iprodcats as $key => $iprodcat) {
        $importprodcatid=isset($iprodcat->prodcat_id)?$iprodcat->prodcat_id:"NEW";        
        if($importprodcatid=="NEW"){
            $prodcatid=$prodcatcontrol->prodcatImportAdd($iprodcat->prodcat_desc);
            $iprodcat->prodcat_id=$prodcatid;
        }else{
           $prodcat=ProductCategories::where('id',$importprodcatid)->first();
           $iprodcat->prodcat_id=$iprodcat->prodcat_id;
           $iprodcat->prodcat_desc=$prodcat->prodcat_desc;
        }
        ProductImport::where('product_category',$iprodcat->prodcat_desc_import)->update(['prodcat_id'=>$iprodcat->prodcat_id]);
      }
      //buat master produk
      $products=ProductImport::with('brand')->get();
      foreach ($products as $key => $iproduct) {
        $productid=$this->saveProductImport($iproduct,$compid);
        ProductImport::where('product_name',$iproduct->product_name)->update(['product_id'=>$productid]);
      }

      $products=ProductImport::with('brand','productcat')->get();
      return view("products/import/importlist",compact('compid','products'));
    }

    public function importSave(Request $request){
        $compid=$request->compid;
        $iproducts=json_decode($request->iproducts);

        //simpan dan master produk        
        $store_id=$iproducts["0"]->store_destination;
        // $stockcard=new StockCardServices;
        // $arrStockIn=array(); 

        $username='IMPORT';
        $offmemcontrol=new OfficialMemosController;
        $autoNo=$offmemcontrol->autoOffMemoNo($store_id);

        $offmem=new OfficialMemos;
        $offmem->offmem_no=$autoNo;
        $offmem->store_id=$store_id; 
        $offmem->offmem_name=$username; 
        $offmem->offmem_date=date("Y-m-d H:i:s");
        $offmem->offmem_event_date=date("Y-m-d H:i:s");
        $offmem->offmem_title="Impor Produk"; 
        $offmem->offmem_desc="Berita Acara Impor Produk";
        $offmem->offmem_receive_value=0;
        $offmem->offmem_reduce_value=0;
        $offmem->offmem_type_id='3';
        $offmem->offmem_state="1";
        $offmem->save();

        foreach ($iproducts as $key => $iproduct) {
          $stock=$iproduct->product_stock;
          if($stock!=0){
            $this->updatePriceImport($iproduct->store_destination,$iproduct->product_id,$stock,
                  $iproduct->product_buy_price,$iproduct->product_sell_price);
            $productid=$iproduct->product_id;
            // $arrStockIn[]= array('product_id' => $productid,
            //         'trans_no'=>$autoNo, 
            //         'stock_qty' => $stock);
            
            $offmem->products()->attach([
                'product_id'=>$productid],
                [
                    'offmem_id'=>$offmem->id,
                    'offmem_product_qty'=>$stock,
                    'offmem_product_price'=>$iproduct->product_sell_price,
                    'offmem_product_state'=>'I'
                ]);
          }
        }

        ProductImport::truncate(); //pastikan data di hapus semua

        // $stockcard::updateProductStock($store_id,$arrStockIn,"ADD"); //update stock tabel produk
        // $stockcard::updateStockCard($username,$arrStockIn,"OFFMEMADD"); //tambahkan histori di table stockcard
        return redirect()->action([HomeController::class,'index']);
    }

    private function saveProductImport($iproduct,$compid){
      //tambahkan produk
      $lowerproddesc = strtolower($iproduct->product_name);
      // $product=Products::whereRaw('lower(product_name) like (?)',["%{$lowerproddesc}%"])->first();

      $product=Products::whereRaw('lower(product_name) like (?)',["{$lowerproddesc}"])->first();
      if($product!=null){
          return $product->id;
      }
      
      $product=new Products; 
      $product->product_plu=$iproduct->product_plu; 
      $product->brand_id=$iproduct->brand_id;
      $product->prodcat_id=$iproduct->prodcat_id;
      $product->unit_id='1';
      $product->product_name=$iproduct->product_name;
      $product->product_barcode=$iproduct->product_barcode;
      // $product->product_short_desc=substr($iproduct->product_name,0,50); //substr(string,start,length)
      // $product->product_stock_state=$iproduct->product_stock_state;
      $product->product_state='1'; 
      $exec = $product->save();
      return $product->id;
    }

    private function updatePriceImport($storeid,$productid,$productstock,$productbuyprice,$productprice){
      $foundstore=ProductStores::where([['store_id',$storeid],['product_id',$productid]])->count();

      $store=Stores::where('id',$storeid)->first();  
      $product=Products::with('stores')->where('id',$productid)->first();

      if($foundstore==0){
        $product->stores()->attach([
          'store_id'=>$storeid],
          [
              'product_id'=>$productid,
              'product_stock'=>$productstock,
              'product_buffer_stock'=>0,
              'product_sell_high_price'=>0,
              'product_sell_lower_price'=>0,
              'product_stock_state'=> '1', //default status stok
              'product_buy_price' =>$productbuyprice,
              'product_price'=>$productprice, 
          ]);
      }else{
        $product->stores()->updateExistingPivot($storeid,['product_price'=>$productprice]); 
      }
    }



  //----GAMBAR PRODUK
  public function productImageUpload(){
    return view('products/uploadimage');
  }
  public function productImageSave(Request $request){
    $validator = Validator::make($request->all(), [
      'image' => 'required | mimes:jpeg,png,jpg,gif | max:16'
    ]);
    
    if ($validator->fails()) {
      return back()->with('error',$validator->messages()->first());
    }

    // menyimpan data file yang diupload ke variabel $file
    $productid=$request->get('productid');
    $images = $request->file('image');
    $new_name=$productid.'.'.$images->getClientOriginalExtension();


    $tujuan_upload = base_path("images");
    $images->move($tujuan_upload,$new_name); 

    $fileloc=substr($request->root(),0,strlen($request->root())-6)."images/".$new_name;

    Products::where('product_id','=',$productid)->update(['product_file_loc'=>$fileloc]);

    return back()->with('success','Upload Gambar Berhasil '.$new_name);
  }
   
  //----------------------------Tools---------------------------------
  #produk di toko baik dengan maupun tanpa stok
  public function productByStore($storeid){
    $stores=function ($query) use ($storeid){
        $query->where('stores.id',$storeid);
    };
    $products=Products::with(['stores'=>$stores,'productcategory','brand'])->whereHas('stores',$stores)->get();
    $products=$this->injectProductList($products);
    return $products;
  }
  public function productConsignmentByStore($storeid){
    $stores=function ($query) use ($storeid){
        $query->where('stores.id',$storeid);
    };
    $products=Products::with(['stores'=>$stores,'productcategory','brand'])->whereHas('stores',$stores)->get();
    $products=$this->injectProductList($products);
    $arrProduct=array();
    foreach ($products as $key => $product) {
      $product_consignment_state=$product->stores[0]['product_stores']['product_consignment_state'];
      if($product_consignment_state=='1'){
        $arrProduct[]=$product;
      }
    }
    $products=json_decode(json_encode($arrProduct), FALSE);  //array to object
    return $products;
  }
  public function productStoreById($storeid,$productid){
    $stores=function ($query) use ($storeid){
        $query->where('stores.id',$storeid);
    };
    $product=Products::with(['stores'=>$stores])->whereHas('stores',$stores)->where('id',$productid)->first();
    $product=$this->injectProductSingle($product);
    return $product;
  }
  public function productStoreByCriteria($storeid,$kriteria){
    $stores=function ($query) use ($storeid){
        $query->with('company')->where('stores.id',$storeid);
    };
    $products=Products::with(['stores'=>$stores,'productcategory','brand'])->whereHas('stores',$stores)
            ->where('product_name','like','%'.$kriteria.'%')
            ->orWhere('product_barcode','like','%'.$kriteria.'%')
            ->get();
    $products=$this->injectProductList($products);
    return $products;
  }
  public function productStoreByCategory($storeid,$prodcatid){
    $stores=function ($query) use ($storeid){
        $query->with('company')->where('stores.id',$storeid);
    };
    $products=Products::with(['stores'=>$stores,'productcategory','brand'])->whereHas('stores',$stores)
            ->where('prodcat_id',$prodcatid)->get();
    $products=$this->injectProductList($products);
    return $products;
  }

  public static function updateProductStock($store_id,$products,$stat){
    $prodControl=new ProductsController;
    for ($i=0; $i < count($products); $i++) {
        $product_id=$products[$i]['product_id'];
        $product_qty=$products[$i]['product_qty'];   
        // $product_stock=$products[$i]['product_stock']; 
        //ambil stok produk
        $product_stock=ProductStores::where([['product_id',$product_id],['store_id',$store_id]])->first()->product_stock;

        $newstock=0;
        if($stat=="IN"){
          $newstock=$product_stock+$product_qty; //add new stock
        }else{
          $newstock=$product_stock-$product_qty; //substract current stock
        }

        // update stok di tabel produk store
        $product=Products::where('id',$product_id)->first();
        $product->stores()->updateExistingPivot($store_id,['product_stock'=>$newstock]);
    }
  }

  public function productStoreActivator($storeid,$productid){
    $storeprod=function($query) use ($storeid){
      $query->where('stores.id',$storeid);
    };
    $productfound=Products::whereHas('stores',$storeprod)->where('id',$productid)->first();
    if($productfound){
      return false;
    }
    $product=Products::where('id',$productid)->first();
    $product->stores()->attach([
      'store_id'=>$storeid],
      [
          'product_id'=>$productid,
          'product_stock'=>0,
          'product_buffer_stock'=>0,
      ]);
    return true;
  }  
  public function productFindStorePLUBarcode($storeid,$productplubarcode){
    $store=function ($query) use ($storeid){
      $query->where('stores.id',$storeid);
    };
    $product=Products::with(['stores'=>$store])->whereHas('stores',$store)
              ->where('products.product_plu',$productplubarcode) 
              ->orWhere('products.product_barcode',$productplubarcode)
              ->first();
    if(!$product){
      return $product;
    }
    if(count($product->stores)==0){
      return $product->stores;
    }
    $product=$this->injectProductSingle($product);
    return $product;
  }
  private function injectProductSingle($product){
    //inject dummy
    $product->product_buy_price=$product->stores[0]['product_stores']['product_buy_price'];
    $product->product_price=$product->stores[0]['product_stores']['product_price'];
    $product->product_stock=$product->stores[0]['product_stores']['product_stock'];
    $product->product_buffer_stock=$product->stores[0]['product_stores']['product_buffer_stock'];
    $product->product_stock_state=$product->stores[0]['product_stores']['product_stock_state'];
    $product->product_consignment_state=$product->stores[0]['product_stores']['product_consignment_state'];
    $product->product_promo_state='0';
    return $product;
  }
  private function injectProductList($products){
    //inject dummy
    foreach ($products as $key => $product) {
        $product->product_buy_price=$product->stores[0]['product_stores']['product_buy_price'];
        $product->product_price=$product->stores[0]['product_stores']['product_price'];
        $product->product_stock=$product->stores[0]['product_stores']['product_stock'];
        $product->product_buffer_stock=$product->stores[0]['product_stores']['product_buffer_stock'];
        $product->product_stock_state=$product->stores[0]['product_stores']['product_stock_state'];
        $product->product_consignment_state=$product->stores[0]['product_stores']['product_consignment_state'];
        $product->product_promo_state='0';
    }
    return $products;
  }
  public function productWithoutStockByStore($storeid){
    $products=ProductServices::getProductByStoreId($storeid,"0"); //produk di toko yang tidak memiliki stok
    return $products;
  }
  public function productOnTheStore($storeid){
    $products=ProductServices::getProductOnTheStore($storeid); //semua produk yang ada di toko
    return $products;
  }

    //----curent version
    //produk yang aktif di toko
    public function getproductstoresbystid($store_id){
      $products=ProductServices::getProductStoresByStoreId($store_id);
      return $products;
    }
    //pencarian produk
    public function searchproductbykriteria($kriteria){
      $activestoreid=$this->activeuserstoreid();
      $products=ProductServices::getProductStoreByCriteria($activestoreid,$kriteria);
      return $products;
    }
    public function searchproductbystorekriteria($storeid,$kriteria){
      $activestoreid=$this->activeuserstoreid();
      $products=ProductServices::getProductStoreByCriteria($storeid,$kriteria);
      return $products;
    }

    public function AutoProdId($prodcat_id)
    {
      // aturan nomor produk : kategori12345

      $strNewId = $prodcat_id."00001";
      // kalau belum ada record sebelumnya berarti ini yang pertama
      if(Products::where('product_id','=',$strNewId)->count()==0){
        return $strNewId;
      }

      $strLastId= Products::where('prodcat_id','=',$prodcat_id)->get()->last()->product_id;
      $intNewId= substr($strLastId,-5)+1;

      $strNewId=strlen($intNewId);
      switch (strlen($intNewId)) {
          case 1:
              $strNewId=$prodcat_id.'0000'.$intNewId;
              break;
          case 2:
              $strNewId=$prodcat_id.'000'.$intNewId;
              break;
          case 3:
              $strNewId=$prodcat_id.'00'.$intNewId;
              break;
          case 4:
              $strNewId=$prodcat_id.'0'.$intNewId;
              break;
          case 5:
              $strNewId=$prodcat_id.$intNewId;
              break;
      }
      return $strNewId;
    }














   
  
}
