<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table='sales';
    protected $fillable=[
      'sale_no',
      'store_id',
      'spot_id',
      'sale_cashier',
      'sale_date',  //tanggal dan jam pada saat mulai order, tanggal transaksi ambil dari created date
      'sale_note',
      'sale_disc', //menyimpan semua discount baik total produk maupun discount pembayaran
      'sale_tax', 
      'sale_service_charge',
      'sale_total', 
      'member_no',
      'username',
      'sale_state',//0=cancel 1=active
    ];
    public function store()  
    {
      return $this->belongsTo(Stores::class,'store_id','id');
    }
    public function products(){
      return $this->belongsToMany(Products::class,'sale_products','sale_id','product_id','id','id')
                  ->as('sale_products')
                  ->withPivot('sale_product_qty','sale_product_buy_price', 'sale_product_price','sale_product_disc','sale_product_total','sale_product_note')
                  ->withTimestamps();
    }
    public function payments()
    {
      return $this->belongsToMany('App\Models\Payments','sale_payments','sale_id','pay_id','id','id')
                  ->as('sale_payments')
                  ->withPivot('card_no', 'pay_desc','sale_pay_purchase','sale_pay_payed','sale_pay_return')
                  ->withTimestamps();
    }


    // public function product()
    // {
    //   return $this->hasMany('App\Models\Products','sale_products','sale_no','product_id','sale_no','product_id')
    //               ->as('saleproduct')
    //               ->withPivot('sale_product_qty','sale_buy_price', 'sale_product_price','sale_product_disc','sale_product_total','sale_product_note')
    //               ->withTimestamps();
    // }

    // public function members()
    // {
    //   return $this->belongsTo('App\Models\Members','member_no','member_no');
    // }

    // public function shifting(){
    //   return $this->belongsToMany('App\Models\Shifting','shifting_sales','sale_no','shift_no','sale_no','shift_no')
    //               ->as('shiftingsales')
    //               ->withPivot('shift_index','shift_sale_total', 'shift_sale_disc')
    //               ->withTimestamps();
    // }
    // public function promotions(){
    //   return $this->belongsToMany('App\Models\Promotions','sale_promotions','sale_no','promo_no','sale_no','promo_no')
    //               ->withPivot('sale_promo_qty','sale_promo_price')
    //               ->as('salepromotions')
    //               ->withTimeStamps();
    // }
}
