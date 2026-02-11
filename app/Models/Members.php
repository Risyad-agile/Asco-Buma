<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Members extends Model
{
    protected $table='members'; 
    protected $fillable=[
      'member_no', //id unix di database
      'member_id', //nomor handphone untuk pencarian
      'comp_id', 
      'store_id', //lokasi toko daftar bisa berubah
      'memtype_id',  
      'member_card_no',
      'member_ktp',
      'member_name',
      'member_email',
      'member_desc',
      'member_birth_date',
      'member_birth_place',
      'member_gender', //Pria Wanita
      'member_religion', //islam Protestan Katolik Hindu Budha
      'member_address',
      'member_alter_phone',
      'member_pin',
      'member_total_trans', 
      'member_points',
      'member_state', //0. not active 1. aktif 
      'member_store_activation', //dummy variable khusus aplikasi tidak ada di database
    ];
    public function companies()
    {
        return $this->belongsTo('App\Models\Companys','comp_id','comp_id');
    }     
    public function stores()
    {
      return $this->belongsTo('App\Models\Stores','store_id','store_id');
    }
    public function membertypes()
    {
        return $this->belongsTo('App\Models\MemberTypes','memtype_id','memtype_id');
    }     
    public function sales()
    {
      return $this->hasMany('App\Models\Sales','member_no','member_no');
    }
    public function evouchers(){
      return $this->hasMany('App\Models\EVouchers','member_no','member_no');
    }
    public function pendingsales(){
      return $this->hasOne('App\Models\PendingSales','member_no','member_no');
    }
}
