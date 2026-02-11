<?php 

namespace App\Models ;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

Class TempSync extends Model
{
    protected $table='temp_sync'; 

    protected $fillable= [
        'acc_id',
        'acc_name',
        'stat_active'
    ];

}
