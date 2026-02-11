<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountStyleGroupImport extends Model
{
    use HasFactory;
    protected $table='account_style_group_maping';
    protected $fillable=[
      'comp_id',
      'task_id',
      'acc_style_caption',
      'acc_style_caption_import',  
      'acc_style_group_describe_state',
      'acc_style_group_state',   
    ];
}
