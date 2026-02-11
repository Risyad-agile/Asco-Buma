<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessDataLogs extends Model
{
    // tabel ini digunakan untuk mencat logs penerimaan data dari client 
    // baik itu dari ambil (api, ftp) maupun terima (api,upload)
    protected $table='process_data_logs';
    protected $fillable=[
        'comp_id',
        'connect_id',
        'process_data_type', //RECEIVE RETRIEVE
        'process_data_origin', //client api receive, client api retrive, client ftp, client ftp receive,client upload
        'process_data_tittle',
        'process_data_note',
        'process_data_process_state',
        'process_data_process_time', 
        'process_data_describe_state', 
        'process_data_state', //0=non active 1=receive 2=send 3=error
      ];
      
    public function company(){
        return $this->belongsTo('App\Models\Companies','comp_id','id');
    }
}

