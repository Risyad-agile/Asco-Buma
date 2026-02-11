<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Promotions;
use App\Models\Products;
use App\Models\Stores;
use App\Models\ProductCategorys;
use App\Models\Brands;
use App\Services\ProductServices;
use App\Services\PromoServices;
use App\Services\StoreServices;
use DB;
use App\Http\Controllers\ProductsController;

class PromotionsController extends Controller
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
        return Auth::user()->stores->companys->comp_id;
    }
    public function activeuserstoreid(){
        return Auth::user()->stores->store_id;
    }
    public function index()
    { 
        $promos=Promotions::where([['comp_id','=',$this->compid()],['promo_type','!=','9']])
                ->orderby('created_at', 'desc')->get();
        return view('promotions/promotionlist',compact('promos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $promos=Promotions::where([['comp_id','=',$this->compid()],['promo_type','!=','9'],['promo_state','1']])
        ->orderby('created_at', 'desc')->get();
        return $promos;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {    
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
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
        Promotions::where('promo_no','=',$id)->update(['promo_active'=>$request->promo_active]);
        return "success";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $promo=Promotions::where('promo_no',$id)->first();

        //delete relasi
        $promo->products()->detach();
        //delete promosi
        Promotions::where('promo_no',$id)->delete();
    }


    //promo discount
    //update 31-08-2020
    //karena ada masalah double save ketika di server, ternyarta masalah di sebabkan parsing compact(product)
    //maka konsep pembuatan promo di ganti dengan konsep load data via ajax
    public function promoDiscountIndex(){ 
        //eksekusi status promo terbaru (untuk refresh update, karena status harian sudah dicek pada saat login)
        if(substr($this->activeuserstoreid(),-3)!="000"){
            return view('layouts/message')->with('pesan','Pengguna Bukan dari Kantor Pusat, Proses tidak bisa dilanjutkan, 
            Silakan di perbaharui atau login dengan user Kantor Pusat (HO)');;
        }

        $promos=Promotions::with('products')->where([['comp_id','=',$this->compid()],['promo_type','1']])
                ->orderby('created_at', 'desc')->get();
        return view('promotions/promodiscindex',compact('promos'));
    }

    public function promoDiscountMain(Request $request){
        $promono=$request->get('promo_no');
        $promostate=$request->get('promo_state');

        // $products=ProductServices::getProductStoresByStoreId($this->activeuserstoreid()); //produk yang aktif di kantor pusat
        // $products=ProductServices::getProductStoresByStoreId($this->activeuserstoreid()); //produk yang aktif di toko tsb
        $store=function ($query){
            $query->where([['comp_id',$this->compid()],['store_default','1']]); //head office
        };

        $products=Products::with(['stores'=>$store,'productcats'])->whereHas('stores',$store)->get();

        if($promono==null){  //addnew
            foreach ($products as $product) {
                $product->product_disc=0;
            }
            return view('promotions/promodiscnew',compact('products'));
        }else{
            $promo=Promotions::with('products')->where('promo_no','=',$promono)->first();
            if($promostate=="UPDATE"){
                return view('promotions/promodiscupdate',compact('promo','products'));
            }else{
                return view('promotions/promodiscdelete',compact('promo'));
            }
        }
    }
    public function promoDiscountProductLoad(){
        //menggunakan konsep load, karena kalau produk di kirim langsung dari promotion main 
        //terjadi double save pada saat di server
        $products=Products::with('productcats')->where([['comp_id',$this->compid()],['product_state','!=','0']])->get();
        return $products;
    }
    public function promoDiscountProductUpdate(Request $request, $id){
        //konsekuensi load pake ajax, harus ada updatenya juga
        $productdisc=$request->get('values')['product_disc'];
        Products::where('product_id','=',$id)->update(['product_disc'=>$productdisc]);

        $store=function ($query){
            $query->where([['comp_id',$this->compid()],['store_default','1']]); //head office
        };
        $products=Products::with(['stores'=>$store,'productcats'])->whereHas('stores',$store)->get();
        foreach ($products as $product) {
            if($product->product_id==$id){
                $product->product_disc=$productdisc;
            }
            if($product->product_disc!=0){
                $product->product_disc=0;
            }
        }
        return $products;
    }
    public function promoDiscountSave(Request $request){
        $promo_day_all="0";
        $promo_day_mon="0";
        $promo_day_tue="0"; 
        $promo_day_wed="0";
        $promo_day_thu="0"; 
        $promo_day_fri="0"; 
        $promo_day_sat="0"; 
        $promo_day_sun="0"; 
        for ($i=0; $i < count($request->get("selecteddays")) ; $i++) { 
            if($request->get("selecteddays")[$i]["id"]=="1"){
                $promo_day_mon="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="2"){
                $promo_day_tue="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="3"){
                $promo_day_wed="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="4"){
                $promo_day_thu="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="5"){
                $promo_day_fri="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="6"){
                $promo_day_sat="1";
            }            
            if($request->get("selecteddays")[$i]["id"]=="7"){
                $promo_day_sun="1";
            }
        }

        $sumday=$promo_day_mon+$promo_day_tue+$promo_day_wed+$promo_day_thu+$promo_day_fri+$promo_day_sat+$promo_day_sun;
        if($sumday==7){
            $promo_day_all="1";
        }

        try {
            $comp_id=$this->compid();
            $promo=new Promotions;
            $autoNo=$this->autoPromoNo($comp_id);
            $promo->promo_no=$autoNo;
            $promo->comp_id=$comp_id;
            $promo->promo_date_start=$request->input('form')['promo_date_start'];
            $promo->promo_date_end=$request->input('form')['promo_date_end'];
            $promo->promo_desc=$request->input('form')['promo_desc'];
            $promo->promo_rule=$request->input('form')['promo_rule'];
            $promo->promo_type="1"; //discount
            $promo->promo_price=0;
            $promo->promo_day_all=$promo_day_all;
            $promo->promo_day_mon=$promo_day_mon;
            $promo->promo_day_tue=$promo_day_tue; 
            $promo->promo_day_wed=$promo_day_wed;
            $promo->promo_day_thu=$promo_day_thu; 
            $promo->promo_day_fri=$promo_day_fri; 
            $promo->promo_day_sat=$promo_day_sat; 
            $promo->promo_day_sun=$promo_day_sun; 
            $promo->promo_state='1'; //0:non aktif 1:aktif
            $exec = $promo->save();
            if (!$exec) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }

            

            for ($i=0; $i < count($request->input('table')) ; $i++) {
                $jnspromo='1'; //discount jenis produk promo Utama
                $productid=$request->input('table')[$i]['product_id'];
                $productdisc=$request->input('table')[$i]['product_disc'];
                
                if($productdisc!=0){
                    $promo->products()->attach([
                        'product_id'=>$productid],
                        [
                            'promo_no'=>$autoNo,
                            'promo_product_qty'=>1,
                            'promo_product_disc'=>$productdisc,
                            'promo_product_state'=>$jnspromo
                        ]);
                    //update status produk promo dan kembalikan disc menjadi 0 (karena sifatnya sementara)
                    Products::where('product_id',$productid)->update(['product_promostate'=>'1','product_disc'=>0]);  //promo discount
                }
            }
            // dd(count($request->input('table')));
            
            return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
    }    
    public function promoDiscountUpdate(Request $request){
        try {
            $promono=$request->input('form')['promo_no'];
            $promo=Promotions::where('promo_no',$promono)->first();

            $promostate='0';
            //cek tanggal, jika lebih dari tanggal hari ini maka status aktifnya menjadi 1
            $today=date('Y-m-d');
            $date_end=$request->input('form')['promo_date_end'];
            
            if($date_end>$today){
                $promostate='1';
            }

            Promotions::where('promo_no','=',$promono)
            ->update([
                'promo_date_start'=>$request->input('form')['promo_date_start'],
                'promo_date_end'=>$date_end,
                'promo_desc'=>$request->input('form')['promo_desc'],
                'promo_rule'=>$request->input('form')['promo_rule'],
                'promo_state'=>$promostate,
            ]);

            // kembalikan status not promo
            foreach ($promo->products as $product ) {
                $productid=$product->product_id;
                Products::where('product_id',$productid)
                ->update([
                    'product_promostate'=>'0',
                ]);
            }
            $promo->products()->detach(); //hapus berdasarkan promo

            for ($i=0; $i < count($request->input('table')) ; $i++) {
                $jnspromo='1'; //discount jenis produk promo Utama
                $productid=$request->input('table')[$i]['product_id'];
                $productdisc=$request->input('table')[$i]['promotionproducts']['promo_product_disc'];
                
                if($productdisc!=0){
                    // var_dump($productid);
                    $promo->products()->attach([
                    'product_id'=>$productid],
                    [
                        'promo_no'=>$promono,
                        'promo_product_qty'=>1,
                        'promo_product_disc'=>$productdisc,
                        'promo_product_state'=>$jnspromo
                    ]);   
                    //update status produk promo untuk produk utama
                    //dipindahkan ke sini karena menghindari proses promo service update state
                    // Products::where('product_id',$productid)->update(['product_promostate'=>'1']); //promo discount
                    Products::where('product_id',$productid)->update(['product_promostate'=>'1','product_disc'=>0]); //promo discount
                }
            }
            //eksekusi status promo terbaru (untuk refresh update, karena status harian sudah dicek pada saat login)
            PromoServices::updatePromoState($this->compid());
            return response()->json(['status' => 'success', 'message' => 'Data successfully updated', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);

    }


    //promo gift
    public function promoMerchantIndex(){ 
        if(substr($this->activeuserstoreid(),-3)!="000"){
            return view('layouts/message')->with('pesan','Pengguna Bukan dari Kantor Pusat, Proses tidak bisa dilanjutkan, 
            Silakan di perbaharui atau login dengan user Kantor Pusat (HO)');;
        }
        $promos=Promotions::with('products')->where([['comp_id','=',$this->compid()],['promo_type','2']])
                ->orderby('created_at', 'desc')->get();

        return view('promotions/promomerchantindex',compact('promos'));
    }
    public function promoMerchantMain(Request $request){
        $promono=$request->get('promo_no');
        $promostate=$request->get('promo_state');
        
        $store=function ($query){
            $query->where([['comp_id',$this->compid()],['store_default','1']]); //head office
        };

        $products=Products::with(['stores'=>$store,'productcats'])->whereHas('stores',$store)->get();
        
        
        // $products=ProductServices::getProductStoresByStoreId($this->activeuserstoreid()); //produk yang aktif 
        if($promono==null){  //addnew
            //titip new value di objek yang di kirim
            foreach ($products as $product) {
                $product->product_qty=0;
                $product->product_state=false;
            }
            return view('promotions/promomerchantnew',compact('products'));
        }else{
            $promo=Promotions::with('products')->where('promo_no',$promono)->first();
            
            if($promostate=="UPDATE"){
                return view('promotions/promomerchantupdate',compact('promo','products'));
            }else{
                return view('promotions/promomerchantdelete',compact('promo'));
            }
        }
    }
    public function promoMerchantSave(Request $request){
        $promo_day_all="0";
        $promo_day_mon="0";
        $promo_day_tue="0"; 
        $promo_day_wed="0";
        $promo_day_thu="0"; 
        $promo_day_fri="0"; 
        $promo_day_sat="0"; 
        $promo_day_sun="0"; 
        for ($i=0; $i < count($request->get("selecteddays")) ; $i++) { 
            if($request->get("selecteddays")[$i]["id"]=="1"){
                $promo_day_mon="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="2"){
                $promo_day_tue="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="3"){
                $promo_day_wed="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="4"){
                $promo_day_thu="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="5"){
                $promo_day_fri="1";
            }
            if($request->get("selecteddays")[$i]["id"]=="6"){
                $promo_day_sat="1";
            }            
            if($request->get("selecteddays")[$i]["id"]=="7"){
                $promo_day_sun="1";
            }
        }

        $sumday=$promo_day_mon+$promo_day_tue+$promo_day_wed+$promo_day_thu+$promo_day_fri+$promo_day_sat+$promo_day_sun;
        if($sumday==7){
            $promo_day_all="1";
        }
        try {
            $comp_id=$this->compid();
            $promo=new Promotions;
            $autoNo=$this->autoPromoNo($comp_id);
            $promo->promo_no=$autoNo;
            $promo->comp_id=$comp_id;
            $promo->promo_date_start=$request->input('form')['promo_date_start'];
            $promo->promo_date_end=$request->input('form')['promo_date_end'];
            $promo->promo_desc=$request->input('form')['promo_desc'];
            $promo->promo_rule=$request->input('form')['promo_rule'];
            $promo->promo_type="2"; //merchant
            $promo->promo_price=$request->input('form')['promo_price'];
            $promo->promo_state='1'; //0:non aktif 1:aktif
            $promo->promo_day_all=$promo_day_all;
            $promo->promo_day_mon=$promo_day_mon;
            $promo->promo_day_tue=$promo_day_tue; 
            $promo->promo_day_wed=$promo_day_wed;
            $promo->promo_day_thu=$promo_day_thu; 
            $promo->promo_day_fri=$promo_day_fri; 
            $promo->promo_day_sat=$promo_day_sat; 
            $promo->promo_day_sun=$promo_day_sun; 
            $exec = $promo->save();
            if (!$exec) {
                return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
            }
            for ($i=0; $i < count($request->input('table')) ; $i++) {
            
                $productid=$request->input('table')[$i]['product_id'];
                $productqty=$request->input('table')[$i]['product_qty'];
                $productstate=$request->input('table')[$i]['product_state'];


                if($productqty!=0){
                    //penentuan status produk utama dan produk hadiah
                    $jnspromo='2';  
                    if($productstate=='true'){
                        $jnspromo='1';
                        //update status produk menjadi produk promo khusus ptoduk utama
                        Products::where('product_id',$productid)->update(['product_promostate'=>'2']); //promo merchant
                    }

                    $promo->products()->attach([
                        'product_id'=>$productid],
                        [
                            'promo_no'=>$autoNo,
                            'promo_product_qty'=>$productqty,
                            'promo_product_disc'=>0,
                            'promo_product_state'=>$jnspromo
                        ]);
                }
            }
            return response()->json(['status' => 'success', 'message' => 'Data successfully saved', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
    }    
    public function promoMerchantUpdate(Request $request){
        try {
            $promono=$request->input('form')['promo_no'];
            $promo=Promotions::with('products')->where('promo_no',$promono)->first();

            $promostate='0';
            //cek tanggal, jika lebih dari tanggal hari ini maka status aktifnya menjadi 1
            $today=date('Y-m-d');
            $date_end=$request->input('form')['promo_date_end'];
            
            if($date_end>$today){
                $promostate='1';
            }

            Promotions::where('promo_no','=',$promono)
            ->update([
            'promo_date_start'=>$request->input('form')['promo_date_start'],
            'promo_date_end'=>$date_end,
            'promo_price'=>$request->input('form')['promo_price'],
            'promo_desc'=>$request->input('form')['promo_desc'],
            'promo_rule'=>$request->input('form')['promo_rule'],
            'promo_state'=>$promostate,
            ]);

            //kembalikan status produk not promo
            foreach ($promo->products as $product ) {
                $productid=$product->product_id;
                Products::where('product_id',$productid)->update(['product_promostate'=>'0']);
            }

            //hapus berdasarkan promo
            $promo->products()->detach(); 
            $prime_prod="";
            for ($i=0; $i < count($request->input('table')) ; $i++) {
                $productid=$request->input('table')[$i]['product_id'];
                $productqty=$request->input('table')[$i]['promotionproducts']['promo_product_qty'];
                $productstate=$request->input('table')[$i]['promotionproducts']['promo_product_state']; 

                if($productqty!=0){
                    $jnspromo='2';  
                    if($productstate=='1'){
                        //update status produk menjadi produk promo khusus ptoduk utama
                        $jnspromo='1';
                        Products::where('product_id',$productid)->update(['product_promostate'=>'2']); //promo merchant
                    }
                    $promo->products()->attach([
                    'product_id'=>$productid],
                    [
                        'promo_no'=>$promono,
                        'promo_product_qty'=>$productqty,
                        'promo_product_state'=>$jnspromo
                    ]);

                }
            }
            //eksekusi status promo terbaru (untuk refresh update, karena status harian sudah dicek pada saat login)
            PromoServices::updatePromoState($this->compid());
            return response()->json(['status' => 'success', 'message' => 'Data successfully updated', 'code' => 200]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'code' => 404]);
        }
        return response()->json(['status' => 'error', 'message' => 'System error', 'code' => 404]);
    }


    //aktifasi promo di toko
    public function activeindex(){
        $promos=Promotions::with('stores')->where([['comp_id','=',$this->compid()],['promo_type','!=','9'],['promo_state','1']])
        ->orderby('created_at', 'desc')->get();

        //hitung jumlah toko dalam perusahaan
        $numstores=Stores::where('comp_id','=',$this->compid())->count(); //dikurangi ho
        
        foreach ($promos as $key => $promo) {
            $promono=$promo->promo_no;
            //hitung jumlah toko yang memiliki promo, pada tabel promo store  KDSW-PR19030010
            $numpromstore=DB::table('promotion_stores')->where([['promo_no',$promono],
                         [DB::raw('LEFT(store_id,4)'),'=',$this->compid()]])->count();

            if($numpromstore==0){
                $promo->promo_state="Tidak Aktif di Toko";
            }elseif($numpromstore<$numstores){
                $promo->promo_state="Aktif di Sebagian Toko";
            }elseif($numpromstore==$numstores){
                $promo->promo_state="Aktif Semua Toko";
            }
        }
        return view('promotions/promotionactivation',compact('promos'));
    }
    public function getpromoselectedstorenamebycompany(){                
        $stores=StoreServices::getStoreIDNameByCompanyID($this->compid());
        return $stores;
    }
    public function activatepromostores(Request $request){
        $promo_no=$request->get('promono');
        $selectedstores=explode(",",$request->get('storeids'));
        $stores=Stores::whereIn('store_id',$selectedstores)->get();
        $promos=Promotions::where('promo_no','=',$promo_no)->first();

        //memastikan promo aktif promo dengan tanggal
        //hapus semua lokasi dengan promo sejenis
        if($promos->count()!=0){
            $promos->stores()->detach();
        }
        
        foreach ($stores as $key => $value) {
            $promos->stores()->attach([
                'store_id'=>$value->store_id],
                [
                    'promo_store_state'=>"1", 
                ]);
        }

        return "success";
    }
    public function deactivatepromostores(Request $request){
        $promo_no=$request->get('promono');
        $selectedstores=explode(",",$request->get('storeids'));
        $stores=Stores::whereIn('store_id',$selectedstores)->get();
        $promos=Promotions::where('promo_no','=',$promo_no)->first();

        //hapus semua lokasi dengan promo sejenis
        if($promos->count()!=0){
            $promos->stores()->detach();
        }
        
        foreach ($stores as $key => $value) {
            $promos->stores()->attach([
                'store_id'=>$value->store_id],
                [
                    'promo_store_state'=>"1", 
                ]);
        }

        return "success";
    }

   


    public function autoPromoNo($comp_id)
    {
      // aturan nomor Promo : CompID-PRmmyy1234
    //   $comp_id=$this->compid();
      $bulan=date("m");
      $tahun=date("y");
      $strNewId = $comp_id."-PR".$tahun.$bulan."0001";

      // kalau belum ada record sebelumnya berarti ini yang pertama
      if(Promotions::where('comp_id','=',$comp_id)->count()==0){
          return $strNewId;
      }

      $strLastId= Promotions::where('comp_id','=',$comp_id)->get()->last()->promo_no;
      $intNewId=substr($strLastId,-4)+1;

      $strNewId=strlen($intNewId);
      switch (strlen($intNewId)) {
          case 1:
              $strNewId=$comp_id.'-PR'.$tahun.$bulan.'000'.$intNewId;
              break;
          case 2:
              $strNewId=$comp_id.'-PR'.$tahun.$bulan.'00'.$intNewId;
              break;
          case 3:
              $strNewId=$comp_id.'-PR'.$tahun.$bulan.'0'.$intNewId;
              break;
          case 4:
              $strNewId=$comp_id.'-PR'.$tahun.$bulan.$intNewId;
              break;
      }
      return $strNewId;
    }
}
