<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStyleMapping extends Model
{
    //untuk account style yang aktif di perusahaan
    use HasFactory;
    protected $table='account_style_mapping';
    protected $fillable=[
        'comp_id',
        'acc_style_comp_id', 
        'acc_style_mtra_caption', 
        'acc_number',
        'acc_style_type'
    ];  
    public function company()
    {
        return $this->belongsTo(Companies::class,'comp_id','id');
    }
    public function accstyles()
    {
        return $this->belongsTo(AccountStyles::Class,'acc_style_comp_id','id');
    }
}