<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $table='locations';
    protected $fillable=[
        'reff_id',
        'org_name', // mandatory
        'group_type',  // mandatory
        'group_hierarchy_name',
        'group_name1', // mandatory
        'group_name2', // mandatory
        'group_name3', // mandatory
        'location_name', // mandatory   
        'location_type',
        'location_reff',
        'location_reff_no',
        'location_id',
        'location_address',
        'location_city',
        'location_state_province', 
        'postal_code',
        'country',
        'latitude_y',
        'longitude_x',
        'location_close_date'   
    ];  
    public function organization()
    {
        return $this->belongsTo(Organization::class,'org_name','org_name');
    }
}

