<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Companies extends Model
{
    protected $table='companies';
    protected $fillable=[  
      'comp_name',
      'comp_address',
      'comp_pos_code',
      'comp_city',
      'comp_province', 
      'comp_phone',
      'comp_email',
      'comp_logo',
      'comp_state',  //0=non active 1=active
      'db_host',
      'db_name',
      'db_user',
      'db_pass',
      'org_link',
    ]; 
    public function organizations()
    {
        return $this->hasMany(Organization::class, 'comp_id', 'id');
    }

    public function connectorSources()
    {
        return $this->hasMany(ConnectorSource::class, 'comp_id');
    }
   
}
