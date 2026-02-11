<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model
{
    protected $table='mst_clients';
    protected $primaryKey = 'client_id';

    protected $fillable=[
        'client_id',
        'client_init',
        'client_name',
        'client_address',
        'client_poscode',
        'client_city',
        'client_province',
        'client_phone',
        'client_email',
        'client_logo',
        'client_status', //0:Non Active, 1:Active
        'db_ip',
        'db_name',
        'db_user',
        'db_pwd'
    ];
    
}