<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStylesImport extends Model
{
    use HasFactory;
    protected $table='account_styles_import';
    protected $fillable=[
        'acc_style_product',  
        'acc_style_datatype',
        'acc_style_scope',
        'acc_style_category',
        'acc_style_name',
        'acc_style_caption',
        'acc_style_qty_uom', 
        'acc_style_cost_supported', 
    ];  
}
 