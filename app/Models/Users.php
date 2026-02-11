<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class Users extends Authenticatable 
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table='sys_users';
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'user_id', // di database user_id
        'user_name',
        'user_pwd',
        'email',
        'client_id' 
    ];
    public function client()
    {
        return $this->belongsTo(Clients::class,'client_id','client_id');
    }
}