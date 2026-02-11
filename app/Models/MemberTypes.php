<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberTypes extends Model
{
    protected $table='member_types';
    protected $fillable=[
        'memtype_id',
        'comp_id',
        'memtype_desc',
        'memtype_rule',
        'memtype_min_value',  //nilai transaksi tercapai untuk menjadi jenis anggota tsb
        'memtype_min_periode', //dalam bulan
        'memtype_disc_state', //0. Tidak Mendapat Discount 1. Mendapatkan Discount
        'memtype_disc_type',  //1. nominal 2.total sales per factur
        'memtype_disc_type_value', //ada nominal jika pilihan type 1, nol jika pilihan type 2
        'memtype_disc_value', //nilai discount yang diberikan dalam rupiah
        'memtype_disc_percent', //nilai discount dalam persentase
        'memtype_point_state', //0. Poin tidak aktif 2. Poin Aktif
        'memtype_point_value', //harga per point
        'memtype_state', //0=tidak aktif 1=aktif  
      ];
    public function companys()
    {
        return $this->belongsTo('App\Models\Companys','comp_id','comp_id');
    }      
}

