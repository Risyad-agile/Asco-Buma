<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Syncronize extends Model
{
    //tabel ini digunakan untuk sinkronisasi data master
    //asri dengan envizi
    use HasFactory; 
    protected $table='syncronize';
    protected $fillable=[
        'comp_id',
        'connect_id', 
        'sync_task_name', 
        'sync_time',
        'sync_state_note', 
        'sync_state',
    ];
    
    public function company()
    {
        return $this->belongsTo(Companies::class,'comp_id','id');
    }
}

