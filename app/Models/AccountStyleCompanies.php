<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStyleCompanies extends Model
{
    //untuk account style yang aktif di perusahaan
    use HasFactory;
    protected $table='account_style_companies';
    protected $fillable=[
        'comp_id', 
        'acc_style_comp_link',
        'acc_style_comp_caption', 
        'acc_style_comp_caption_import',
        'acc_style_comp_subtype',
        'acc_style_comp_number',
        'acc_style_comp_reference',
        'acc_style_comp_supplier',
        'acc_style_comp_reader', 
        'acc_style_comp_state', //0=non active 1=active
        'acc_style_scope', //description of the scope of the account style
    ];  
    public function company()
    {
        return $this->belongsTo(Companies::class,'comp_id','id');
    }
}
