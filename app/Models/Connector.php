<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Connector extends Model
{
    use HasFactory;
    protected $table='connectors';
    protected $fillable=[
        'comp_id',
        'connect_name',
        'connect_type', //ENVIZI CLIENT-ACCSETUP CLIENT-ACCSTYLE CLIENT-CSRDATA
        'connect_protocol', //API, FTP S2
        'connect_username', //user token user name
        'connect_password', //user token passwor3
        'connect_url',
        'connect_body', //additional body command
        'connect_email', //user token api
        'connect_token_value', // token untuk akses api envizi
        'connect_storage_code', //posisi konfigurasi aws s3 di laravel, isi public untuk disimpan ke local cloud
        'connect_access_key_id',//access key aws s3
        'connect_access_key_secret',//secret key aws s3
        'connect_access_region', //regios aws
        'connect_remote_folder',//folder evizi di aws s3
        'connect_data_load_name', //nama file account style
        'connect_data_load_name_csr', //nama file csr account style
        'connect_state',  //0=non active 1=active
    ];
    public function company()
    {
        return $this->belongsTo(Companies::class,'comp_id','id');
    }
}

    