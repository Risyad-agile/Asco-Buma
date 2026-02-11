<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
  protected $table='products';
  protected $fillable=[
    'comp_id',
    'brand_id',
    'prodcat_id',
    'unit_id',
    'product_plu', //mempermudah entri di kasir POS
    'product_reg_number',
    'product_name', //nama produk/ obat
    'product_short_desc', //deskrisi singkat
    'product_long_desc', //deskripsi panjang
    'product_barcode',
    'product_dot', //Hijau, Merah, Biru
    'product_unit', //PCS, SYRUP, VIA, SPO, MLT, BOX, TUB, CAN, GRAM, AMP (Aktif)
    'product_buy_unit', //PCS, SYRUP, VIA, SPO, MLT, BOX, TUB, CAN, GRAM, AMP
    'product_sell_unit',  //PCS, SYRUP, VIA, SPO, MLT, BOX, TUB, CAN, GRAM, AMP
    'product_pic_loc', //default picture
    'product_pic_loc_1', //depan
    'product_pic_loc_2', //sisi kiri
    'product_pic_loc_3', //sisi kanan
    'product_generic_state',
    // 'product_stock_state', diatur di sisi product store, bisa jadi di satu store jenis stock sementara di store lainnya non stok
    'product_state', //0=not active 1=active
    
    //injeksi field untuk mempermudah proses 
    'product_buy_price',
    'product_price',
    'store_id',
    'brand_name',
    'prodcat_desc',
    'product_stock', //untuk stok produk (dari relasi dengan store)
    'product_stock_state', //untuk injeksi status stok
    'product_consignment_state', //untuk injeksi status stok
    'product_promo_state', //0=not promo 1=promo discount  2=promo merchant, status promo di apps untuk mempermudah identifikasi, akan diberikan kepada
    //produk yang berstatus promo baik utama maupun hadiah
    'product_sales_disc', //untuk parsing nilai discount promo type dicount
    'product_stock_physic',
    //untuk hitung mutasi stok
    'product_stock_in',
    'product_stock_out',
    'product_stock_purchase',
    'product_stock_retur',
    'product_stock_mutation_in',
    'product_stock_mutation_out',
    'product_stock_sales',
    'product_stock_offmemo_plus',
    'product_stock_offmemo_minus',
  ];
 
    

  public function productcategory(){
    return $this->hasOne('App\Models\ProductCategories','id','prodcat_id');
  }
  public function brand(){
    return $this->hasOne('App\Models\Brands','id','brand_id');
  }
    public function productunit(){
    return $this->hasOne('App\Models\ProductUnits','id','unit_id');
  }
  public function stores(){
    return $this->belongsToMany(Stores::class,'product_stores','product_id','store_id')
                ->as('product_stores')
                ->withPivot('product_stock','product_buy_price','product_price', 'product_buffer_stock',
                            'product_sell_high_price','product_sell_lower_price','product_sold_state',
                            'product_consignment_state','product_stock_state')
                ->withTimestamps();
  }
  public function sales(){
    return $this->belongsToMany(Sales::class,'sale_products','product_id','sale_id','id','id')
                ->as('sale_products')
                ->withPivot('sale_product_qty','sale_product_buy_price', 'sale_product_price','sale_product_disc','sale_product_total','sale_product_note')
                ->withTimestamps();
  }
  public function receives(){
    return $this->belongsToMany('App\Models\Receives','receive_products','product_id','receive_id','id','id')
                ->as('receive_products')
                ->withPivot('receive_product_price', 'receive_product_qty')
                ->withTimestamps();
  }
  public function returns()
  {
    return $this->belongsToMany('App\Models\Returns','return_products','product_id','retur_id','id','id')
                ->as('return_products')
                ->withPivot('retur_product_price', 'retur_product_qty');
  }
  public function mutationin(){
    return $this->belongsToMany('App\Models\MutationIn','mutationin_products','product_id','mutin_id','id','id')
                ->as('mutationin_products')
                ->withPivot('mutin_product_price', 'mutin_product_qty');
  }
  public function mutationout(){
    return $this->belongsToMany('App\Models\MutationOut','mutationout_products','product_id','mutout_id','id','id')
                ->as('mutationout_products')
                ->withPivot('mutout_product_price', 'mutout_product_qty');
  }
  public function officialmemos()
  {
    return $this->belongsToMany('App\Models\officialmemos','officialmemo_products','product_id','offmem_id','id','id')
                ->as('officialmemo_products')
                ->withPivot('offmem_product_price', 'offmem_product_qty','offmem_product_state');
  }
  public function pengdingsales()
  {
    return $this->belongsToMany('App\Models\PendingSales','pending_sale_products','pending_id','product_id','id','id')
                ->as('pendingsale_products')
                ->withPivot('pending_sale_product_qty', 'pending_sale_product_price','pending_sale_product_disc','pending_sale_product_total',
                'pending_sale_product_note','pending_sale_product_state','pending_sale_product_buy_price')
                ->withTimestamps();
  }


    // public function stores(){
    //   return $this->belongsToMany('App\Models\Stores','product_stores','product_id','store_id','id','id')
    //               ->as('productstores')
    //               ->withPivot('product_stock', 'product_buffer_stock','product_price','product_state')
    //               ->withTimestamps();
    // }





    // public function sales(){
    //   return $this->belongsToMany('App\Models\Sales','sale_products','product_id','sale_id','id','id')
    //               ->as('productsales')
    //               ->withPivot('sale_product_qty','sale_buy_price','sale_product_price','sale_product_disc','sale_product_total','sale_product_note')
    //               ->withTimestamps();
    // }
    // public function promotions(){
    //   return $this->belongsToMany('App\Models\Promotions','promotion_products','product_id','promo_no','product_id','promo_no')
    //               ->as('promotionproducts')
    //               ->withPivot('promo_product_price', 'promo_product_qty','promo_product_disc','promo_product_state');
    //               // ->withTimestamps();
    // }
    // public function pendingsales(){
    //   return $this->belongsToMany('App\Models\PendingSales','pending_sale_products','product_id','pending_no','product_id','pending_no')
    //               ->as('pendingsaleproducts')
    //               ->withPivot('pending_sale_product_qty', 'pending_sale_product_price','pending_sale_product_disc','pending_sale_product_total',
    //                'pending_sale_product_note','pending_sale_product_state','pending_sale_product_printed','pending_sale_product_qty_last')
    //                ->withTimestamps();
    // }



}
