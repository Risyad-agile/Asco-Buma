<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountStyles extends Model
{
    protected $table='account_styles';
    protected $fillable=[
        'acc_style_link',
        'acc_style_caption',
        'acc_style_name',
        'acc_style_product',
        'acc_style_scope',
        'acc_style_datatype',
        'acc_style_subtype',
        'acc_style_product',
        'acc_style_category',
        'acc_style_number',
        'acc_style_reference',
        'acc_style_supplier',
        'acc_style_reader', 
        'acc_style_qty_uom',
        'acc_style_cost_supported',
        'acc_style_xls_format', //NORMAL, SPECIAL=exanmple: biodiesel B53
        'acc_style_state', //0=non active 1=active
    ];  
    public function company()
    {
        return $this->belongsTo(Companies::class,'comp_id','id');
    }
}
  
