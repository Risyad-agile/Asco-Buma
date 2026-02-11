<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationGroupImport extends Model
{
    use HasFactory;
    protected $table='location_group_maping';
    protected $fillable=[
      'comp_id',
      'task_id',
      'location_name_origin',
      'location_name_import',  
      'location_group_state',   
    ];
}

